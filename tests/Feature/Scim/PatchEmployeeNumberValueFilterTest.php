<?php

namespace Tests\Feature\Scim;

use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * Regression for #19819. When Entra ID de-provisions a user whose
 * employeeNumber has also changed, it sends one PATCH to
 * /scim/v2/Users/{id} with two operations: one that flips `active` to
 * false and one that touches `employeeNumber` using a value-path
 * filter like `employeeNumber[value eq "<previous value>"]`. The whole
 * PATCH was failing with:
 *
 *   Unknown path: employeeNumber[value eq "123"], in object:
 *   urn:ietf:params:scim:schemas:core:2.0:User
 *
 * because the vendor Complex::patch's value-path branch calls
 * getSubNode('employeeNumber') on root and (missing it there) falls
 * back to the FIRST schema node, which is core. employeeNumber lives
 * on the enterprise schema, so the fallback never resolves it. The
 * user never flipped to inactive even though the active=false op was
 * well-formed, because PATCH is all-or-nothing.
 *
 * SnipeRootComplex::patch now scans every AttributeSchema sub-node
 * for the attribute by name before deferring to the vendor, mirroring
 * the schema-aware routing already in add() / replace() /
 * applyComparison() from #19347.
 */
class PatchEmployeeNumberValueFilterTest extends TestCase
{
    public function test_patch_with_value_filter_on_employee_number_disables_user_and_updates_employee_number(): void
    {
        Passport::actingAs(User::factory()->superuser()->create());
        $target = User::factory()->create([
            'activated' => true,
            'employee_num' => 'OLD-123',
        ]);

        $response = $this->patchJson('/scim/v2/Users/' . $target->id, [
            'schemas' => ['urn:ietf:params:scim:api:messages:2.0:PatchOp'],
            'Operations' => [
                [
                    'op' => 'replace',
                    'path' => 'active',
                    'value' => false,
                ],
                [
                    'op' => 'replace',
                    'path' => 'employeeNumber[value eq "OLD-123"]',
                    'value' => 'NEW-456',
                ],
            ],
        ]);

        $response->assertStatus(200);

        $target->refresh();
        $this->assertFalse((bool) $target->activated, 'active=false op must land even though employeeNumber op shares the request.');
        $this->assertSame('NEW-456', $target->employee_num);
    }

    public function test_patch_remove_employee_number_via_value_filter_clears_the_field(): void
    {
        Passport::actingAs(User::factory()->superuser()->create());
        $target = User::factory()->create(['employee_num' => 'KEEP-999']);

        $response = $this->patchJson('/scim/v2/Users/' . $target->id, [
            'schemas' => ['urn:ietf:params:scim:api:messages:2.0:PatchOp'],
            'Operations' => [
                [
                    'op' => 'remove',
                    'path' => 'employeeNumber[value eq "KEEP-999"]',
                ],
            ],
        ]);

        $response->assertStatus(200);
        $target->refresh();
        $this->assertNull($target->employee_num);
    }

    public function test_patch_replace_without_filter_still_works(): void
    {
        // Belt-and-suspenders: the bare `employeeNumber` path (no value
        // filter) uses the attribute-path branch rather than the value-
        // path branch my fix overrides. Pinning it so the override
        // doesn't accidentally regress the already-working case.
        Passport::actingAs(User::factory()->superuser()->create());
        $target = User::factory()->create(['employee_num' => null]);

        $response = $this->patchJson('/scim/v2/Users/' . $target->id, [
            'schemas' => ['urn:ietf:params:scim:api:messages:2.0:PatchOp'],
            'Operations' => [
                [
                    'op' => 'replace',
                    'path' => 'urn:ietf:params:scim:schemas:extension:enterprise:2.0:User:employeeNumber',
                    'value' => 'SET-FIRST-TIME',
                ],
            ],
        ]);

        $response->assertStatus(200);
        $target->refresh();
        $this->assertSame('SET-FIRST-TIME', $target->employee_num);
    }
}
