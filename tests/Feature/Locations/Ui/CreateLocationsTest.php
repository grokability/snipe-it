<?php

namespace Tests\Feature\Locations\Ui;

use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

class CreateLocationsTest extends TestCase
{
    public function test_permission_required_to_create_location()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('locations.store'), [
                'name' => 'Test Location',
                'company_id' => Company::factory()->create()->id,
            ])
            ->assertForbidden();
    }

    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('locations.create'))
            ->assertOk();
    }

    public function test_user_can_create_locations()
    {
        $this->assertFalse(Location::where('name', 'Test Location')->exists());

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('locations.store'), [
                'name' => 'Test Location',
                'notes' => 'Test Note',
            ])
            ->assertRedirect(route('locations.index'));

        $this->assertTrue(Location::where('name', 'Test Location')->where('notes', 'Test Note')->exists());
    }

    public function test_user_cannot_create_locations_with_invalid_parent()
    {
        $this->assertFalse(Location::where('name', 'Test Location')->exists());

        $this->actingAs(User::factory()->superuser()->create())
            ->from(route('locations.create'))
            ->post(route('locations.store'), [
                'name' => 'Test Location',
                'parent_id' => '100000000',
            ])
            ->assertRedirect(route('locations.create'));

        $this->assertFalse(Location::where('name', 'Test Location')->exists());
    }
}
