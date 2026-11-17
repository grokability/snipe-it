<?php

namespace Tests\Feature\Components\Api;

use App\Models\Company;
use App\Models\Component;
use App\Models\User;
use Tests\TestCase;

/**
 * Mirrors the accessory + consumable regression. Same root-cause
 * bulk-edit flow, same fix: non-superusers cannot submit
 * `null_company_id=1` to detach a component from its tenant.
 */
class BulkEditNullCompanyIdTest extends TestCase
{
    public function test_non_superuser_cannot_null_company_id_via_bulk_edit_under_fmcs()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $actor = $company->users()->save(
            User::factory()->editComponents()->viewComponents()->make()
        );
        $component = Component::factory()->for($company)->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.components.bulkedit'), [
                'ids' => [$component->id],
                'null_company_id' => true,
            ])
            ->assertStatus(422);

        $this->assertEquals($company->id, $component->fresh()->company_id);
    }

    public function test_non_superuser_cannot_null_company_id_via_bulk_edit_in_floater_mode()
    {
        $this->settings->enableFloaterMode();

        $company = Company::factory()->create();
        $actor = $company->users()->save(
            User::factory()->editComponents()->viewComponents()->make()
        );
        $component = Component::factory()->for($company)->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.components.bulkedit'), [
                'ids' => [$component->id],
                'null_company_id' => true,
            ])
            ->assertStatus(422);

        $this->assertEquals($company->id, $component->fresh()->company_id);
    }

    public function test_superuser_can_still_null_company_id_via_bulk_edit()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $component = Component::factory()->for($company)->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.components.bulkedit'), [
                'ids' => [$component->id],
                'null_company_id' => true,
            ])
            ->assertOk();

        $this->assertNull($component->fresh()->company_id);
    }

    public function test_non_superuser_can_still_bulk_edit_other_fields()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $actor = $company->users()->save(
            User::factory()->editComponents()->viewComponents()->make()
        );
        $component = Component::factory()->for($company)->create(['min_amt' => 1]);

        $this->actingAsForApi($actor)
            ->postJson(route('api.components.bulkedit'), [
                'ids' => [$component->id],
                'min_amt' => 42,
            ])
            ->assertOk();

        $this->assertSame(42, (int) $component->fresh()->min_amt);
        $this->assertEquals($company->id, $component->fresh()->company_id);
    }
}
