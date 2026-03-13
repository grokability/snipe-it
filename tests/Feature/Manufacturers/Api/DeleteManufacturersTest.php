<?php

namespace Tests\Feature\Manufacturers\Api;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Manufacturer;
use App\Models\User;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class DeleteManufacturersTest extends TestCase implements TestsPermissionsRequirement
{
    public function testRequiresPermission()
    {
        $manufacturer = Manufacturer::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->deleteJson(route('api.manufacturers.destroy', $manufacturer))
            ->assertForbidden();

        $this->assertNotSoftDeleted($manufacturer);
    }

    public function testCannotDeleteManufacturerWithAssociatedData()
    {
        $manufacturerWithAccessories = Manufacturer::factory()->hasAccessories()->create();
        $manufacturerWithConsumables = Manufacturer::factory()->hasConsumables()->create();
        $manufacturerWithLicenses = Manufacturer::factory()->hasLicenses()->create();

        $manufacturerWithAssets = Manufacturer::factory()->hasAssets()->create();
        $model = AssetModel::factory()->create(['manufacturer_id' => $manufacturerWithAssets->id]);
        Asset::factory()->create(['model_id' => $model->id]);

        $this->assertGreaterThan(0, $manufacturerWithAccessories->accessories->count(), 'Precondition failed: Manufacturer has no accessories');
        $this->assertGreaterThan(0, $manufacturerWithAssets->assets->count(), 'Precondition failed: Manufacturer has no assets');
        $this->assertGreaterThan(0, $manufacturerWithConsumables->consumables->count(), 'Precondition failed: Manufacturer has no consumables');
        $this->assertGreaterThan(0, $manufacturerWithLicenses->licenses->count(), 'Precondition failed: Manufacturer has no licenses');

        $actor = $this->actingAsForApi(User::factory()->deleteManufacturers()->create());

        $actor->deleteJson(route('api.manufacturers.destroy', $manufacturerWithAccessories))->assertStatusMessageIs('error');
        $actor->deleteJson(route('api.manufacturers.destroy', $manufacturerWithAssets))->assertStatusMessageIs('error');
        $actor->deleteJson(route('api.manufacturers.destroy', $manufacturerWithConsumables))->assertStatusMessageIs('error');
        $actor->deleteJson(route('api.manufacturers.destroy', $manufacturerWithLicenses))->assertStatusMessageIs('error');

        $this->assertNotSoftDeleted($manufacturerWithAssets);
        $this->assertNotSoftDeleted($manufacturerWithAccessories);
        $this->assertNotSoftDeleted($manufacturerWithConsumables);
        $this->assertNotSoftDeleted($manufacturerWithLicenses);
    }

    public function testCanDeleteManufacturer()
    {
        $manufacturer = Manufacturer::factory()->create();

        $this->actingAsForApi(User::factory()->deleteManufacturers()->create())
            ->deleteJson(route('api.manufacturers.destroy', $manufacturer))
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertSoftDeleted($manufacturer);
    }
}
