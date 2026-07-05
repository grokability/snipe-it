<?php

namespace Tests\Feature\Companies\Api;

use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

/**
 * Verifies the JS-visible flag that drives the bulk-delete checkbox on the
 * companies index. The bootstrap-table `checkboxEnabledFormatter` reads
 * `available_actions.bulk_selectable.delete` and disables the row's checkbox
 * when every entry there is false. A company with any associated record must
 * therefore report `bulk_selectable.delete === false`; a clean company must
 * report `true`.
 */
class BulkSelectableTest extends TestCase
{
    public function test_clean_company_is_bulk_selectable()
    {
        $company = Company::factory()->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.companies.show', $company))
            ->assertOk()
            ->assertJsonPath('available_actions.bulk_selectable.delete', true);
    }

    public function test_company_with_assets_is_not_bulk_selectable()
    {
        $company = Company::factory()->create();
        Asset::factory()->create(['company_id' => $company->id]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.companies.show', $company))
            ->assertOk()
            ->assertJsonPath('available_actions.bulk_selectable.delete', false);
    }

    public function test_company_with_users_is_not_bulk_selectable()
    {
        $company = Company::factory()->create();
        $company->users()->syncWithoutDetaching([User::factory()->create()->id]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.companies.show', $company))
            ->assertOk()
            ->assertJsonPath('available_actions.bulk_selectable.delete', false);
    }

    public function test_parent_company_with_children_is_not_bulk_selectable()
    {
        $parent = Company::factory()->create();
        Company::factory()->create(['parent_id' => $parent->id]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.companies.show', $parent))
            ->assertOk()
            ->assertJsonPath('available_actions.bulk_selectable.delete', false);
    }
}
