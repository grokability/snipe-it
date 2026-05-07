<?php

namespace Tests\Feature\Locations\Api;

use App\Models\Location;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class LocationsForSelectListTest extends TestCase
{
    public function test_getting_location_list_requires_proper_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.locations.selectlist'))
            ->assertForbidden();
    }

    public function test_locations_returned()
    {
        Location::factory()->create();

        // see the where the "view.selectlists" is defined in the AuthServiceProvider
        // for info on why "createUsers()" is used here.
        $this->actingAsForApi(User::factory()->createUsers()->create())
            ->getJson(route('api.locations.selectlist'))
            ->assertOk()
            ->assertJsonStructure([
                'results',
                'pagination',
                'total_count',
                'page',
                'page_count',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('results', 1)->etc());
    }

    public function test_locations_are_returned_when_user_is_updating_their_profile_and_has_permission_to_update_location()
    {
        $this->actingAsForApi(User::factory()->canEditOwnLocation()->create())
            ->withHeader('referer', route('profile'))
            ->getJson(route('api.locations.selectlist'))
            ->assertOk();
    }
}
