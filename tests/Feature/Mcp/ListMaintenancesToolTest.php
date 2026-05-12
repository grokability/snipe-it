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
            $this->assertEquals($asset1->id, $m['asset_id']);
        }
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle()->responses()->first()->isError());
    }
}
