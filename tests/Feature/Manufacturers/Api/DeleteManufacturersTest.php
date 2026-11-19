<?php

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Manufacturer;
use App\Models\User;

test('requires permission', function () {
    $manufacturer = Manufacturer::factory()->create();

    $this->actingAsForApi(User::factory()->create())
        ->deleteJson(route('api.manufacturers.destroy', $manufacturer))
        ->assertForbidden();

    $this->assertNotSoftDeleted($manufacturer);
});

test('cannot delete manufacturer with associated data', function () {
    $manufacturerWithAccessories = Manufacturer::factory()->hasAccessories()->create();
    $manufacturerWithConsumables = Manufacturer::factory()->hasConsumables()->create();
    $manufacturerWithLicenses = Manufacturer::factory()->hasLicenses()->create();

    $manufacturerWithAssets = Manufacturer::factory()->hasAssets()->create();
    $model = AssetModel::factory()->create(['manufacturer_id' => $manufacturerWithAssets->id]);
    Asset::factory()->create(['model_id' => $model->id]);

    expect($manufacturerWithAccessories->accessories->count())->toBeGreaterThan(0, 'Precondition failed: Manufacturer has no accessories');
    expect($manufacturerWithAssets->assets->count())->toBeGreaterThan(0, 'Precondition failed: Manufacturer has no assets');
    expect($manufacturerWithConsumables->consumables->count())->toBeGreaterThan(0, 'Precondition failed: Manufacturer has no consumables');
    expect($manufacturerWithLicenses->licenses->count())->toBeGreaterThan(0, 'Precondition failed: Manufacturer has no licenses');

    $actor = $this->actingAsForApi(User::factory()->deleteManufacturers()->create());

    $actor->deleteJson(route('api.manufacturers.destroy', $manufacturerWithAccessories))->assertStatusMessageIs('error');
    $actor->deleteJson(route('api.manufacturers.destroy', $manufacturerWithAssets))->assertStatusMessageIs('error');
    $actor->deleteJson(route('api.manufacturers.destroy', $manufacturerWithConsumables))->assertStatusMessageIs('error');
    $actor->deleteJson(route('api.manufacturers.destroy', $manufacturerWithLicenses))->assertStatusMessageIs('error');

    $this->assertNotSoftDeleted($manufacturerWithAssets);
    $this->assertNotSoftDeleted($manufacturerWithAccessories);
    $this->assertNotSoftDeleted($manufacturerWithConsumables);
    $this->assertNotSoftDeleted($manufacturerWithLicenses);
});

test('can delete manufacturer', function () {
    $manufacturer = Manufacturer::factory()->create();

    $this->actingAsForApi(User::factory()->deleteManufacturers()->create())
        ->deleteJson(route('api.manufacturers.destroy', $manufacturer))
        ->assertOk()
        ->assertStatusMessageIs('success');

    $this->assertSoftDeleted($manufacturer);
});
