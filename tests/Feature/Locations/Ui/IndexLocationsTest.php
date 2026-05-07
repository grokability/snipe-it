<?php

namespace Tests\Feature\Locations\Ui;

use App\Models\User;
use Tests\TestCase;

class IndexLocationsTest extends TestCase
{
    public function test_permission_required_to_view_locations_list()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('locations.index'))
            ->assertForbidden();
    }

    public function test_user_can_list_locations()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('locations.index'))
            ->assertOk();
    }
}
