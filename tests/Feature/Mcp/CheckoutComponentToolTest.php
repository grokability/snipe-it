<?php

use App\Models\Asset;
use App\Models\Component;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->checkoutComponents()->create());
});

function handle(array $args): ResponseFactory
{
    return handle(new Request($args));
}

test('checks out component to asset by id', function () {
    $component = Component::factory()->create(['qty' => 10]);
    $asset = Asset::factory()->create();

    $content = handle([
        'id' => $component->id,
        'asset_id' => $asset->id,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('components_assets', [
        'component_id' => $component->id,
        'asset_id' => $asset->id,
        'assigned_qty' => 1,
    ]);
});

test('checks out component to asset by name', function () {
    $component = Component::factory()->create(['name' => 'Named Checkout Component', 'qty' => 10]);
    $asset = Asset::factory()->create();

    $content = handle([
        'name' => 'Named Checkout Component',
        'asset_id' => $asset->id,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
});

test('checks out specified quantity', function () {
    $component = Component::factory()->create(['qty' => 10]);
    $asset = Asset::factory()->create();

    handle([
        'id' => $component->id,
        'asset_id' => $asset->id,
        'assigned_qty' => 3,
    ]);

    $this->assertDatabaseHas('components_assets', [
        'component_id' => $component->id,
        'asset_id' => $asset->id,
        'assigned_qty' => 3,
    ]);
});

test('response includes component asset id for checkin', function () {
    $component = Component::factory()->create(['qty' => 10]);
    $asset = Asset::factory()->create();

    $content = handle([
        'id' => $component->id,
        'asset_id' => $asset->id,
    ])->getStructuredContent();

    expect($content)->toHaveKey('component_asset_id');
    expect($content['component_asset_id'])->toBeInt();
    expect($content['assigned_qty'])->toEqual(1);
});

test('response includes asset tag', function () {
    $component = Component::factory()->create(['qty' => 10]);
    $asset = Asset::factory()->create();

    $content = handle([
        'id' => $component->id,
        'asset_id' => $asset->id,
    ])->getStructuredContent();

    expect($content['asset_tag'])->toEqual($asset->asset_tag);
    expect($content['asset_id'])->toEqual($asset->id);
});

test('returns error when not enough units remaining', function () {
    $component = Component::factory()->create(['qty' => 2]);
    $asset = Asset::factory()->create();

    $response = handle([
        'id' => $component->id,
        'asset_id' => $asset->id,
        'assigned_qty' => 5,
    ]);

    expect($response->responses()->first()->isError())->toBeTrue();
});

test('returns error when component not found', function () {
    $asset = Asset::factory()->create();

    expect(handle([
        'id' => 999999,
        'asset_id' => $asset->id,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when asset not found', function () {
    $component = Component::factory()->create(['qty' => 5]);

    expect(handle([
        'id' => $component->id,
        'asset_id' => 999999,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $component = Component::factory()->create(['qty' => 5]);
    $asset = Asset::factory()->create();

    expect(handle([
        'id' => $component->id,
        'asset_id' => $asset->id,
    ])->responses()->first()->isError())->toBeTrue();
});

test('refuses to check out archived component', function () {
    $component = Component::factory()->archived()->create(['qty' => 5]);
    $asset = Asset::factory()->create();

    $response = handle([
        'id' => $component->id,
        'asset_id' => $asset->id,
    ]);

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseMissing('components_assets', [
        'component_id' => $component->id,
        'asset_id' => $asset->id,
    ]);
});
