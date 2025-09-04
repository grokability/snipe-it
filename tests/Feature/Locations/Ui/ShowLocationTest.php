<?php

namespace Tests\Feature\Locations\Ui;

use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

class ShowLocationTest extends TestCase
{
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('locations.show', Location::factory()->create()))
            ->assertOk();
    }

    public function testDeniesAccessToRegularUser()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('locations.show', Location::factory()->create()))
            ->assertStatus(403)
            ->assertForbidden();
    }

    public function testDeniesPrintAccessToRegularUser()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('locations.print_all_assigned', Location::factory()->create()))
            ->assertStatus(403)
            ->assertForbidden();
    }

    public function testPageRendersForSuperAdmin()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('locations.print_all_assigned', Location::factory()->create()))
            ->assertOk();
    }
}
