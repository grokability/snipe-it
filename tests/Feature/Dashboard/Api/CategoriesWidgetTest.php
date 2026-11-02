<?php

namespace Tests\Feature\Dashboard\Api;

use App\Models\Accessory;
use App\Models\Category;
use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * Narrow endpoint backing the dashboard Categories summary widget.
 * See Api\DashboardController for the auth + payload rationale that
 * splits this from the general-purpose api.categories.index.
 */
class CategoriesWidgetTest extends TestCase
{
    public function test_user_without_any_view_permission_is_forbidden()
    {
        Passport::actingAs(User::factory()->create());

        $this->getJson(route('api.dashboard.categories'))
            ->assertForbidden();
    }

    public function test_scoped_viewer_sees_only_matching_category_types()
    {
        Category::factory()->create(['name' => 'Asset Cat', 'category_type' => 'asset']);
        Category::factory()->create(['name' => 'Accessory Cat', 'category_type' => 'accessory']);
        Category::factory()->create(['name' => 'License Cat', 'category_type' => 'license']);

        Passport::actingAs(User::factory()->viewAccessories()->create());

        $rows = $this->getJson(route('api.dashboard.categories'))
            ->assertOk()
            ->json('rows');
        $names = collect($rows)->pluck('name')->all();

        $this->assertContains('Accessory Cat', $names);
        $this->assertNotContains('Asset Cat', $names);
        $this->assertNotContains('License Cat', $names);
    }

    public function test_scoped_viewer_only_gets_count_columns_for_viewable_types()
    {
        Category::factory()->create(['category_type' => 'accessory']);

        Passport::actingAs(User::factory()->viewAccessories()->create());

        $row = $this->getJson(route('api.dashboard.categories'))
            ->assertOk()
            ->json('rows.0');

        $this->assertArrayHasKey('accessories_count', $row);
        $this->assertArrayNotHasKey('assets_count', $row);
        $this->assertArrayNotHasKey('consumables_count', $row);
        $this->assertArrayNotHasKey('components_count', $row);
        $this->assertArrayNotHasKey('licenses_count', $row);
    }

    public function test_row_carries_available_actions_view_flag()
    {
        Category::factory()->create(['category_type' => 'accessory']);

        Passport::actingAs(User::factory()->viewAccessories()->create());

        $row = $this->getJson(route('api.dashboard.categories'))
            ->assertOk()
            ->json('rows.0');

        $this->assertFalse($row['available_actions']['view']);
    }

    public function test_stale_sort_column_for_type_the_viewer_cannot_see_falls_back()
    {
        // See LocationsWidgetTest for full rationale. localStorage-
        // persisted bs-table sort state can name a *_count column the
        // current caller does not have. Falling back to name keeps
        // the widget rendering.
        Category::factory()->count(2)->create(['category_type' => 'component']);

        Passport::actingAs(User::factory()->viewComponents()->create());

        $this->getJson(route('api.dashboard.categories', ['sort' => 'accessories_count', 'order' => 'desc']))
            ->assertOk();
    }

    public function test_search_param_filters_rows_but_other_params_are_ignored()
    {
        // Issue #19979: `search` was ignored. Everything else still is.
        Category::factory()->count(3)->create(['category_type' => 'accessory']);

        Passport::actingAs(User::factory()->viewAccessories()->create());

        $unfilteredTotal = $this->getJson(route('api.dashboard.categories'))
            ->assertOk()
            ->json('total');

        $searchHonoredTotal = $this->getJson(route('api.dashboard.categories', ['search' => 'never-matches-anything']))
            ->assertOk()
            ->json('total');
        $this->assertSame(0, $searchHonoredTotal);

        $extraParamsIgnoredTotal = $this->getJson(route('api.dashboard.categories', [
            'filter' => 'never-matches-anything',
            'name' => 'never-matches-anything',
            'created_by' => 999_999,
        ]))
            ->assertOk()
            ->json('total');
        $this->assertSame($unfilteredTotal, $extraParamsIgnoredTotal);
    }

    public function test_limit_caps_at_fifty()
    {
        Accessory::factory()->count(0)->create();
        Category::factory()->count(60)->create(['category_type' => 'accessory']);

        Passport::actingAs(User::factory()->viewAccessories()->create());

        $rows = $this->getJson(route('api.dashboard.categories', ['limit' => 500]))
            ->assertOk()
            ->json('rows');

        $this->assertLessThanOrEqual(50, count($rows));
    }
}
