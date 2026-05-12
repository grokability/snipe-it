<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CreateMaintenanceTool;
use App\Models\Asset;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CreateMaintenanceToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->editAssets()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new CreateMaintenanceTool)->handle(new Request($args));
    }

    public function test_creates_maintenance_for_asset()
    {
        $asset = Asset::factory()->laptopZenbook()->create();

        $this->handle([
            'asset_id' => $asset->id,
            'title' => 'Test Maintenance',
            'start_date' => '2024-01-01',
        ]);

        $this->assertDatabaseHas('maintenances', [
            'asset_id' => $asset->id,
            'name' => 'Test Maintenance',
        ]);
    }

    public function test_response_includes_id_title_and_asset_id()
    {
        $asset = Asset::factory()->laptopZenbook()->create();

        $content = $this->handle([
            'asset_id' => $asset->id,
            'title' => 'Test Maintenance Response',
            'start_date' => '2024-01-01',
        ])->getStructuredContent();

        $this->assertArrayHasKey('id', $content);
        $this->assertArrayHasKey('title', $content);
        $this->assertArrayHasKey('asset_id', $content);
        $this->assertEquals($asset->id, $content['asset_id']);
        $this->assertTrue($content['success']);
    }

    public function test_returns_error_when_asset_id_missing()
    {
        $this->assertTrue($this->handle(['title' => 'Test'])->responses()->first()->isError());
    }

    public function test_returns_error_when_title_missing()
    {
        $asset = Asset::factory()->laptopZenbook()->create();

        $this->assertTrue($this->handle(['asset_id' => $asset->id])->responses()->first()->isError());
    }

    public function test_returns_error_when_asset_not_found()
    {
        $this->assertTrue($this->handle([
            'asset_id' => 999999,
            'title' => 'Test',
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $asset = Asset::factory()->laptopZenbook()->create();

        $this->assertTrue($this->handle([
            'asset_id' => $asset->id,
            'title' => 'Unauthorized Maintenance',
        ])->responses()->first()->isError());
    }
}
