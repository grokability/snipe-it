<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\UpdateAssetTool;
use App\Models\Asset;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class UpdateAssetToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new UpdateAssetTool)->handle(new Request($args));
    }

    public function test_updates_name_by_asset_tag()
    {
        $asset = Asset::factory()->create(['name' => 'Old Name']);

        $content = $this->handle([
            'asset_tag' => $asset->asset_tag,
            'name' => 'New Name',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'name' => 'New Name']);
    }

    public function test_updates_asset_by_numeric_id()
    {
        $asset = Asset::factory()->create(['name' => 'Old Name']);

        $content = $this->handle([
            'id' => $asset->id,
            'name' => 'Updated by ID',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'name' => 'Updated by ID']);
    }

    public function test_updates_asset_by_serial()
    {
        $asset = Asset::factory()->create(['serial' => 'SN-UPDATE-001', 'name' => 'Old Name']);

        $content = $this->handle([
            'serial' => 'SN-UPDATE-001',
            'name' => 'Updated by Serial',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'name' => 'Updated by Serial']);
    }

    public function test_updates_multiple_fields_at_once()
    {
        $asset = Asset::factory()->create();
        $location = Location::factory()->create();
        $status = Statuslabel::factory()->rtd()->create();

        $this->handle([
            'asset_tag' => $asset->asset_tag,
            'notes' => 'MCP note',
            'location_id' => $location->id,
            'status_id' => $status->id,
        ]);

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'notes' => 'MCP note',
            'location_id' => $location->id,
            'status_id' => $status->id,
        ]);
    }

    public function test_renames_asset_tag_via_new_asset_tag()
    {
        $asset = Asset::factory()->create(['asset_tag' => 'TAG-OLD-001']);

        $content = $this->handle([
            'asset_tag' => 'TAG-OLD-001',
            'new_asset_tag' => 'TAG-NEW-001',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertEquals('TAG-NEW-001', $content['asset_tag']);
        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'asset_tag' => 'TAG-NEW-001']);
    }

    public function test_updates_serial_via_new_serial()
    {
        $asset = Asset::factory()->create(['serial' => 'SN-OLD-001']);

        $this->handle([
            'asset_tag' => $asset->asset_tag,
            'new_serial' => 'SN-NEW-001',
        ]);

        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'serial' => 'SN-NEW-001']);
    }

    public function test_returns_error_when_asset_not_found()
    {
        $this->assertTrue($this->handle([
            'asset_tag' => 'DOES-NOT-EXIST',
            'name' => 'Anything',
        ])->responses()->first()->isError());
    }

    public function test_response_includes_asset_tag_and_id()
    {
        $asset = Asset::factory()->create();

        $content = $this->handle([
            'asset_tag' => $asset->asset_tag,
            'name' => 'Response Check',
        ])->getStructuredContent();

        $this->assertEquals($asset->asset_tag, $content['asset_tag']);
        $this->assertEquals($asset->id, $content['id']);
    }
}
