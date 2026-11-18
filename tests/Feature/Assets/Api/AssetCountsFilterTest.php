<?php

use App\Models\Asset;
use App\Models\Component;
use App\Models\LicenseSeat;
use App\Models\Maintenance;
use App\Models\User;

test('index response includes the four count fields', function () {
    $asset = Asset::factory()->create();

    attachLicenseSeats($asset, 2);
    attachComponents($asset, 3);
    attachMaintenances($asset, 4);
    attachChildAssets($asset, 5);

    $row = indexRowFor($asset);

    expect($row['licenses_count'])->toBe(2);
    expect($row['components_count'])->toBe(3);
    expect($row['maintenances_count'])->toBe(4);
    expect($row['assigned_assets_count'])->toBe(5);
});

test('index sort by assigned assets count descending', function () {
    [$few, $many] = Asset::factory()->count(2)->create();
    attachChildAssets($few, 1);
    attachChildAssets($many, 4);

    $ids = indexSortedIds('assigned_assets_count', 'desc');

    expect($ids[0])->toBe($many->id);
    expect($ids[1])->toBe($few->id);
});

test('assigned assets count filter gt narrows the result set', function () {
    [$zero, $one, $three] = Asset::factory()->count(3)->create();
    attachChildAssets($one, 1);
    attachChildAssets($three, 3);

    $ids = filteredIds(['assigned_assets_count' => 'gt:1']);

    expect($ids)->toContain($three->id);
    expect($ids)->not->toContain($one->id);
    expect($ids)->not->toContain($zero->id);
});

test('assigned assets count excludes trashed children', function () {
    /*
     * A child asset whose soft-delete column is set should NOT
     * count toward the parent's assigned_assets_count. The
     * withCount closure and the filter subquery both filter
     * trashed children.
     */
    $parent = Asset::factory()->create();
    attachChildAssets($parent, 2);
    $trashedChild = Asset::factory()->create([
        'assigned_to' => $parent->id,
        'assigned_type' => Asset::class,
    ]);
    $trashedChild->delete();

    $row = indexRowFor($parent);

    expect($row['assigned_assets_count'])->toBe(2);
});

test('index sort by licenses count descending', function () {
    [$few, $many] = Asset::factory()->count(2)->create();
    attachLicenseSeats($few, 1);
    attachLicenseSeats($many, 3);

    $ids = indexSortedIds('licenses_count', 'desc');

    expect($ids[0])->toBe($many->id);
    expect($ids[1])->toBe($few->id);
});

test('index sort by components count descending', function () {
    [$few, $many] = Asset::factory()->count(2)->create();
    attachComponents($few, 1);
    attachComponents($many, 4);

    $ids = indexSortedIds('components_count', 'desc');

    expect($ids[0])->toBe($many->id);
    expect($ids[1])->toBe($few->id);
});

test('index sort by maintenances count descending', function () {
    [$few, $many] = Asset::factory()->count(2)->create();
    attachMaintenances($few, 1);
    attachMaintenances($many, 5);

    $ids = indexSortedIds('maintenances_count', 'desc');

    expect($ids[0])->toBe($many->id);
    expect($ids[1])->toBe($few->id);
});

test('licenses count filter gt narrows the result set', function () {
    [$zero, $one, $three] = Asset::factory()->count(3)->create();
    attachLicenseSeats($one, 1);
    attachLicenseSeats($three, 3);

    $ids = filteredIds(['licenses_count' => 'gt:1']);

    expect($ids)->toContain($three->id);
    expect($ids)->not->toContain($one->id);
    expect($ids)->not->toContain($zero->id);
});

test('components count filter lt narrows the result set', function () {
    [$zero, $one, $three] = Asset::factory()->count(3)->create();
    attachComponents($one, 1);
    attachComponents($three, 3);

    $ids = filteredIds(['components_count' => 'lt:2']);

    expect($ids)->toContain($zero->id);
    expect($ids)->toContain($one->id);
    expect($ids)->not->toContain($three->id);
});

test('maintenances count filter gt and lt bounds the result set', function () {
    $zero = Asset::factory()->create();
    $two = Asset::factory()->create();
    $five = Asset::factory()->create();
    attachMaintenances($two, 2);
    attachMaintenances($five, 5);

    $ids = filteredIds(['maintenances_count' => 'gt:1,lt:4']);

    expect($ids)->toContain($two->id);
    expect($ids)->not->toContain($zero->id);
    expect($ids)->not->toContain($five->id);
});

test('maintenances count respects morph type predicate', function () {
    /*
     * A Maintenance row whose morph target is NOT an Asset
     * (item_type on a Component, say) must not inflate any
     * asset's maintenances_count. Guard against someone later
     * dropping the item_type predicate from the subquery and
     * silently catching unrelated rows.
     */
    $asset = Asset::factory()->create();
    $foreignOwner = Component::factory()->create();

    attachMaintenances($asset, 1);
    Maintenance::factory()->create([
        'item_id' => $foreignOwner->id,
        'item_type' => Component::class,
    ]);

    $row = indexRowFor($asset);

    expect($row['maintenances_count'])->toBe(1);
});

function indexRowFor(Asset $asset): array
{
    $rows = test()->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.assets.index', ['limit' => 100]))
        ->assertOk()
        ->json('rows');

    foreach ($rows as $row) {
        if ((int) $row['id'] === $asset->id) {
            return $row;
        }
    }

    test()->fail('Asset '.$asset->id.' did not appear in the API index response');
}

/** @return array<int, int> */
function indexSortedIds(string $sort, string $order): array
{
    return collect(
        test()->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.assets.index', [
                'sort' => $sort,
                'order' => $order,
                'limit' => 100,
            ]))
            ->assertOk()
            ->json('rows'),
    )->pluck('id')->map(fn ($id) => (int) $id)->all();
}

/**
 * @param  array<string, string>  $filter
 * @return array<int, int>
 */
function filteredIds(array $filter): array
{
    return collect(
        test()->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.assets.index', [
                'filter' => $filter,
                'limit' => 100,
            ]))
            ->assertOk()
            ->json('rows'),
    )->pluck('id')->map(fn ($id) => (int) $id)->all();
}

function attachLicenseSeats(Asset $asset, int $count): void
{
    LicenseSeat::factory()->count($count)->assignedToAsset($asset)->create();
}

function attachComponents(Asset $asset, int $count): void
{
    $creator = User::factory()->create();
    for ($i = 0; $i < $count; $i++) {
        $component = Component::factory()->create();
        $asset->components()->attach($component->id, [
            'assigned_qty' => 1,
            'created_by' => $creator->id,
            'created_at' => now(),
        ]);
    }
}

function attachMaintenances(Asset $asset, int $count): void
{
    Maintenance::factory()->count($count)->create([
        'item_id' => $asset->id,
        'item_type' => Asset::class,
    ]);
}

function attachChildAssets(Asset $parent, int $count): void
{
    Asset::factory()->count($count)->create([
        'assigned_to' => $parent->id,
        'assigned_type' => Asset::class,
    ]);
}
