<?php

namespace Tests\Feature\Consumables\Ui;

use App\Models\Company;
use App\Models\Consumable;
use App\Models\User;
use Tests\TestCase;

/**
 * GHSA-rhwq-w7gg-rwr6 / GHSA-3prj-5x7r-hx5r. See the sibling
 * BulkEditNullCompanyIdTest files for the full context.
 */
class BulkEditNullCompanyIdTest extends TestCase
{
    public function test_non_superuser_cannot_null_company_id_via_ui_bulk_edit_under_fmcs()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $actor = $company->users()->save(
            User::factory()->editConsumables()->viewConsumables()->make()
        );
        $consumable = Consumable::factory()->for($company, 'company')->create();

        $this->actingAs($actor)
            ->post(route('consumables.bulkedit.store'), [
                'ids' => [$consumable->id],
                'null_company_id' => '1',
            ])
            ->assertRedirect(route('consumables.index'));

        $this->assertEquals($company->id, $consumable->fresh()->company_id);
    }

    public function test_non_superuser_cannot_null_company_id_via_ui_bulk_edit_in_floater_mode()
    {
        $this->settings->enableFloaterMode();

        $company = Company::factory()->create();
        $actor = $company->users()->save(
            User::factory()->editConsumables()->viewConsumables()->make()
        );
        $consumable = Consumable::factory()->for($company, 'company')->create();

        $this->actingAs($actor)
            ->post(route('consumables.bulkedit.store'), [
                'ids' => [$consumable->id],
                'null_company_id' => '1',
            ])
            ->assertRedirect(route('consumables.index'));

        $this->assertEquals($company->id, $consumable->fresh()->company_id);
    }

    public function test_superuser_can_still_null_company_id_via_ui_bulk_edit()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $consumable = Consumable::factory()->for($company, 'company')->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('consumables.bulkedit.store'), [
                'ids' => [$consumable->id],
                'null_company_id' => '1',
            ])
            ->assertRedirect(route('consumables.index'));

        $this->assertNull($consumable->fresh()->company_id);
    }
}
