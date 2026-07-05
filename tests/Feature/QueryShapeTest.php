<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Location;
use Tests\TestCase;

/**
 * Guards against the correlated-subquery footgun.
 *
 * The problem: using whereHas() to filter by status label inside a withCount() relationship
 * generates a nested EXISTS subquery that MySQL re-evaluates for every row. On large datasets
 * (thousands of locations, hundreds of thousands of assets) this causes 30+ second timeouts.
 *
 * The fix: pluck the matching status IDs once in PHP, then use whereIn() with the flat list.
 * MySQL can use the index on assets.status_id directly rather than running a subquery per row.
 *
 * If any test here fails, DO NOT restore whereHas() — you will reintroduce the timeout.
 * See the comment blocks in Asset::scopeRTD(), scopeArchived(), scopePending(),
 * scopeUndeployable(), scopeNotArchived(), scopeAssetsForShow(), and Location::assets().
 */
class QueryShapeTest extends TestCase
{
    private function assertNoCorrelatedExists(string $sql, string $context): void
    {
        $this->assertStringNotContainsString(
            'exists (select',
            strtolower($sql),
            "Correlated EXISTS detected in {$context}. Replace whereHas() with a Statuslabel::pluck()+whereIn() — see comments in the affected scope/relationship."
        );
    }

    // ----- Direct scope SQL shape -----

    public function test_rtd_scope_uses_where_in_not_correlated_exists(): void
    {
        $sql = Asset::RTD()->toSql();
        $this->assertNoCorrelatedExists($sql, 'Asset::RTD()');
        $this->assertStringContainsString('in (', strtolower($sql));
    }

    public function test_pending_scope_uses_where_in_not_correlated_exists(): void
    {
        $sql = Asset::Pending()->toSql();
        $this->assertNoCorrelatedExists($sql, 'Asset::Pending()');
        $this->assertStringContainsString('in (', strtolower($sql));
    }

    public function test_archived_scope_uses_where_in_not_correlated_exists(): void
    {
        $sql = Asset::Archived()->toSql();
        $this->assertNoCorrelatedExists($sql, 'Asset::Archived()');
        $this->assertStringContainsString('in (', strtolower($sql));
    }

    public function test_undeployable_scope_uses_where_in_not_correlated_exists(): void
    {
        $sql = Asset::Undeployable()->toSql();
        $this->assertNoCorrelatedExists($sql, 'Asset::Undeployable()');
        $this->assertStringContainsString('in (', strtolower($sql));
    }

    public function test_not_archived_scope_uses_where_in_not_correlated_exists(): void
    {
        $sql = Asset::NotArchived()->toSql();
        $this->assertNoCorrelatedExists($sql, 'Asset::NotArchived()');
        $this->assertStringContainsString('in (', strtolower($sql));
    }

    public function test_assets_for_show_scope_uses_where_in_not_correlated_exists(): void
    {
        // show_archived_in_list defaults to 0, so the whereIn filter is always applied in tests
        $sql = Asset::AssetsForShow()->toSql();
        $this->assertNoCorrelatedExists($sql, 'Asset::AssetsForShow()');
        $this->assertStringContainsString('in (', strtolower($sql));
    }

    // ----- withCount SQL shape (the real danger zone) -----
    // These test the queries that actually timed out in production.
    // withCount() embeds the relationship query as a correlated subquery —
    // any EXISTS inside it runs once per outer row, not once total.

    public function test_asset_model_available_assets_withcount_uses_where_in_not_correlated_exists(): void
    {
        $sql = AssetModel::withCount('availableAssets as remaining')->toSql();
        $this->assertNoCorrelatedExists($sql, 'AssetModel::withCount(availableAssets)');
    }

    public function test_asset_model_archived_assets_withcount_uses_where_in_not_correlated_exists(): void
    {
        $sql = AssetModel::withCount('archivedAssets as assets_archived_count')->toSql();
        $this->assertNoCorrelatedExists($sql, 'AssetModel::withCount(archivedAssets)');
    }

    public function test_location_assets_withcount_uses_where_in_not_correlated_exists(): void
    {
        $sql = Location::withCount('assets as assets_count')->toSql();
        $this->assertNoCorrelatedExists($sql, 'Location::withCount(assets)');
    }

    public function test_location_assigned_assets_withcount_uses_where_in_not_correlated_exists(): void
    {
        $sql = Location::withCount('assignedAssets as assigned_assets_count')->toSql();
        $this->assertNoCorrelatedExists($sql, 'Location::withCount(assignedAssets)');
    }

    public function test_category_showable_assets_withcount_uses_where_in_not_correlated_exists(): void
    {
        $sql = Category::withCount('showableAssets as assets_count')->toSql();
        $this->assertNoCorrelatedExists($sql, 'Category::withCount(showableAssets)');
    }
}
