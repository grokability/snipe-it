<?php

use App\Mcp\Tools\UploadAssetImageTool;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->editAssets()->create());
    Storage::fake('public');
});

function uploadAssetImageTool(array $args): ResponseFactory
{
    return resolve(UploadAssetImageTool::class)->handle(new Request($args));
}

function pngBase64(): string
{
    // 1x1 red PNG
    return 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==';
}

test('attaches image to asset by asset tag', function () {
    $asset = Asset::factory()->create();

    $content = uploadAssetImageTool([
        'asset_tag' => $asset->asset_tag,
        'image_base64' => pngBase64(),
        'mime_type' => 'image/png',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($asset->fresh()->image)->not->toBeEmpty();
});

test('attaches image to asset by numeric id', function () {
    $asset = Asset::factory()->create();

    $content = uploadAssetImageTool([
        'id' => $asset->id,
        'image_base64' => pngBase64(),
        'mime_type' => 'image/png',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
});

test('returns error when no identifier provided', function () {
    expect(uploadAssetImageTool([
        'image_base64' => pngBase64(),
        'mime_type' => 'image/png',
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when asset not found', function () {
    expect(uploadAssetImageTool([
        'asset_tag' => 'DOES-NOT-EXIST',
        'image_base64' => pngBase64(),
        'mime_type' => 'image/png',
    ])->responses()->first()->isError())->toBeTrue();
});

test('rejects disallowed mime type', function () {
    $asset = Asset::factory()->create();

    $this->expectException(\Illuminate\Validation\ValidationException::class);
    uploadAssetImageTool([
        'asset_tag' => $asset->asset_tag,
        'image_base64' => pngBase64(),
        'mime_type' => 'application/octet-stream',
    ]);
});

test('rejects undecodable base64', function () {
    $asset = Asset::factory()->create();

    $response = uploadAssetImageTool([
        'asset_tag' => $asset->asset_tag,
        'image_base64' => '!!!not valid base64!!!',
        'mime_type' => 'image/png',
    ]);

    expect($response->responses()->first()->isError())->toBeTrue();
});

test('returns error when acting user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $asset = Asset::factory()->create();

    expect(uploadAssetImageTool([
        'asset_tag' => $asset->asset_tag,
        'image_base64' => pngBase64(),
        'mime_type' => 'image/png',
    ])->responses()->first()->isError())->toBeTrue();
});

test('response includes image filename and url', function () {
    $asset = Asset::factory()->create();

    $content = uploadAssetImageTool([
        'asset_tag' => $asset->asset_tag,
        'image_base64' => pngBase64(),
        'mime_type' => 'image/png',
    ])->getStructuredContent();

    expect($content)->toHaveKey('image');
    expect($content)->toHaveKey('image_url');
    expect($content['image'])->not->toBeEmpty();
});
