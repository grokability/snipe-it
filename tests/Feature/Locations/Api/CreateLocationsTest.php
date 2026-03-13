<?php

namespace Tests\Feature\Locations\Api;

use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

class CreateLocationsTest extends TestCase
{

    public function testRequiresPermissionToCreateLocation()
    {
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.departments.store'))
            ->assertForbidden();
    }


    public function testCanCreateLocation()
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

        $department = Location::find($response['payload']['id']);
        $this->assertEquals('Test Location', $department->name);
        $this->assertEquals('Test Note', $department->notes);
    }

    public function testCannotCreateNewLocationsWithTheSameName()
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

    public function testUserCannotCreateLocationsThatAreTheirOwnParent()
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
                    'parent_id'    => ['The parent id must not create a circular reference.'],
                ],
            ])
            ->json();


    }

}
