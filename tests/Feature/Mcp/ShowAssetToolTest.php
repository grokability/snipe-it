<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ShowAssetTool;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ShowAssetToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewAssets()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new ShowAssetTool)->handle(new Request($args));
    }

    public function test_returns_error_when_asset_tag_not_found()
    {
        $response = $this->handle(['asset_tag' => 'DOES-NOT-EXIST']);

        $this->assertTrue($response->responses()->first()->isError());
    }

    public function test_returns_error_when_id_not_found()
    {
        $response = $this->handle(['id' => 99999]);

        $this->assertTrue($response->responses()->first()->isError());
    }

    public function test_finds_asset_by_asset_tag()
    {
        $asset = Asset::factory()->create(['asset_tag' => 'TAG-FIND-001']);

        $content = $this->handle(['asset_tag' => 'TAG-FIND-001'])->getStructuredContent();

        $this->assertEquals('TAG-FIND-001', $content['asset_tag']);
        $this->assertEquals($asset->id, $content['id']);
    }

    public function test_finds_asset_by_serial()
    {
        $asset = Asset::factory()->create(['serial' => 'SN-FIND-001']);

        $content = $this->handle(['serial' => 'SN-FIND-001'])->getStructuredContent();

        $this->assertEquals('SN-FIND-001', $content['serial']);
        $this->assertEquals($asset->id, $content['id']);
    }

    public function test_returns_error_when_serial_not_found()
    {
        $response = $this->handle(['serial' => 'DOES-NOT-EXIST']);

        $this->assertTrue($response->responses()->first()->isError());
    }

    public function test_finds_asset_by_numeric_id()
    {
        $asset = Asset::factory()->create();

        $content = $this->handle(['id' => $asset->id])->getStructuredContent();

        $this->assertEquals($asset->asset_tag, $content['asset_tag']);
        $this->assertEquals($asset->id, $content['id']);
    }

    public function test_returns_model_status_and_manufacturer_details()
    {
        $asset = Asset::factory()->create();

        $content = $this->handle(['asset_tag' => $asset->asset_tag])->getStructuredContent();

        $this->assertArrayHasKey('model', $content);
        $this->assertArrayHasKey('model_number', $content);
        $this->assertArrayHasKey('category', $content);
        $this->assertArrayHasKey('manufacturer', $content);
        $this->assertArrayHasKey('status', $content);
        $this->assertArrayHasKey('status_type', $content);
    }

    public function test_returns_date_and_cost_fields()
    {
        $asset = Asset::factory()->create();

        $content = $this->handle(['asset_tag' => $asset->asset_tag])->getStructuredContent();

        $this->assertArrayHasKey('purchase_date', $content);
        $this->assertArrayHasKey('purchase_cost', $content);
        $this->assertArrayHasKey('warranty_months', $content);
        $this->assertArrayHasKey('created_at', $content);
        $this->assertArrayHasKey('updated_at', $content);
    }

    public function test_shows_assignment_info_when_checked_out_to_user()
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create();

        $content = $this->handle(['asset_tag' => $asset->asset_tag])->getStructuredContent();

        $this->assertEquals($user->id, $content['assigned_to_id']);
        $this->assertEquals('User', $content['assigned_to_type']);
    }

    public function test_shows_assignment_info_when_checked_out_to_location()
    {
        $location = Location::factory()->create();
        $asset = Asset::factory()->assignedToLocation($location)->create();

        $content = $this->handle(['asset_tag' => $asset->asset_tag])->getStructuredContent();

        $this->assertEquals($location->id, $content['assigned_to_id']);
        $this->assertEquals('Location', $content['assigned_to_type']);
    }

    public function test_assignment_info_is_null_when_not_checked_out()
    {
        $asset = Asset::factory()->create();

        $content = $this->handle(['asset_tag' => $asset->asset_tag])->getStructuredContent();

        $this->assertNull($content['assigned_to_id']);
        $this->assertNull($content['assigned_to_type']);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $asset = Asset::factory()->create();

        $this->assertTrue($this->handle(['asset_tag' => $asset->asset_tag])->responses()->first()->isError());
    }
}
