<?php

namespace Tests\Feature\Locations\Ui;

use App\Models\User;
use Tests\TestCase;

class IndexLocationsTest extends TestCase
{
    public function testPermissionRequiredToViewLocationsList()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('locations.index'))
            ->assertForbidden();
    }

    public function testUserCanListLocations()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('locations.index'))
            ->assertOk();
    }
}
