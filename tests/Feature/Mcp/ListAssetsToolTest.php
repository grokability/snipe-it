<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ListAssetsTool;
use App\Models\Asset;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ListAssetsToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewAssets()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new ListAssetsTool)->handle(new Request($args));
    }

    public function test_returns_total_assets_and_pagination_metadata()
    {
        Asset::factory()->count(3)->create();

        $content = $this->handle()->getStructuredContent();

        $this->assertArrayHasKey('total', $content);
        $this->assertArrayHasKey('assets', $content);
        $this->assertArrayHasKey('limit', $content);
        $this->assertArrayHasKey('offset', $content);
        $this->assertGreaterThanOrEqual(3, $content['total']);
    }

    public function test_each_asset_includes_key_fields()
    {
        Asset::factory()->create();

        $content = $this->handle()->getStructuredContent();

        $asset = $content['assets'][0];
        $this->assertArrayHasKey('id', $asset);
        $this->assertArrayHasKey('asset_tag', $asset);
        $this->assertArrayHasKey('status', $asset);
        $this->assertArrayHasKey('status_type', $asset);
        $this->assertArrayHasKey('model', $asset);
        $this->assertArrayHasKey('location', $asset);
    }

    public function test_excludes_archived_assets_by_default()
    {
        $rtdAsset = Asset::factory()->create();
        $archivedAsset = Asset::factory()->create([
            'status_id' => Statuslabel::factory()->archived()->create()->id,
        ]);

        $content = $this->handle()->getStructuredContent();
        $ids = array_column($content['assets'], 'id');

        $this->assertContains($rtdAsset->id, $ids);
        $this->assertNotContains($archivedAsset->id, $ids);
    }

    public function test_rtd_filter_excludes_deployed_assets()
    {
        $rtdAsset = Asset::factory()->create();
        $deployedAsset = Asset::factory()->assignedToUser()->create();

        $content = $this->handle(['status_type' => 'RTD'])->getStructuredContent();
        $ids = array_column($content['assets'], 'id');

        $this->assertContains($rtdAsset->id, $ids);
        $this->assertNotContains($deployedAsset->id, $ids);
    }

    public function test_deployed_filter_excludes_rtd_assets()
    {
        $user = User::factory()->create();
        $deployedAsset = Asset::factory()->assignedToUser($user)->create();
        $rtdAsset = Asset::factory()->create();

        $content = $this->handle(['status_type' => 'Deployed'])->getStructuredContent();
        $ids = array_column($content['assets'], 'id');

        $this->assertContains($deployedAsset->id, $ids);
        $this->assertNotContains($rtdAsset->id, $ids);
    }

    public function test_archived_filter_returns_only_archived_assets()
    {
        $archivedStatus = Statuslabel::factory()->archived()->create();
        $archivedAsset = Asset::factory()->create(['status_id' => $archivedStatus->id]);
        $rtdAsset = Asset::factory()->create();

        $content = $this->handle(['status_type' => 'Archived'])->getStructuredContent();
        $ids = array_column($content['assets'], 'id');

        $this->assertContains($archivedAsset->id, $ids);
        $this->assertNotContains($rtdAsset->id, $ids);
    }

    public function test_limit_controls_number_of_results_returned()
    {
        Asset::factory()->count(10)->create();

        $content = $this->handle(['limit' => 3])->getStructuredContent();

        $this->assertCount(3, $content['assets']);
        $this->assertGreaterThan(3, $content['total']);
    }

    public function test_offset_skips_results_for_pagination()
    {
        Asset::factory()->count(10)->create();

        $page1 = $this->handle(['limit' => 4, 'offset' => 0])->getStructuredContent();
        $page2 = $this->handle(['limit' => 4, 'offset' => 4])->getStructuredContent();

        $ids1 = array_column($page1['assets'], 'id');
        $ids2 = array_column($page2['assets'], 'id');

        $this->assertEmpty(array_intersect($ids1, $ids2));
    }

    public function test_search_matches_on_asset_tag()
    {
        $target = Asset::factory()->create(['asset_tag' => 'UNIQ-SEARCH-XYZ']);
        Asset::factory()->create(['asset_tag' => 'COMPLETELY-DIFFERENT']);

        $content = $this->handle(['search' => 'UNIQ-SEARCH-XYZ'])->getStructuredContent();
        $ids = array_column($content['assets'], 'id');

        $this->assertContains($target->id, $ids);
    }

    public function test_filters_by_location_id()
    {
        $location = Location::factory()->create();
        $matchingAsset = Asset::factory()->create(['location_id' => $location->id]);
        $otherAsset = Asset::factory()->create();

        $content = $this->handle(['location_id' => $location->id])->getStructuredContent();
        $ids = array_column($content['assets'], 'id');

        $this->assertContains($matchingAsset->id, $ids);
        $this->assertNotContains($otherAsset->id, $ids);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle()->responses()->first()->isError());
    }
}
