<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CheckoutComponentTool;
use App\Models\Asset;
use App\Models\Component;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CheckoutComponentToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->checkoutComponents()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new CheckoutComponentTool)->handle(new Request($args));
    }

    public function test_checks_out_component_to_asset_by_id()
    {
        $component = Component::factory()->create(['qty' => 10]);
        $asset = Asset::factory()->create();

        $content = $this->handle([
            'id' => $component->id,
            'asset_id' => $asset->id,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('components_assets', [
            'component_id' => $component->id,
            'asset_id' => $asset->id,
            'assigned_qty' => 1,
        ]);
    }

    public function test_checks_out_component_to_asset_by_name()
    {
        $component = Component::factory()->create(['name' => 'Named Checkout Component', 'qty' => 10]);
        $asset = Asset::factory()->create();

        $content = $this->handle([
            'name' => 'Named Checkout Component',
            'asset_id' => $asset->id,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
    }

    public function test_checks_out_specified_quantity()
    {
        $component = Component::factory()->create(['qty' => 10]);
        $asset = Asset::factory()->create();

        $this->handle([
            'id' => $component->id,
            'asset_id' => $asset->id,
            'assigned_qty' => 3,
        ]);

        $this->assertDatabaseHas('components_assets', [
            'component_id' => $component->id,
            'asset_id' => $asset->id,
            'assigned_qty' => 3,
        ]);
    }

    public function test_response_includes_component_asset_id_for_checkin()
    {
        $component = Component::factory()->create(['qty' => 10]);
        $asset = Asset::factory()->create();

        $content = $this->handle([
            'id' => $component->id,
            'asset_id' => $asset->id,
        ])->getStructuredContent();

        $this->assertArrayHasKey('component_asset_id', $content);
        $this->assertIsInt($content['component_asset_id']);
        $this->assertEquals(1, $content['assigned_qty']);
    }

    public function test_response_includes_asset_tag()
    {
        $component = Component::factory()->create(['qty' => 10]);
        $asset = Asset::factory()->create();

        $content = $this->handle([
            'id' => $component->id,
            'asset_id' => $asset->id,
        ])->getStructuredContent();

        $this->assertEquals($asset->asset_tag, $content['asset_tag']);
        $this->assertEquals($asset->id, $content['asset_id']);
    }

    public function test_returns_error_when_not_enough_units_remaining()
    {
        $component = Component::factory()->create(['qty' => 2]);
        $asset = Asset::factory()->create();

        $response = $this->handle([
            'id' => $component->id,
            'asset_id' => $asset->id,
            'assigned_qty' => 5,
        ]);

        $this->assertTrue($response->responses()->first()->isError());
    }

    public function test_returns_error_when_component_not_found()
    {
        $asset = Asset::factory()->create();

        $this->assertTrue($this->handle([
            'id' => 999999,
            'asset_id' => $asset->id,
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_asset_not_found()
    {
        $component = Component::factory()->create(['qty' => 5]);

        $this->assertTrue($this->handle([
            'id' => $component->id,
            'asset_id' => 999999,
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $component = Component::factory()->create(['qty' => 5]);
        $asset = Asset::factory()->create();

        $this->assertTrue($this->handle([
            'id' => $component->id,
            'asset_id' => $asset->id,
        ])->responses()->first()->isError());
    }
}
