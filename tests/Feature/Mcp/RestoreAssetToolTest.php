<?php

use App\Mcp\Tools\RestoreAssetTool;
use App\Models\Asset;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->superuser()->create());
});

function restoreAssetTool(array $args): ResponseFactory
{
    return (new RestoreAssetTool)->handle(new Request($args));
}

test('restores soft deleted asset', function () {
    $asset = Asset::factory()->create();
    $asset->delete();

    $this->assertSoftDeleted('assets', ['id' => $asset->id]);

    $content = restoreAssetTool(['id' => $asset->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertNotSoftDeleted('assets', ['id' => $asset->id]);
});

test('returns error when not found', function () {
    expect(restoreAssetTool(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when asset not deleted', function () {
    $asset = Asset::factory()->create();

    expect(restoreAssetTool(['id' => $asset->id])->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseHas('assets', ['id' => $asset->id, 'deleted_at' => null]);
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    $asset = Asset::factory()->create();
    $asset->delete();

    expect(restoreAssetTool(['id' => $asset->id])->responses()->first()->isError())->toBeTrue();
    $this->assertSoftDeleted('assets', ['id' => $asset->id]);
});
