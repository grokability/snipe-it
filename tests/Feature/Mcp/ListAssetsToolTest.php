<?php

use App\Mcp\Tools\ListAssetsTool;
use App\Models\Asset;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewAssets()->create());
});

function listAssetsTool(array $args = []): ResponseFactory
{
    return (new ListAssetsTool)->handle(new Request($args));
}

test('returns total assets and pagination metadata', function () {
    Asset::factory()->count(3)->create();

    $content = listAssetsTool()->getStructuredContent();

    expect($content)->toHaveKey('total');
    expect($content)->toHaveKey('assets');
    expect($content)->toHaveKey('limit');
    expect($content)->toHaveKey('offset');
    expect($content['total'])->toBeGreaterThanOrEqual(3);
});

test('each asset includes key fields', function () {
    Asset::factory()->create();

    $content = listAssetsTool()->getStructuredContent();

    $asset = $content['assets'][0];
    expect($asset)->toHaveKey('id');
    expect($asset)->toHaveKey('asset_tag');
    expect($asset)->toHaveKey('status');
    expect($asset)->toHaveKey('status_type');
    expect($asset)->toHaveKey('model');
    expect($asset)->toHaveKey('location');
});

test('excludes archived assets by default', function () {
    $rtdAsset = Asset::factory()->create();
    $archivedAsset = Asset::factory()->create([
        'status_id' => Statuslabel::factory()->archived()->create()->id,
    ]);

    $content = listAssetsTool()->getStructuredContent();
    $ids = array_column($content['assets'], 'id');

    expect($ids)->toContain($rtdAsset->id);
    expect($ids)->not->toContain($archivedAsset->id);
});

test('rtd filter excludes deployed assets', function () {
    $rtdAsset = Asset::factory()->create();
    $deployedAsset = Asset::factory()->assignedToUser()->create();

    $content = listAssetsTool(['status_type' => 'RTD'])->getStructuredContent();
    $ids = array_column($content['assets'], 'id');

    expect($ids)->toContain($rtdAsset->id);
    expect($ids)->not->toContain($deployedAsset->id);
});

test('deployed filter excludes rtd assets', function () {
    $user = User::factory()->create();
    $deployedAsset = Asset::factory()->assignedToUser($user)->create();
    $rtdAsset = Asset::factory()->create();

    $content = listAssetsTool(['status_type' => 'Deployed'])->getStructuredContent();
    $ids = array_column($content['assets'], 'id');

    expect($ids)->toContain($deployedAsset->id);
    expect($ids)->not->toContain($rtdAsset->id);
});

test('archived filter returns only archived assets', function () {
    $archivedStatus = Statuslabel::factory()->archived()->create();
    $archivedAsset = Asset::factory()->create(['status_id' => $archivedStatus->id]);
    $rtdAsset = Asset::factory()->create();

    $content = listAssetsTool(['status_type' => 'Archived'])->getStructuredContent();
    $ids = array_column($content['assets'], 'id');

    expect($ids)->toContain($archivedAsset->id);
    expect($ids)->not->toContain($rtdAsset->id);
});

test('limit controls number of results returned', function () {
    Asset::factory()->count(10)->create();

    $content = listAssetsTool(['limit' => 3])->getStructuredContent();

    expect($content['assets'])->toHaveCount(3);
    expect($content['total'])->toBeGreaterThan(3);
});

test('offset skips results for pagination', function () {
    Asset::factory()->count(10)->create();

    $page1 = listAssetsTool(['limit' => 4, 'offset' => 0])->getStructuredContent();
    $page2 = listAssetsTool(['limit' => 4, 'offset' => 4])->getStructuredContent();

    $ids1 = array_column($page1['assets'], 'id');
    $ids2 = array_column($page2['assets'], 'id');

    expect(array_intersect($ids1, $ids2))->toBeEmpty();
});

test('search matches on asset tag', function () {
    $target = Asset::factory()->create(['asset_tag' => 'UNIQ-SEARCH-XYZ']);
    Asset::factory()->create(['asset_tag' => 'COMPLETELY-DIFFERENT']);

    $content = listAssetsTool(['search' => 'UNIQ-SEARCH-XYZ'])->getStructuredContent();
    $ids = array_column($content['assets'], 'id');

    expect($ids)->toContain($target->id);
});

test('filters by location id', function () {
    $location = Location::factory()->create();
    $matchingAsset = Asset::factory()->create(['location_id' => $location->id]);
    $otherAsset = Asset::factory()->create();

    $content = listAssetsTool(['location_id' => $location->id])->getStructuredContent();
    $ids = array_column($content['assets'], 'id');

    expect($ids)->toContain($matchingAsset->id);
    expect($ids)->not->toContain($otherAsset->id);
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(listAssetsTool()->responses()->first()->isError())->toBeTrue();
});

test('filters by rtd location id', function () {
    $rtdLocation = Location::factory()->create();
    $elsewhere = Location::factory()->create();
    $matching = Asset::factory()->create(['rtd_location_id' => $rtdLocation->id, 'location_id' => $elsewhere->id]);
    $other = Asset::factory()->create(['rtd_location_id' => $elsewhere->id]);

    $content = listAssetsTool(['rtd_location_id' => $rtdLocation->id])->getStructuredContent();
    $ids = array_column($content['assets'], 'id');

    expect($ids)->toContain($matching->id);
    expect($ids)->not->toContain($other->id);
});

test('filters by supplier id', function () {
    $supplier = \App\Models\Supplier::factory()->create();
    $matching = Asset::factory()->create(['supplier_id' => $supplier->id]);
    $other = Asset::factory()->create();

    $content = listAssetsTool(['supplier_id' => $supplier->id])->getStructuredContent();
    $ids = array_column($content['assets'], 'id');

    expect($ids)->toContain($matching->id);
    expect($ids)->not->toContain($other->id);
});

test('filter payload forwards to text search', function () {
    $match = Asset::factory()->create(['name' => 'Executive MacBook Pro']);
    Asset::factory()->create(['name' => 'Generic Laptop']);

    $content = listAssetsTool(['filter' => ['name' => 'Executive']])->getStructuredContent();
    $ids = array_column($content['assets'], 'id');

    expect($ids)->toContain($match->id);
});

test('response includes lifecycle fields and child counts', function () {
    $asset = Asset::factory()->create([
        'warranty_months' => 36,
        'purchase_date' => '2026-01-15',
    ]);

    $content = listAssetsTool(['limit' => 500])->getStructuredContent();
    $row = collect($content['assets'])->firstWhere('id', $asset->id);

    expect($row)->not->toBeNull();
    expect($row)->toHaveKey('purchase_date');
    expect($row)->toHaveKey('warranty_months');
    expect($row)->toHaveKey('warranty_expires');
    expect($row)->toHaveKey('asset_eol_date');
    expect($row)->toHaveKey('licenses_count');
    expect($row)->toHaveKey('components_count');
    expect($row)->toHaveKey('maintenances_count');
    expect($row)->toHaveKey('notes');
});
