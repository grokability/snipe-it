<?php

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

beforeEach(function () {
    $this->actingAs(User::factory()->checkoutComponents()->checkinComponents()->create());
});

function checkinComponentTool(array $args): ResponseFactory
{
    return (new CheckinComponentTool)->handle(new Request($args));
}

function checkoutToAsset(Component $component, Asset $asset, int $qty = 1): int
{
    $response = (new CheckoutComponentTool)->handle(new Request([
        'id' => $component->id,
        'asset_id' => $asset->id,
        'assigned_qty' => $qty,
    ]));

    return $response->getStructuredContent()['component_asset_id'];
}

test('checks in full quantity', function () {
    $component = Component::factory()->create(['qty' => 5]);
    $asset = Asset::factory()->create();
    $componentAssetId = checkoutToAsset($component, $asset);

    $content = checkinComponentTool(['component_asset_id' => $componentAssetId])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($content['qty_still_checked_out'])->toEqual(0);
    $this->assertDatabaseMissing('components_assets', ['id' => $componentAssetId]);
});

test('partial checkin leaves remainder in record', function () {
    $component = Component::factory()->create(['qty' => 10]);
    $asset = Asset::factory()->create();
    $componentAssetId = checkoutToAsset($component, $asset, 4);

    $content = checkinComponentTool([
        'component_asset_id' => $componentAssetId,
        'checkin_qty' => 2,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($content['checkin_qty'])->toEqual(2);
    expect($content['qty_still_checked_out'])->toEqual(2);
    $this->assertDatabaseHas('components_assets', ['id' => $componentAssetId, 'assigned_qty' => 2]);
});

test('full checkin deletes pivot record', function () {
    $component = Component::factory()->create(['qty' => 5]);
    $asset = Asset::factory()->create();
    $componentAssetId = checkoutToAsset($component, $asset, 3);

    checkinComponentTool([
        'component_asset_id' => $componentAssetId,
        'checkin_qty' => 3,
    ]);

    $this->assertDatabaseMissing('components_assets', ['id' => $componentAssetId]);
});

test('response includes component name and counts', function () {
    $component = Component::factory()->create(['name' => 'Checkin Test Component', 'qty' => 5]);
    $asset = Asset::factory()->create();
    $componentAssetId = checkoutToAsset($component, $asset);

    $content = checkinComponentTool(['component_asset_id' => $componentAssetId])->getStructuredContent();

    expect($content['component_name'])->toEqual('Checkin Test Component');
    expect($content['component_id'])->toEqual($component->id);
});

test('fires checkin event', function () {
    Event::fake([CheckoutableCheckedIn::class]);

    $component = Component::factory()->create(['qty' => 5]);
    $asset = Asset::factory()->create();
    $componentAssetId = checkoutToAsset($component, $asset);

    checkinComponentTool(['component_asset_id' => $componentAssetId]);

    Event::assertDispatched(CheckoutableCheckedIn::class, function (CheckoutableCheckedIn $event) use ($component) {
        return $event->checkoutable->id === $component->id;
    });
});

test('returns error when checkout record not found', function () {
    expect(checkinComponentTool(['component_asset_id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when checkin qty exceeds assigned qty', function () {
    $component = Component::factory()->create(['qty' => 5]);
    $asset = Asset::factory()->create();
    $componentAssetId = checkoutToAsset($component, $asset, 2);

    $response = checkinComponentTool([
        'component_asset_id' => $componentAssetId,
        'checkin_qty' => 5,
    ]);

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseHas('components_assets', ['id' => $componentAssetId, 'assigned_qty' => 2]);
});

test('factory checkout record can be checked in', function () {
    $asset = Asset::factory()->create();
    $component = Component::factory()->checkedOutToAsset($asset)->create();

    $record = DB::table('components_assets')->where('component_id', $component->id)->first();

    $content = checkinComponentTool(['component_asset_id' => $record->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseMissing('components_assets', ['id' => $record->id]);
});

test('returns error when user lacks permission', function () {
    $component = Component::factory()->create(['qty' => 5]);
    $asset = Asset::factory()->create();
    $componentAssetId = checkoutToAsset($component, $asset);

    $this->actingAs(User::factory()->create());

    expect(checkinComponentTool(['component_asset_id' => $componentAssetId])->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseHas('components_assets', ['id' => $componentAssetId]);
});
