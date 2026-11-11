<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ListMaintenancesTool;
use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ListMaintenancesToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewAssets()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new ListMaintenancesTool)->handle(new Request($args));
    }

    public function test_returns_maintenances_with_pagination()
    {
        $asset = Asset::factory()->laptopZenbook()->create();
        Maintenance::factory()->create(['asset_id' => $asset->id]);

        $content = $this->handle()->getStructuredContent();

        $this->assertArrayHasKey('maintenances', $content);
        $this->assertArrayHasKey('total', $content);
        $this->assertGreaterThanOrEqual(1, $content['total']);
    }

    public function test_filters_by_asset_id()
    {
        $asset1 = Asset::factory()->laptopZenbook()->create();
        $asset2 = Asset::factory()->laptopZenbook()->create();

        $maintenance1 = Maintenance::factory()->create(['asset_id' => $asset1->id]);
        Maintenance::factory()->create(['asset_id' => $asset2->id]);

        $content = $this->handle(['asset_id' => $asset1->id])->getStructuredContent();

        $this->assertGreaterThanOrEqual(1, $content['total']);
        foreach ($content['maintenances'] as $m) {
            // The response emits the polymorphic item_id (same value as
            // the legacy asset_id when item_type is Asset) instead of
            // the stale asset_id key.
            $this->assertEquals($asset1->id, $m['item_id']);
        }
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle()->responses()->first()->isError());
    }

    public function test_filters_by_asset_tag()
    {
        $asset = Asset::factory()->laptopZenbook()->create(['asset_tag' => 'FIND-ME']);
        $other = Asset::factory()->laptopZenbook()->create(['asset_tag' => 'OTHER']);
        $matching = Maintenance::factory()->create(['asset_id' => $asset->id]);
        Maintenance::factory()->create(['asset_id' => $other->id]);

        $content = $this->handle(['asset_tag' => 'FIND-ME'])->getStructuredContent();
        $ids = array_column($content['maintenances'], 'id');

        $this->assertContains($matching->id, $ids);
        $this->assertCount(1, $ids);
    }

    public function test_returns_error_when_asset_tag_not_found()
    {
        $this->assertTrue($this->handle(['asset_tag' => 'NOPE'])->responses()->first()->isError());
    }

    public function test_status_open_excludes_completed_maintenances()
    {
        $asset = Asset::factory()->laptopZenbook()->create();
        $open = Maintenance::factory()->create(['asset_id' => $asset->id, 'completed_at' => null]);
        $closed = Maintenance::factory()->create(['asset_id' => $asset->id, 'completed_at' => now()]);

        $content = $this->handle(['status' => 'open'])->getStructuredContent();
        $ids = array_column($content['maintenances'], 'id');

        $this->assertContains($open->id, $ids);
        $this->assertNotContains($closed->id, $ids);
    }

    public function test_status_completed_excludes_open_maintenances()
    {
        $asset = Asset::factory()->laptopZenbook()->create();
        $open = Maintenance::factory()->create(['asset_id' => $asset->id, 'completed_at' => null]);
        $closed = Maintenance::factory()->create(['asset_id' => $asset->id, 'completed_at' => now()]);

        $content = $this->handle(['status' => 'completed'])->getStructuredContent();
        $ids = array_column($content['maintenances'], 'id');

        $this->assertContains($closed->id, $ids);
        $this->assertNotContains($open->id, $ids);
    }

    public function test_is_warranty_filter_scopes_results()
    {
        $asset = Asset::factory()->laptopZenbook()->create();
        $warranty = Maintenance::factory()->create(['asset_id' => $asset->id, 'is_warranty' => true]);
        $paid = Maintenance::factory()->create(['asset_id' => $asset->id, 'is_warranty' => false]);

        $content = $this->handle(['is_warranty' => true])->getStructuredContent();
        $ids = array_column($content['maintenances'], 'id');

        $this->assertContains($warranty->id, $ids);
        $this->assertNotContains($paid->id, $ids);
    }

    public function test_start_date_range_narrows_window()
    {
        $asset = Asset::factory()->laptopZenbook()->create();
        $inWindow = Maintenance::factory()->create([
            'asset_id' => $asset->id,
            'start_date' => '2026-06-15',
        ]);
        $outOfWindow = Maintenance::factory()->create([
            'asset_id' => $asset->id,
            'start_date' => '2026-01-01',
        ]);

        $content = $this->handle([
            'start_date_from' => '2026-06-01',
            'start_date_to' => '2026-06-30',
        ])->getStructuredContent();
        $ids = array_column($content['maintenances'], 'id');

        $this->assertContains($inWindow->id, $ids);
        $this->assertNotContains($outOfWindow->id, $ids);
    }

    public function test_response_includes_item_fields_and_completed_at()
    {
        $asset = Asset::factory()->laptopZenbook()->create();
        $maintenance = Maintenance::factory()->create([
            'asset_id' => $asset->id,
            'completed_at' => '2026-05-10 14:00:00',
        ]);

        $content = $this->handle(['limit' => 500])->getStructuredContent();
        $row = collect($content['maintenances'])->firstWhere('id', $maintenance->id);

        $this->assertNotNull($row);
        $this->assertSame('Asset', $row['item_type']);
        $this->assertSame($asset->id, $row['item_id']);
        $this->assertSame($asset->asset_tag, $row['asset_tag']);
        $this->assertArrayHasKey('expected_completion_date', $row);
        $this->assertArrayHasKey('completed_at', $row);
        $this->assertArrayHasKey('notes', $row);
    }
}
