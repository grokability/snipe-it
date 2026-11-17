<?php

namespace Tests\Feature\Scim;

use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;


class PatchAddressSubAttributeTest extends TestCase
{
    public function test_patch_updates_work_address_street_via_sub_attribute_path(): void
    {
        Passport::actingAs(User::factory()->superuser()->create());
        $target = User::factory()->create([
            'address' => '123 Old Street',
            'city' => 'Portland',
        ]);

        $response = $this->patchJson('/scim/v2/Users/'.$target->id, [
            'schemas' => ['urn:ietf:params:scim:api:messages:2.0:PatchOp'],
            'Operations' => [
                [
                    'op' => 'replace',
                    'path' => 'addresses[type eq "work"].streetAddress',
                    'value' => '456 New Avenue',
                ],
            ],
        ]);

        $response->assertStatus(200);
        $target->refresh();
        $this->assertSame('456 New Avenue', $target->address);
        $this->assertSame('Portland', $target->city, 'Only streetAddress was in the op; city must stay unchanged.');
    }

    public function test_patch_updates_every_work_address_sub_attribute_in_a_single_request(): void
    {
        // Reported case: Entra ID sends one PATCH with a separate op
        // per sub-attribute.
        Passport::actingAs(User::factory()->superuser()->create());
        $target = User::factory()->create([
            'address' => null,
            'city' => null,
            'state' => null,
            'zip' => null,
            'country' => null,
        ]);

        $response = $this->patchJson('/scim/v2/Users/'.$target->id, [
            'schemas' => ['urn:ietf:params:scim:api:messages:2.0:PatchOp'],
            'Operations' => [
                ['op' => 'replace', 'path' => 'addresses[type eq "work"].streetAddress', 'value' => '1 Infinite Loop'],
                ['op' => 'replace', 'path' => 'addresses[type eq "work"].locality', 'value' => 'Cupertino'],
                ['op' => 'replace', 'path' => 'addresses[type eq "work"].region', 'value' => 'CA'],
                ['op' => 'replace', 'path' => 'addresses[type eq "work"].postalCode', 'value' => '95014'],
                ['op' => 'replace', 'path' => 'addresses[type eq "work"].country', 'value' => 'US'],
            ],
        ]);

        $response->assertStatus(200);
        $target->refresh();
        $this->assertSame('1 Infinite Loop', $target->address);
        $this->assertSame('Cupertino', $target->city);
        $this->assertSame('CA', $target->state);
        $this->assertSame('95014', $target->zip);
        $this->assertSame('US', $target->country);
    }

    public function test_patch_add_work_address_sub_attribute_sets_a_previously_null_field(): void
    {
        Passport::actingAs(User::factory()->superuser()->create());
        $target = User::factory()->create(['city' => null]);

        $response = $this->patchJson('/scim/v2/Users/'.$target->id, [
            'schemas' => ['urn:ietf:params:scim:api:messages:2.0:PatchOp'],
            'Operations' => [
                [
                    'op' => 'add',
                    'path' => 'addresses[type eq "work"].locality',
                    'value' => 'Boston',
                ],
            ],
        ]);

        $response->assertStatus(200);
        $target->refresh();
        $this->assertSame('Boston', $target->city);
    }
}
