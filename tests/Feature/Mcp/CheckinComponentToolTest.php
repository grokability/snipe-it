<?php

namespace Tests\Feature\Mcp;

use App\Events\CheckoutableCheckedIn;
use App\Mcp\Tools\CheckinComponentTool;
use App\Mcp\Tools\CheckoutComponentTool;
use App\Models\Asset;
use App\Models\Component;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CheckinComponentToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->checkoutComponents()->checkinComponents()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new CheckinComponentTool)->handle(new Request($args));
    }

    private function checkoutToAsset(Component $component, Asset $asset, int $qty = 1): int
    {
        $response = (new CheckoutComponentTool)->handle(new Request([
            'id' => $component->id,
            'asset_id' => $asset->id,
            'assigned_qty' => $qty,
        ]));

        return $response->getStructuredContent()['component_asset_id'];
    }

    public function test_checks_in_full_quantity()
    {
        $component = Component::factory()->create(['qty' => 5]);
        $asset = Asset::factory()->create();
        $componentAssetId = $this->checkoutToAsset($component, $asset);

        $content = $this->handle(['component_asset_id' => $componentAssetId])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertEquals(0, $content['qty_still_checked_out']);
        $this->assertDatabaseMissing('components_assets', ['id' => $componentAssetId]);
    }

    public function test_partial_checkin_leaves_remainder_in_record()
    {
        $component = Component::factory()->create(['qty' => 10]);
        $asset = Asset::factory()->create();
        $componentAssetId = $this->checkoutToAsset($component, $asset, 4);

        $content = $this->handle([
            'component_asset_id' => $componentAssetId,
            'checkin_qty' => 2,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertEquals(2, $content['checkin_qty']);
        $this->assertEquals(2, $content['qty_still_checked_out']);
        $this->assertDatabaseHas('components_assets', ['id' => $componentAssetId, 'assigned_qty' => 2]);
    }

    public function test_full_checkin_deletes_pivot_record()
    {
        $component = Component::factory()->create(['qty' => 5]);
        $asset = Asset::factory()->create();
        $componentAssetId = $this->checkoutToAsset($component, $asset, 3);

        $this->handle([
            'component_asset_id' => $componentAssetId,
            'checkin_qty' => 3,
        ]);

        $this->assertDatabaseMissing('components_assets', ['id' => $componentAssetId]);
    }

    public function test_response_includes_component_name_and_counts()
    {
        $component = Component::factory()->create(['name' => 'Checkin Test Component', 'qty' => 5]);
        $asset = Asset::factory()->create();
        $componentAssetId = $this->checkoutToAsset($component, $asset);

        $content = $this->handle(['component_asset_id' => $componentAssetId])->getStructuredContent();

        $this->assertEquals('Checkin Test Component', $content['component_name']);
        $this->assertEquals($component->id, $content['component_id']);
    }

    public function test_fires_checkin_event()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $component = Component::factory()->create(['qty' => 5]);
        $asset = Asset::factory()->create();
        $componentAssetId = $this->checkoutToAsset($component, $asset);

        $this->handle(['component_asset_id' => $componentAssetId]);

        Event::assertDispatched(CheckoutableCheckedIn::class, function (CheckoutableCheckedIn $event) use ($component) {
            return $event->checkoutable->id === $component->id;
        });
    }

    public function test_returns_error_when_checkout_record_not_found()
    {
        $this->assertTrue($this->handle(['component_asset_id' => 999999])->responses()->first()->isError());
    }

    public function test_returns_error_when_checkin_qty_exceeds_assigned_qty()
    {
        $component = Component::factory()->create(['qty' => 5]);
        $asset = Asset::factory()->create();
        $componentAssetId = $this->checkoutToAsset($component, $asset, 2);

        $response = $this->handle([
            'component_asset_id' => $componentAssetId,
            'checkin_qty' => 5,
        ]);

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertDatabaseHas('components_assets', ['id' => $componentAssetId, 'assigned_qty' => 2]);
    }

    public function test_factory_checkout_record_can_be_checked_in()
    {
        $asset = Asset::factory()->create();
        $component = Component::factory()->checkedOutToAsset($asset)->create();

        $record = DB::table('components_assets')->where('component_id', $component->id)->first();

        $content = $this->handle(['component_asset_id' => $record->id])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseMissing('components_assets', ['id' => $record->id]);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $component = Component::factory()->create(['qty' => 5]);
        $asset = Asset::factory()->create();
        $componentAssetId = $this->checkoutToAsset($component, $asset);

        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle(['component_asset_id' => $componentAssetId])->responses()->first()->isError());
        $this->assertDatabaseHas('components_assets', ['id' => $componentAssetId]);
    }
}
