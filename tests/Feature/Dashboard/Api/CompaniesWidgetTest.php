<?php

namespace Tests\Feature\Dashboard\Api;

use App\Models\Company;
use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * Narrow endpoint backing the dashboard Companies summary widget.
 * See Api\DashboardController for the auth + payload rationale that
 * splits this from the general-purpose api.companies.index.
 */
class CompaniesWidgetTest extends TestCase
{
    public function test_user_without_any_view_permission_is_forbidden()
    {
        Passport::actingAs(User::factory()->create());

        $this->getJson(route('api.dashboard.companies'))
            ->assertForbidden();
    }

    public function test_scoped_viewer_only_gets_count_columns_for_viewable_types()
    {
        Company::factory()->create();

        Passport::actingAs(User::factory()->viewAccessories()->create());

        $row = $this->getJson(route('api.dashboard.companies'))
            ->assertOk()
            ->json('rows.0');

        $this->assertArrayHasKey('accessories_count', $row);
        $this->assertArrayNotHasKey('assets_count', $row);
        $this->assertArrayNotHasKey('consumables_count', $row);
        $this->assertArrayNotHasKey('components_count', $row);
        $this->assertArrayNotHasKey('licenses_count', $row);
        $this->assertArrayNotHasKey('users_count', $row);
    }

    public function test_row_carries_available_actions_view_flag_and_no_extra_fields()
    {
        Company::factory()->create();

        Passport::actingAs(User::factory()->viewAccessories()->create());

        $row = $this->getJson(route('api.dashboard.companies'))
            ->assertOk()
            ->json('rows.0');

        // Widget-shape whitelist. Anything beyond these fields is a
        // widening that leaks past what the widget renders. Extend
        // this list intentionally when adding a widget column, not
        // accidentally by extending the transformer.
        $allowed = ['id', 'name', 'tag_color', 'available_actions', 'accessories_count'];
        sort($allowed);
        $actual = collect(array_keys($row))->sort()->values()->all();
        $this->assertSame($allowed, $actual);
        $this->assertFalse($row['available_actions']['view']);
    }

    public function test_admin_sees_view_flag_true()
    {
        Company::factory()->create();

        Passport::actingAs(User::factory()->superuser()->create());

        $row = $this->getJson(route('api.dashboard.companies'))
            ->assertOk()
            ->json('rows.0');

        $this->assertTrue($row['available_actions']['view']);
    }

    public function test_stale_sort_column_for_type_the_viewer_cannot_see_falls_back()
    {
        // See LocationsWidgetTest for full rationale.
        Company::factory()->count(2)->create();

        Passport::actingAs(User::factory()->viewComponents()->create());

        $this->getJson(route('api.dashboard.companies', ['sort' => 'accessories_count', 'order' => 'desc']))
            ->assertOk();
    }

    public function test_search_param_filters_rows_but_other_params_are_ignored()
    {
        // Issue #19979: `search` was ignored, which broke the dashboard
        // filter field. Everything else still is ignored (the index
        // controller handles per-column filters, the dashboard widget
        // only wires the free-text search field).
        Company::factory()->count(3)->create();

        Passport::actingAs(User::factory()->viewAccessories()->create());

        $unfilteredTotal = $this->getJson(route('api.dashboard.companies'))
            ->assertOk()
            ->json('total');

        $searchHonoredTotal = $this->getJson(route('api.dashboard.companies', ['search' => 'never-matches-anything']))
            ->assertOk()
            ->json('total');
        $this->assertSame(0, $searchHonoredTotal, 'search param must filter the widget');

        $extraParamsIgnoredTotal = $this->getJson(route('api.dashboard.companies', [
            'filter' => 'never-matches',
            'name' => 'never-matches',
            'email' => 'never-matches',
            'created_by' => 999_999,
            'tag_color' => '#ffffff',
        ]))
            ->assertOk()
            ->json('total');
        $this->assertSame($unfilteredTotal, $extraParamsIgnoredTotal, 'non-search filter params are not wired on the widget');
    }
}
