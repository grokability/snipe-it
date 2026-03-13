<?php

namespace Tests\Feature\Locations\Api;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\Location;
use App\Models\User;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class DeleteLocationsTest extends TestCase implements TestsPermissionsRequirement
{
    public function testRequiresPermission()
    {
        $location = Location::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->deleteJson(route('api.locations.destroy', $location))
            ->assertForbidden();

        $this->assertNotSoftDeleted($location);
    }

    public function testErrorReturnedViaApiIfLocationDoesNotExist()
    {
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->deleteJson(route('api.locations.destroy', 'invalid-id'))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();

    }

    public function testErrorReturnedViaApiIfLocationIsAlreadyDeleted()
    {
        $location = Location::factory()->deletedLocation()->create();
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->deleteJson(route('api.locations.destroy', $location->id))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();
    }

    public function testDisallowLocationDeletionViaApiIfStillHasPeople()
    {
        $location = Location::factory()->create();
        User::factory()->count(5)->create(['location_id' => $location->id]);

        $this->assertFalse($location->isDeletable());

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->deleteJson(route('api.locations.destroy', $location->id))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();
        $this->assertNotSoftDeleted($location);
    }

    public function testDisallowLocationDeletionViaApiIfStillHasChildLocations()
    {
        $parent = Location::factory()->create();
        Location::factory()->count(5)->create(['parent_id' => $parent->id]);
        $this->assertFalse($parent->isDeletable());

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->deleteJson(route('api.locations.destroy', $parent->id))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();
        $this->assertNotSoftDeleted($parent);
    }

    public function testDisallowLocationDeletionViaApiIfStillHasAssetsAssigned()
    {
        $location = Location::factory()->create();
        Asset::factory()->count(5)->assignedToLocation($location)->create();

        $this->assertFalse($location->isDeletable());

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->deleteJson(route('api.locations.destroy', $location->id))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();
        $this->assertNotSoftDeleted($location);
    }

    public function testDisallowLocationDeletionViaApiIfStillHasAssetsAsLocation()
    {
        $location = Location::factory()->create();
        Asset::factory()->count(5)->create(['location_id' => $location->id]);

        $this->assertFalse($location->isDeletable());

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->deleteJson(route('api.locations.destroy', $location->id))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();
        $this->assertNotSoftDeleted($location);
    }

    public function testDisallowLocationDeletionViaApiIfStillHasConsumablesAsLocation()
    {
        $location = Location::factory()->create();
        Consumable::factory()->count(5)->create(['location_id' => $location->id]);

        $this->assertFalse($location->isDeletable());

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->deleteJson(route('api.locations.destroy', $location->id))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();
        $this->assertNotSoftDeleted($location);
    }

    public function testDisallowLocationDeletionViaApiIfStillHasComponentsAsLocation()
    {
        $location = Location::factory()->create();
        Component::factory()->count(5)->create(['location_id' => $location->id]);

        $this->assertFalse($location->isDeletable());

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->deleteJson(route('api.locations.destroy', $location->id))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();

        $this->assertNotSoftDeleted($location);
    }

    public function testDisallowLocationDeletionViaApiIfStillHasAccessoriesAssigned()
    {
        $location = Location::factory()->create();
        Accessory::factory()->count(5)->checkedOutToLocation($location)->create();

        $this->assertFalse($location->isDeletable());

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->deleteJson(route('api.locations.destroy', $location->id))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();
        $this->assertNotSoftDeleted($location);
    }

    public function testDisallowLocationDeletionViaApiIfStillHasAccessoriesAsLocation()
    {
        $location = Location::factory()->create();
        Accessory::factory()->count(5)->create(['location_id' => $location->id]);

        $this->assertFalse($location->isDeletable());

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->deleteJson(route('api.locations.destroy', $location->id))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();

        $this->assertNotSoftDeleted($location);
    }

    public function testCanDeleteLocation()
    {
        $location = Location::factory()->create();

        $this->actingAsForApi(User::factory()->deleteLocations()->create())
            ->deleteJson(route('api.locations.destroy', $location->id))
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertSoftDeleted($location);
    }
}
