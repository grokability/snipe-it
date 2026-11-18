<?php

use App\Mcp\Tools\ListUploadsTool;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->superuser()->create());
});

function listUploadsTool(array $args): ResponseFactory
{
    return (new ListUploadsTool)->handle(new Request($args));
}

test('returns uploads for asset', function () {
    $asset = Asset::factory()->create();

    Actionlog::factory()->create([
        'item_type' => Asset::class,
        'item_id' => $asset->id,
        'action_type' => 'uploaded',
        'filename' => 'test-document.pdf',
    ]);

    $content = listUploadsTool(['object_type' => 'assets', 'id' => $asset->id])->getStructuredContent();

    expect($content['total'])->toEqual(1);
    expect($content['files'][0]['filename'])->toEqual('test-document.pdf');
});

test('returns empty list when no uploads', function () {
    $asset = Asset::factory()->create();

    $content = listUploadsTool(['object_type' => 'assets', 'id' => $asset->id])->getStructuredContent();

    expect($content['total'])->toEqual(0);
    expect($content['files'])->toBeEmpty();
});

test('returns error when object not found', function () {
    expect(listUploadsTool(['object_type' => 'assets', 'id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks files permission', function () {
    $this->actingAs(User::factory()->create());
    $asset = Asset::factory()->create();

    expect(listUploadsTool(['object_type' => 'assets', 'id' => $asset->id])->responses()->first()->isError())->toBeTrue();
});

test('supports user object type', function () {
    $user = User::factory()->create();

    $content = listUploadsTool(['object_type' => 'users', 'id' => $user->id])->getStructuredContent();

    expect($content['object_type'])->toEqual('users');
    expect($content['object_id'])->toEqual($user->id);
});
