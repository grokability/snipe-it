<?php

use App\Mcp\Tools\AddAssetNoteTool;
use App\Models\Asset;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->editAssets()->create());
});

function addAssetNoteTool(array $args): ResponseFactory
{
    return (new AddAssetNoteTool)->handle(new Request($args));
}

test('adds note by asset tag', function () {
    $asset = Asset::factory()->create();

    $content = addAssetNoteTool([
        'asset_tag' => (string) $asset->asset_tag,
        'note' => 'This is a test note.',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($content['note'])->toEqual('This is a test note.');
    $this->assertDatabaseHas('action_logs', [
        'item_id' => $asset->id,
        'action_type' => 'note added',
        'note' => 'This is a test note.',
    ]);
});

test('adds note by numeric id', function () {
    $asset = Asset::factory()->create();

    $content = addAssetNoteTool([
        'id' => $asset->id,
        'note' => 'Note via ID.',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('action_logs', [
        'item_id' => $asset->id,
        'action_type' => 'note added',
        'note' => 'Note via ID.',
    ]);
});

test('adds note by serial', function () {
    $asset = Asset::factory()->create();

    $content = addAssetNoteTool([
        'serial' => $asset->serial,
        'note' => 'Note via serial.',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('action_logs', [
        'item_id' => $asset->id,
        'action_type' => 'note added',
    ]);
});

test('returns error when asset not found', function () {
    expect(addAssetNoteTool(['id' => 999999, 'note' => 'test'])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $asset = Asset::factory()->create();

    expect(addAssetNoteTool(['id' => $asset->id, 'note' => 'test'])->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseMissing('action_logs', ['item_id' => $asset->id, 'action_type' => 'note added']);
});
