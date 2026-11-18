<?php

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewAssets()->create());
});

function handle(array $args): ResponseFactory
{
    return handle(new Request($args));
}

test('returns notes for asset', function () {
    $asset = Asset::factory()->create();
    $author = User::factory()->create();

    Actionlog::factory()->create([
        'item_type' => Asset::class,
        'item_id' => $asset->id,
        'action_type' => 'note added',
        'note' => 'Test note content',
        'created_by' => $author->id,
    ]);

    $content = handle(['id' => $asset->id])->getStructuredContent();

    expect($content['total'])->toEqual(1);
    expect($content['notes'][0]['note'])->toEqual('Test note content');
});

test('returns empty list when no notes', function () {
    $asset = Asset::factory()->create();

    $content = handle(['id' => $asset->id])->getStructuredContent();

    expect($content['total'])->toEqual(0);
    expect($content['notes'])->toBeEmpty();
});

test('resolves asset by asset tag', function () {
    $asset = Asset::factory()->create();

    $content = handle(['asset_tag' => (string) $asset->asset_tag])->getStructuredContent();

    expect($content['asset_id'])->toEqual($asset->id);
});

test('resolves asset by serial', function () {
    $asset = Asset::factory()->create();

    $content = handle(['serial' => $asset->serial])->getStructuredContent();

    expect($content['asset_id'])->toEqual($asset->id);
});

test('returns error when asset not found', function () {
    expect(handle(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks view permission', function () {
    $this->actingAs(User::factory()->create());
    $asset = Asset::factory()->create();

    expect(handle(['id' => $asset->id])->responses()->first()->isError())->toBeTrue();
});
