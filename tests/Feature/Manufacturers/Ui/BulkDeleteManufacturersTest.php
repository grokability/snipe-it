<?php

namespace Tests\Feature\Manufacturers\Ui;

use App\Models\Accessory;
use App\Models\Manufacturer;
use App\Models\User;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class BulkDeleteManufacturersTest extends TestCase implements TestsPermissionsRequirement
{
    public function test_requires_permission()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('manufacturers.bulk.delete'), [
                'ids' => [1, 2, 3],
            ])
            ->assertForbidden();
    }

    public function test_manufacturer_cannot_be_bulk_deleted_if_models_still_associated()
    {
        // TODO: better test for specific messages
        $manufacturer = Manufacturer::factory()->create();
        Accessory::factory()->for($manufacturer)->create();
        $this->actingAs(User::factory()->deleteManufacturers()->create())
            ->post(route('manufacturers.bulk.delete'), [
                'ids' => [$manufacturer->id],
            ]);
        $this->assertModelExists($manufacturer);
        $this->assertNotSoftDeleted($manufacturer);
    }

    public function test_manufacturers_can_be_bulk_deleted()
    {
        $manufacturer1 = Manufacturer::factory()->create();
        $manufacturer2 = Manufacturer::factory()->create();
        $manufacturer3 = Manufacturer::factory()->create();

        $this->actingAs(User::factory()->deleteManufacturers()->create())
            ->post(route('manufacturers.bulk.delete'), [
                'ids' => [$manufacturer1->id, $manufacturer2->id, $manufacturer3->id],
            ])
            ->assertRedirect(route('manufacturers.index'));

        $this->assertSoftDeleted($manufacturer1);
        $this->assertSoftDeleted($manufacturer2);
        $this->assertSoftDeleted($manufacturer3);
    }

    public function test_bulk_success_message_pluralizes_by_count()
    {
        $solo = Manufacturer::factory()->create();

        $this->actingAs(User::factory()->deleteManufacturers()->create())
            ->post(route('manufacturers.bulk.delete'), [
                'ids' => [$solo->id],
            ])
            ->assertSessionHas('success', trans_choice('admin/manufacturers/message.delete.bulk_success', 1, ['count' => 1]));

        $a = Manufacturer::factory()->create();
        $b = Manufacturer::factory()->create();
        $c = Manufacturer::factory()->create();

        $this->actingAs(User::factory()->deleteManufacturers()->create())
            ->post(route('manufacturers.bulk.delete'), [
                'ids' => [$a->id, $b->id, $c->id],
            ])
            ->assertSessionHas('success', trans_choice('admin/manufacturers/message.delete.bulk_success', 3, ['count' => 3]));
    }
}
