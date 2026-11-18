<?php

use App\Models\Asset;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->editAssets()->create());
});

function handle(array $args): ResponseFactory
{
    return handle(new Request($args));
}

test('updates name by asset tag', function () {
    $asset = Asset::factory()->create(['name' => 'Old Name']);

    $content = handle([
        'asset_tag' => $asset->asset_tag,
        'name' => 'New Name',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('assets', ['id' => $asset->id, 'name' => 'New Name']);
});

test('updates asset by numeric id', function () {
    $asset = Asset::factory()->create(['name' => 'Old Name']);

    $content = handle([
        'id' => $asset->id,
        'name' => 'Updated by ID',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('assets', ['id' => $asset->id, 'name' => 'Updated by ID']);
});

test('updates asset by serial', function () {
    $asset = Asset::factory()->create(['serial' => 'SN-UPDATE-001', 'name' => 'Old Name']);

    $content = handle([
        'serial' => 'SN-UPDATE-001',
        'name' => 'Updated by Serial',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('assets', ['id' => $asset->id, 'name' => 'Updated by Serial']);
});

test('updates multiple fields at once', function () {
    $asset = Asset::factory()->create();
    $location = Location::factory()->create();
    $status = Statuslabel::factory()->rtd()->create();

    handle([
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
});

test('renames asset tag via new asset tag', function () {
    $asset = Asset::factory()->create(['asset_tag' => 'TAG-OLD-001']);

    $content = handle([
        'asset_tag' => 'TAG-OLD-001',
        'new_asset_tag' => 'TAG-NEW-001',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($content['asset_tag'])->toEqual('TAG-NEW-001');
    $this->assertDatabaseHas('assets', ['id' => $asset->id, 'asset_tag' => 'TAG-NEW-001']);
});

test('updates serial via new serial', function () {
    $asset = Asset::factory()->create(['serial' => 'SN-OLD-001']);

    handle([
        'asset_tag' => $asset->asset_tag,
        'new_serial' => 'SN-NEW-001',
    ]);

    $this->assertDatabaseHas('assets', ['id' => $asset->id, 'serial' => 'SN-NEW-001']);
});

test('returns error when asset not found', function () {
    expect(handle([
        'asset_tag' => 'DOES-NOT-EXIST',
        'name' => 'Anything',
    ])->responses()->first()->isError())->toBeTrue();
});

test('response includes asset tag and id', function () {
    $asset = Asset::factory()->create();

    $content = handle([
        'asset_tag' => $asset->asset_tag,
        'name' => 'Response Check',
    ])->getStructuredContent();

    expect($content['asset_tag'])->toEqual($asset->asset_tag);
    expect($content['id'])->toEqual($asset->id);
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $asset = Asset::factory()->create();

    expect(handle([
        'asset_tag' => $asset->asset_tag,
        'name' => 'Should Not Update',
    ])->responses()->first()->isError())->toBeTrue();
});
