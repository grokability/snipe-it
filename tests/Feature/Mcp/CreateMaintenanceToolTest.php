<?php

use App\Models\Asset;
use App\Models\MaintenanceType;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->editAssets()->create());
});

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('creates maintenance for asset via asset id', function () {
    $asset = Asset::factory()->laptopZenbook()->create();
    $type = MaintenanceType::factory()->create();

    handle([
        'asset_id' => $asset->id,
        'maintenance_type_id' => $type->id,
        'title' => 'Test Maintenance',
        'start_date' => '2024-01-01',
    ]);

    // Writes land on the polymorphic native columns, not the legacy
    // asset_id mutator path.
    $this->assertDatabaseHas('maintenances', [
        'item_id' => $asset->id,
        'item_type' => Asset::class,
        'name' => 'Test Maintenance',
    ]);
});

test('creates maintenance for asset via asset tag', function () {
    $asset = Asset::factory()->laptopZenbook()->create(['asset_tag' => 'TAG-LOOKUP']);
    $type = MaintenanceType::factory()->create();

    handle([
        'asset_tag' => 'TAG-LOOKUP',
        'maintenance_type_id' => $type->id,
        'title' => 'Tag Lookup Maintenance',
    ]);

    $this->assertDatabaseHas('maintenances', [
        'item_id' => $asset->id,
        'item_type' => Asset::class,
        'name' => 'Tag Lookup Maintenance',
    ]);
});

test('completion date input writes to expected completion date', function () {
    $asset = Asset::factory()->laptopZenbook()->create();
    $type = MaintenanceType::factory()->create();

    handle([
        'asset_id' => $asset->id,
        'maintenance_type_id' => $type->id,
        'title' => 'Legacy Alias',
        'completion_date' => '2026-12-31',
    ]);

    $this->assertDatabaseHas('maintenances', [
        'name' => 'Legacy Alias',
        'expected_completion_date' => '2026-12-31',
    ]);
});

test('completed at input marks maintenance done and sets completed by', function () {
    $actor = User::factory()->editAssets()->create();
    $this->actingAs($actor);

    $asset = Asset::factory()->laptopZenbook()->create();
    $type = MaintenanceType::factory()->create();

    handle([
        'asset_id' => $asset->id,
        'maintenance_type_id' => $type->id,
        'title' => 'Already Done',
        // start_date has to be before completed_at per the model's
        // after_or_equal:start_date rule on completed_at.
        'start_date' => '2026-06-01',
        'completed_at' => '2026-06-15 12:00:00',
    ]);

    $this->assertDatabaseHas('maintenances', [
        'name' => 'Already Done',
        'completed_at' => '2026-06-15 12:00:00',
        'completed_by' => $actor->id,
    ]);
});

test('response includes item fields not legacy asset id', function () {
    $asset = Asset::factory()->laptopZenbook()->create();
    $type = MaintenanceType::factory()->create();

    $content = handle([
        'asset_id' => $asset->id,
        'maintenance_type_id' => $type->id,
        'title' => 'Test Maintenance Response',
        'start_date' => '2024-01-01',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($content)->toHaveKey('id');
    expect($content)->toHaveKey('name');
    expect($content['item_type'])->toBe('Asset');
    expect($content['item_id'])->toBe($asset->id);
    expect($content['asset_tag'])->toBe($asset->asset_tag);
});

test('returns error when asset identifier missing', function () {
    $type = MaintenanceType::factory()->create();

    expect(handle([
        'maintenance_type_id' => $type->id,
        'title' => 'Test',
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when title missing', function () {
    $asset = Asset::factory()->laptopZenbook()->create();
    $type = MaintenanceType::factory()->create();

    expect(handle([
        'asset_id' => $asset->id,
        'maintenance_type_id' => $type->id,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when maintenance type missing', function () {
    $asset = Asset::factory()->laptopZenbook()->create();

    expect(handle([
        'asset_id' => $asset->id,
        'title' => 'Test',
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when asset not found', function () {
    $type = MaintenanceType::factory()->create();

    expect(handle([
        'asset_id' => 999999,
        'maintenance_type_id' => $type->id,
        'title' => 'Test',
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $asset = Asset::factory()->laptopZenbook()->create();
    $type = MaintenanceType::factory()->create();

    expect(handle([
        'asset_id' => $asset->id,
        'maintenance_type_id' => $type->id,
        'title' => 'Unauthorized Maintenance',
    ])->responses()->first()->isError())->toBeTrue();
});
