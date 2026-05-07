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
    public function test_requires_permission()
    {
        $location = Location::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->deleteJson(route('api.locations.destroy', $location))
            ->assertForbidden();

        $this->assertNotSoftDeleted($location);
    }

    public function test_error_returned_via_api_if_location_does_not_exist()
    {
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->deleteJson(route('api.locations.destroy', 'invalid-id'))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();

    }

    public function test_error_returned_via_api_if_location_is_already_deleted()
    {
        $location = Location::factory()->deletedLocation()->create();
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->deleteJson(route('api.locations.destroy', $location->id))
            ->assertOk()
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->json();
    }

    public function test_disallow_location_deletion_via_api_if_still_has_people()
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

    public function test_disallow_location_deletion_via_api_if_still_has_child_locations()
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

    public function test_disallow_location_deletion_via_api_if_still_has_assets_assigned()
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

    public function test_disallow_location_deletion_via_api_if_still_has_assets_as_location()
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

    public function test_disallow_location_deletion_via_api_if_still_has_consumables_as_location()
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

    public function test_disallow_location_deletion_via_api_if_still_has_components_as_location()
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

    public function test_disallow_location_deletion_via_api_if_still_has_accessories_assigned()
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

    public function test_disallow_location_deletion_via_api_if_still_has_accessories_as_location()
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

    public function test_can_delete_location()
    {
        $location = Location::factory()->create();

        $this->actingAsForApi(User::factory()->deleteLocations()->create())
            ->deleteJson(route('api.locations.destroy', $location->id))
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertSoftDeleted($location);
    }
}
