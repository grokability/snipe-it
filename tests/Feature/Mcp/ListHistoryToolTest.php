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

test('returns history for asset', function () {
    $asset = Asset::factory()->create();

    Actionlog::factory()->create([
        'item_type' => Asset::class,
        'item_id' => $asset->id,
        'action_type' => 'update',
    ]);

    $content = handle(['object_type' => 'asset', 'id' => $asset->id])->getStructuredContent();

    expect($content['total'])->toBeGreaterThanOrEqual(1);
    expect($content['object_type'])->toEqual('asset');
    expect($content['object_id'])->toEqual($asset->id);
});

test('returns empty history for new asset', function () {
    $asset = Asset::factory()->create();

    $content = handle(['object_type' => 'asset', 'id' => $asset->id])->getStructuredContent();

    expect($content['object_type'])->toEqual('asset');
    expect($content['history'])->toBeArray();
});

test('filters by action type', function () {
    $asset = Asset::factory()->create();

    Actionlog::factory()->create([
        'item_type' => Asset::class,
        'item_id' => $asset->id,
        'action_type' => 'update',
    ]);

    Actionlog::factory()->create([
        'item_type' => Asset::class,
        'item_id' => $asset->id,
        'action_type' => 'note added',
        'note' => 'A note',
    ]);

    $content = handle([
        'object_type' => 'asset',
        'id' => $asset->id,
        'action_type' => 'note added',
    ])->getStructuredContent();

    foreach ($content['history'] as $entry) {
        expect($entry['action_type'])->toEqual('note added');
    }
});

test('returns error when object not found', function () {
    expect(handle(['object_type' => 'asset', 'id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks history permission', function () {
    $this->actingAs(User::factory()->create());
    $asset = Asset::factory()->create();

    expect(handle(['object_type' => 'asset', 'id' => $asset->id])->responses()->first()->isError())->toBeTrue();
});

test('supports user object type', function () {
    $this->actingAs(User::factory()->viewUsers()->create());
    $user = User::factory()->create();

    $content = handle(['object_type' => 'user', 'id' => $user->id])->getStructuredContent();

    expect($content['object_type'])->toEqual('user');
    expect($content['object_id'])->toEqual($user->id);
});
