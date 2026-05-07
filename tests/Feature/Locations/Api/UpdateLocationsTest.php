<?php

namespace Tests\Feature\Locations\Api;

use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

class UpdateLocationsTest extends TestCase
{
    public function test_requires_permission_to_edit_location()
    {
        $this->actingAsForApi(User::factory()->create())
            ->patchJson(route('api.locations.update', Location::factory()->create()))
            ->assertForbidden();
    }

    public function test_can_update_location_via_patch()
    {
        $location = Location::factory()->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->patchJson(route('api.locations.update', $location), [
                'name' => 'Test Updated Location',
                'notes' => 'Test Updated Note',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success')
            ->assertStatus(200)
            ->json();

        $location->refresh();
        $this->assertEquals('Test Updated Location', $location->name, 'Name was not updated');
        $this->assertEquals('Test Updated Note', $location->notes, 'Note was not updated');
    }
}
