<?php

namespace Tests\Feature\Locations\Ui;

use App\Models\Consumable;
use Tests\TestCase;
use App\Models\Location;
use App\Models\Accessory;
use App\Models\User;
use App\Models\Asset;

class DeleteLocationsTest extends TestCase
{

    public function testRequiresPermission()
    {
        $this->actingAs(User::factory()->create())
            ->delete(route('locations.destroy', Location::factory()->create()))
            ->assertForbidden();
    }

    public function testCanDeleteLocation()
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
    

    public function testCannotDeleteLocationWithAssetsAsLocation()
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

    public function testCannotDeleteLocationWithAssetsAssigned()
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

    public function testCannotDeleteLocationWithChildren()
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

    public function testCannotDeleteLocationWithConsumableAsLocation()
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

    public function testCannotDeleteLocationWithAccessoriesAssigned()
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

    public function testCannotDeleteLocationWithAccessoriesAsLocation()
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

    public function testCannotDeleteLocationWithPeople()
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
