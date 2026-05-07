<?php

namespace Tests\Feature\Locations\Api;

use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

class LocationsViewTest extends TestCase
{
    public function test_viewing_location_requires_permission()
    {
        $location = Location::factory()->create();
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.locations.show', $location->id))
            ->assertForbidden();
    }

    public function test_viewing_location_asset_index_requires_permission()
    {
        $location = Location::factory()->create();
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.locations.viewassets', $location->id))
            ->assertForbidden();
    }

    public function test_viewing_location_asset_index()
    {
        $location = Location::factory()->create();
        Asset::factory()->count(3)->create(['location_id' => $location->id]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.locations.viewassets', $location->id))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson([
                'total' => 3,
            ]);
    }

    public function test_viewing_assigned_location_asset_index()
    {
        $location = Location::factory()->create();
        Asset::factory()->count(3)->assignedToLocation($location)->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.locations.assigned_assets', $location->id))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson([
                'total' => 3,
            ]);
    }
}
