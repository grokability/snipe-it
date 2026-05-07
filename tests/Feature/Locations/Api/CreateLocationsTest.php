<?php

namespace Tests\Feature\Locations\Api;

use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

class CreateLocationsTest extends TestCase
{
    public function test_requires_permission_to_create_location()
    {
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.locations.store'))
            ->assertForbidden();
    }

    public function test_can_create_location()
    {
        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.locations.store'), [
                'name' => 'Test Location',
                'notes' => 'Test Note',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success')
            ->assertStatus(200)
            ->json();

        $this->assertTrue(Location::where('name', 'Test Location')->exists());

        $location = Location::find($response['payload']['id']);
        $this->assertEquals('Test Location', $location->name);
        $this->assertEquals('Test Note', $location->notes);
    }

    public function test_cannot_create_new_locations_with_the_same_name()
    {
        $location = Location::factory()->create();
        $location2 = Location::factory()->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->patchJson(route('api.locations.update', $location2), [
                'name' => $location->name,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error')
            ->assertStatus(200)
            ->json();

    }

    public function test_user_cannot_create_locations_that_are_their_own_parent()
    {
        $location = Location::factory()->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->patchJson(route('api.locations.update', $location), [
                'parent_id' => $location->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error')
            ->assertJson([
                'messages' => [
                    'parent_id' => ['The parent id must not create a circular reference.'],
                ],
            ])
            ->json();

    }
}
