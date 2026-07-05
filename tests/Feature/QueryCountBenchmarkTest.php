<?php

/**
 * Query-count regression guards for the performance optimisations shipped in this PR.
 *
 * Three classes of regressions are guarded against:
 *
 * 1. **Locations API N+1** – LocationsTransformer calls transformUser($location->manager),
 *    which calls isDeletable(), which fires 6 count queries per manager unless the counts
 *    are eagerly loaded. The fix eager-loads manager withCount in the controller.
 *
 * 2. **Asset-scope correlated subqueries** – scopeRTD / scopePending / scopeArchived /
 *    scopeAssetsForShow all used whereHas('status', …). In a withCount context MySQL
 *    evaluates the EXISTS subquery once per outer row, producing O(n) query work.
 *    The fix plucks status-label IDs to PHP first so MySQL sees a flat IN (1, 2, 3).
 *
 * 3. **Sidebar count on every web request** – the old AssetCountForSidebar middleware
 *    fired on every web request including modals and select2 AJAX calls, adding ~14
 *    count queries each time. The fix moves the counts into a View Composer bound to
 *    layouts.default, so they only fire when a full page renders.
 *
 * Each test asserts a ceiling; the ceilings are intentionally a bit generous so minor
 * schema additions don't break CI, but they are tight enough to catch a regression back
 * to the old O(n) behaviour.
 *
 * Run with:
 *   php artisan test tests/Feature/QueryCountBenchmarkTest.php --verbose
 */

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QueryCountBenchmarkTest extends TestCase
{
    // ---------------------------------------------------------------------------
    // Locations API – N+1 on manager counts
    // ---------------------------------------------------------------------------

    /**
     * With one shared manager across N locations, the old code fired 6 queries per
     * location (one for each count in isDeletable). With eager-loaded manager counts
     * the total should be roughly constant regardless of N.
     */
    public function test_locations_api_index_does_not_n_plus_1_on_manager_counts(): void
    {
        $manager = User::factory()->superuser()->create();

        // 10 locations all sharing one manager — the worst case for N+1 fan-out.
        Location::factory()->count(10)->create(['manager_id' => $manager->id]);

        $actor = User::factory()->superuser()->create();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->actingAsForApi($actor)
            ->getJson(route('api.locations.index', ['limit' => 50, 'offset' => 0]))
            ->assertOk();

        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Without eager loading this would be ≥ 10×6 = 60 extra queries just for manager counts.
        // With the fix the manager is loaded once and counts are embedded in that query.
        $this->assertLessThan(25, $queryCount,
            "Locations index query count ({$queryCount}) suggests N+1 on manager counts. "
            .'Ensure LocationsController eager-loads manager withCount.');
    }

    /**
     * Scaling check: doubling the location count should not double the query count.
     */
    public function test_locations_api_index_query_count_does_not_scale_with_location_count(): void
    {
        $manager = User::factory()->superuser()->create();
        $actor = User::factory()->superuser()->create();

        // Measure with 5 locations
        Location::factory()->count(5)->create(['manager_id' => $manager->id]);

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAsForApi($actor)
            ->getJson(route('api.locations.index', ['limit' => 50, 'offset' => 0]))
            ->assertOk();
        $countWith5 = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Add 15 more (total 20)
        Location::factory()->count(15)->create(['manager_id' => $manager->id]);

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAsForApi($actor)
            ->getJson(route('api.locations.index', ['limit' => 50, 'offset' => 0]))
            ->assertOk();
        $countWith20 = count(DB::getQueryLog());
        DB::disableQueryLog();

        // If N+1 is present, going from 5→20 locations (4×) would add ~90 queries.
        // With the fix the increase should be negligible (within 5 queries).
        $this->assertLessThan($countWith5 + 5, $countWith20,
            "Locations index fired {$countWith5} queries for 5 locations and {$countWith20} for 20. "
            .'This looks like N+1 on manager counts.');
    }

    // ---------------------------------------------------------------------------
    // Asset model API – correlated-subquery scopes used in withCount
    // ---------------------------------------------------------------------------

    /**
     * AssetModel::withCount(['assets', 'availableAssets', 'archivedAssets']) uses
     * the RTD / Archived scopes. Without the pluck+whereIn fix each row generates
     * correlated EXISTS subqueries evaluated per row by MySQL — invisible in query
     * count but catastrophic for runtime. We can't measure execution time reliably
     * in tests, so instead we assert that the SQL contains no EXISTS subquery shape
     * and that the query count is flat.
     */
    public function test_asset_model_index_query_count_is_flat(): void
    {
        $category = Category::factory()->create(['category_type' => 'asset']);
        AssetModel::factory()->count(5)->create(['category_id' => $category->id]);
        $actor = User::factory()->superuser()->create();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->actingAsForApi($actor)
            ->getJson(route('api.models.index', ['limit' => 50, 'offset' => 0]))
            ->assertOk();

        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThan(25, $queryCount,
            "Asset model index fired {$queryCount} queries; expected < 25. "
            .'Check that availableAssets/archivedAssets scopes use pluck+whereIn, not whereHas.');
    }

    // ---------------------------------------------------------------------------
    // RTD / Pending / Archived scope SQL shapes
    // ---------------------------------------------------------------------------

    /**
     * These four tests verify the SQL produced by each scope does NOT contain a
     * correlated EXISTS (which would indicate a regression back to whereHas).
     * They also confirm the IN clause IS present.
     */
    public function test_scope_rtd_uses_where_in_not_exists(): void
    {
        $sql = Asset::RTD()->toSql();
        $this->assertStringNotContainsString('exists (select', strtolower($sql),
            'scopeRTD must not use a correlated EXISTS subquery.');
        $this->assertStringContainsString('in (', strtolower($sql),
            'scopeRTD should use a flat IN clause from plucked IDs.');
    }

    public function test_scope_pending_uses_where_in_not_exists(): void
    {
        $sql = Asset::Pending()->toSql();
        $this->assertStringNotContainsString('exists (select', strtolower($sql),
            'scopePending must not use a correlated EXISTS subquery.');
        $this->assertStringContainsString('in (', strtolower($sql),
            'scopePending should use a flat IN clause from plucked IDs.');
    }

    public function test_scope_archived_uses_where_in_not_exists(): void
    {
        $sql = Asset::Archived()->toSql();
        $this->assertStringNotContainsString('exists (select', strtolower($sql),
            'scopeArchived must not use a correlated EXISTS subquery.');
        $this->assertStringContainsString('in (', strtolower($sql),
            'scopeArchived should use a flat IN clause from plucked IDs.');
    }

    public function test_scope_undeployable_uses_where_in_not_exists(): void
    {
        $sql = Asset::Undeployable()->toSql();
        $this->assertStringNotContainsString('exists (select', strtolower($sql),
            'scopeUndeployable must not use a correlated EXISTS subquery.');
        $this->assertStringContainsString('in (', strtolower($sql),
            'scopeUndeployable should use a flat IN clause from plucked IDs.');
    }

    public function test_scope_assets_for_show_uses_where_in_not_exists(): void
    {
        $sql = Asset::AssetsForShow()->toSql();
        // When show_archived_in_list is off (default), the scope adds a whereIn.
        // When it's on, no filter is added at all — both are fine, neither should have EXISTS.
        $this->assertStringNotContainsString('exists (select', strtolower($sql),
            'scopeAssetsForShow must not use a correlated EXISTS subquery.');
    }

    // ---------------------------------------------------------------------------
    // Sidebar composer – skips non-full-page requests
    // ---------------------------------------------------------------------------

    /**
     * A modal or select2 AJAX endpoint should not trigger the sidebar asset counts.
     * We verify this indirectly by checking that hitting a selectlist endpoint
     * does not fire the ~14 count queries that the old middleware would have fired.
     *
     * The selectlist endpoints are purely JSON, never render layouts.default,
     * so the SidebarComposer must not fire for them.
     */
    public function test_selectlist_endpoint_does_not_fire_sidebar_counts(): void
    {
        Statuslabel::factory()->count(3)->create();
        $actor = User::factory()->superuser()->create();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->actingAsForApi($actor)
            ->getJson(route('api.locations.selectlist'))
            ->assertOk();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $queryCount = count($queries);

        // The old middleware would fire ~14 asset count queries on every request.
        // A selectlist endpoint needs only a handful of queries (auth + the list query).
        // We assert fewer than 15 to confirm the sidebar counts are not being run.
        $this->assertLessThan(15, $queryCount,
            "Selectlist endpoint fired {$queryCount} queries. "
            .'This may indicate the sidebar composer (or old middleware) is firing on AJAX requests. '
            .'The SidebarComposer should only fire when layouts.default renders.');

        // Confirm none of the queries counted RTD/Deployed/Archived/Pending — those
        // are the tell-tale sidebar count queries.
        $sidebarKeywords = ['rtd_assets', 'byod', 'overdue_for_checkin', 'due_for_audit'];
        foreach ($queries as $query) {
            $sql = strtolower($query['query']);
            foreach ($sidebarKeywords as $keyword) {
                $this->assertStringNotContainsString($keyword, $sql,
                    "Selectlist endpoint ran a sidebar-related query containing '{$keyword}'. "
                    .'The SidebarComposer should only fire when layouts.default renders.');
            }
        }
    }
}
