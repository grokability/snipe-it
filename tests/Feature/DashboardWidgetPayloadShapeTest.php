<?php

namespace Tests\Feature;

use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Company;
use App\Models\License;
use App\Models\Location;
use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * Payload-shape tests for the non-admin dashboard widget endpoints.
 * Split from DashboardWidgetVisibilityTest so each file stays under
 * PHPMD's per-class method threshold. Covers per-viewer-type
 * filtering on the activity feed, the available_actions.view flag
 * on lookup rows (Category / Company / Location), and the
 * category-type filter for scoped viewers.
 */
class DashboardWidgetPayloadShapeTest extends TestCase
{
    public function test_scoped_activity_viewer_only_sees_actionlogs_on_types_they_can_view(): void
    {
        // Seed one actionlog per relevant type. A user with only
        // accessories.view should see the Accessory row and nothing
        // else through the dashboard's recent-activity feed.
        $asset = Asset::factory()->create();
        $accessory = Accessory::factory()->create();
        $license = License::factory()->create();

        Actionlog::factory()->create([
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'action_type' => 'checkout',
            'note' => 'SENTINEL_ASSET_ROW',
        ]);
        Actionlog::factory()->create([
            'item_type' => Accessory::class,
            'item_id' => $accessory->id,
            'action_type' => 'checkout',
            'note' => 'SENTINEL_ACCESSORY_ROW',
        ]);
        Actionlog::factory()->create([
            'item_type' => License::class,
            'item_id' => $license->id,
            'action_type' => 'checkout',
            'note' => 'SENTINEL_LICENSE_ROW',
        ]);

        Passport::actingAs(User::factory()->viewAccessories()->create());

        $response = $this->getJson(route('api.dashboard.activity'))->assertOk();
        $body = $response->getContent();

        $this->assertStringContainsString('SENTINEL_ACCESSORY_ROW', $body);
        $this->assertStringNotContainsString('SENTINEL_ASSET_ROW', $body);
        $this->assertStringNotContainsString('SENTINEL_LICENSE_ROW', $body);
    }

    public function test_scoped_viewer_sees_available_actions_view_false_on_lookup_rows(): void
    {
        // Row-level view flag drives the dashboard formatter's
        // decision to render the name as plain text (no link) so a
        // scoped viewer clicking through does not 403 on the show
        // controller they cannot reach. Same shape on Categories,
        // Locations, and Companies transformers. Category needs to
        // be an accessory-typed one so it survives the category_type
        // filter the API applies for scoped viewers.
        Category::factory()->create(['category_type' => 'accessory']);
        Location::factory()->create();
        Company::factory()->create();

        Passport::actingAs(User::factory()->viewAccessories()->create());

        $categoryRow = $this->getJson(route('api.dashboard.categories'))->assertOk()->json('rows.0');
        $locationRow = $this->getJson(route('api.dashboard.locations'))->assertOk()->json('rows.0');
        $companyRow = $this->getJson(route('api.dashboard.companies'))->assertOk()->json('rows.0');

        $this->assertFalse($categoryRow['available_actions']['view']);
        $this->assertFalse($locationRow['available_actions']['view']);
        $this->assertFalse($companyRow['available_actions']['view']);
    }

    public function test_scoped_categories_filter_excludes_types_the_viewer_cannot_view(): void
    {
        // Accessory-only viewer should see accessory-typed categories
        // but not asset / license / consumable / component ones. The
        // filter widens back out to unfiltered when the caller has
        // explicit categories.view.
        Category::factory()->create(['name' => 'Asset Cat', 'category_type' => 'asset']);
        Category::factory()->create(['name' => 'Accessory Cat', 'category_type' => 'accessory']);
        Category::factory()->create(['name' => 'License Cat', 'category_type' => 'license']);

        Passport::actingAs(User::factory()->viewAccessories()->create());

        $rows = $this->getJson(route('api.dashboard.categories'))->assertOk()->json('rows');
        $names = collect($rows)->pluck('name')->all();

        $this->assertContains('Accessory Cat', $names);
        $this->assertNotContains('Asset Cat', $names);
        $this->assertNotContains('License Cat', $names);
    }

    public function test_activity_row_carries_viewable_flag_per_item(): void
    {
        // Recent-activity's polymorphicItemFormatter renders name +
        // link when the caller can view the record, plain text when
        // they cannot. The flag flows through the transformer as
        // item.viewable / target.viewable on each row so a scoped
        // viewer's dashboard clicks do not 403.
        $accessory = Accessory::factory()->create();

        Actionlog::factory()->create([
            'item_type' => Accessory::class,
            'item_id' => $accessory->id,
            'action_type' => 'checkout',
        ]);

        Passport::actingAs(User::factory()->viewAccessories()->create());

        $rows = $this->getJson(route('api.dashboard.activity'))->assertOk()->json('rows');
        $accessoryRows = collect($rows)->filter(fn ($r) => ($r['item']['type'] ?? null) === 'accessory')->values()->all();
        $this->assertNotEmpty($accessoryRows, 'accessory row must survive the scoped-viewer filter');
        foreach ($accessoryRows as $row) {
            $this->assertTrue($row['item']['viewable']);
        }
    }

    public function test_admin_sees_available_actions_view_true_on_lookup_rows(): void
    {
        Category::factory()->create();
        Location::factory()->create();
        Company::factory()->create();

        Passport::actingAs(User::factory()->superuser()->create());

        $categoryRow = $this->getJson(route('api.dashboard.categories'))->assertOk()->json('rows.0');
        $locationRow = $this->getJson(route('api.dashboard.locations'))->assertOk()->json('rows.0');
        $companyRow = $this->getJson(route('api.dashboard.companies'))->assertOk()->json('rows.0');

        $this->assertTrue($categoryRow['available_actions']['view']);
        $this->assertTrue($locationRow['available_actions']['view']);
        $this->assertTrue($companyRow['available_actions']['view']);
    }

    public function test_dashboard_categories_endpoint_filters_rows_by_search_param()
    {
        // Issue #19979: the bs-table filter field on the dashboard widget sends `search` to the server. Pre-fix the controller ignored it and returned every row, which the UI rendered as a stale/no-match state.
        Category::factory()->create(['name' => 'MATCH_ME_FILTER', 'category_type' => 'asset']);
        Category::factory()->create(['name' => 'OTHER_CATEGORY', 'category_type' => 'asset']);

        Passport::actingAs(User::factory()->superuser()->create());

        $rows = $this->getJson(route('api.dashboard.categories', ['search' => 'MATCH_ME_FILTER']))
            ->assertOk()
            ->json('rows');

        $names = collect($rows)->pluck('name')->all();
        $this->assertContains('MATCH_ME_FILTER', $names);
        $this->assertNotContains('OTHER_CATEGORY', $names);
    }

    public function test_dashboard_companies_endpoint_filters_rows_by_search_param()
    {
        Company::factory()->create(['name' => 'MATCH_ME_FILTER']);
        Company::factory()->create(['name' => 'OTHER_COMPANY']);

        Passport::actingAs(User::factory()->superuser()->create());

        $rows = $this->getJson(route('api.dashboard.companies', ['search' => 'MATCH_ME_FILTER']))
            ->assertOk()
            ->json('rows');

        $names = collect($rows)->pluck('name')->all();
        $this->assertContains('MATCH_ME_FILTER', $names);
        $this->assertNotContains('OTHER_COMPANY', $names);
    }

    public function test_dashboard_locations_endpoint_filters_rows_by_search_param()
    {
        Location::factory()->create(['name' => 'MATCH_ME_FILTER']);
        Location::factory()->create(['name' => 'OTHER_LOCATION']);

        Passport::actingAs(User::factory()->superuser()->create());

        $rows = $this->getJson(route('api.dashboard.locations', ['search' => 'MATCH_ME_FILTER']))
            ->assertOk()
            ->json('rows');

        $names = collect($rows)->pluck('name')->all();
        $this->assertContains('MATCH_ME_FILTER', $names);
        $this->assertNotContains('OTHER_LOCATION', $names);
    }
}
