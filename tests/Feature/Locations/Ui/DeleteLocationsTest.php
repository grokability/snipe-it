<?php

namespace Tests\Feature\Locations\Ui;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Consumable;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

class DeleteLocationsTest extends TestCase
{
    public function test_requires_permission()
    {
        $this->actingAs(User::factory()->create())
            ->delete(route('locations.destroy', Location::factory()->create()))
            ->assertForbidden();
    }

    public function test_can_delete_location()
    {
        $location = Location::factory()->create();

        $this->actingAs(User::factory()->deleteLocations()->create())
            ->delete(route('locations.destroy', $location))
            ->assertRedirectToRoute('locations.index')
            ->assertSessionHas('success')
            ->assertStatus(302)
            ->assertRedirect(route('locations.index'));

        $this->assertSoftDeleted($location);
    }

    public function test_cannot_delete_location_with_assets_as_location()
    {
        $location = Location::factory()->create();
        Asset::factory()->count(5)->create(['location_id' => $location->id]);

        $this->actingAs(User::factory()->deleteLocations()->create())
            ->delete(route('locations.destroy', $location))
            ->assertStatus(302)
            ->assertRedirectToRoute('locations.index')
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($location);
    }

    public function test_cannot_delete_location_with_assets_assigned()
    {
        $location = Location::factory()->create();
        Asset::factory()->count(5)->assignedToLocation($location)->create();

        $this->actingAs(User::factory()->deleteLocations()->create())
            ->delete(route('locations.destroy', $location))
            ->assertStatus(302)
            ->assertRedirectToRoute('locations.index')
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($location);
    }

    public function test_cannot_delete_location_with_children()
    {
        $parent = Location::factory()->create();
        Location::factory()->count(5)->create(['parent_id' => $parent->id]);

        $this->actingAs(User::factory()->deleteLocations()->create())
            ->delete(route('locations.destroy', $parent))
            ->assertStatus(302)
            ->assertRedirectToRoute('locations.index')
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($parent);
    }

    public function test_cannot_delete_location_with_consumable_as_location()
    {
        $location = Location::factory()->create();
        Consumable::factory()->count(5)->create(['location_id' => $location->id]);

        $this->actingAs(User::factory()->deleteLocations()->create())
            ->delete(route('locations.destroy', $location))
            ->assertStatus(302)
            ->assertRedirectToRoute('locations.index')
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($location);
    }

    public function test_cannot_delete_location_with_accessories_assigned()
    {
        $location = Location::factory()->create();
        Accessory::factory()->count(5)->checkedOutToLocation($location)->create();

        $this->actingAs(User::factory()->deleteLocations()->create())
            ->delete(route('locations.destroy', $location))
            ->assertStatus(302)
            ->assertRedirectToRoute('locations.index')
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($location);
    }

    public function test_cannot_delete_location_with_accessories_as_location()
    {
        $location = Location::factory()->create();
        Accessory::factory()->count(5)->create(['location_id' => $location->id]);

        $this->actingAs(User::factory()->deleteLocations()->create())
            ->delete(route('locations.destroy', $location))
            ->assertStatus(302)
            ->assertRedirectToRoute('locations.index')
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($location);
    }

    public function test_cannot_delete_location_with_people()
    {
        $location = Location::factory()->create();
        User::factory()->count(5)->create(['location_id' => $location->id]);

        $this->actingAs(User::factory()->deleteLocations()->create())
            ->delete(route('locations.destroy', $location))
            ->assertStatus(302)
            ->assertRedirectToRoute('locations.index')
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($location);
    }
}
