<?php

namespace Tests\Feature\Users\Api;

use App\Models\Location;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class IndexUsersTest extends TestCase
{
    public function testRequiresPermission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.users.index'))
            ->assertForbidden();
    }

    public function testReturnsManagedUsersCountCorrectly()
    {
        $manager = User::factory()->create(['first_name' => 'Manages Users']);
        User::factory()->create(['first_name' => 'Does Not Manage Users']);

        User::factory()->create(['manager_id' => $manager->id]);
        User::factory()->create(['manager_id' => $manager->id]);

        $response = $this->actingAsForApi(User::factory()->viewUsers()->create())
            ->getJson(route('api.users.index', [
                'manages_users_count' => 2,
            ]))
            ->assertOk();

        $response->assertJson(function (AssertableJson $json) {
            $json->has('rows', 1)
                ->where('rows.0.first_name', 'Manages Users')
                ->etc();
        });
    }

    public function testReturnsManagedLocationsCountCorrectly()
    {
        $manager = User::factory()->create(['first_name' => 'Manages Locations']);
        User::factory()->create(['first_name' => 'Does Not Manage Locations']);

        Location::factory()->create(['manager_id' => $manager->id]);
        Location::factory()->create(['manager_id' => $manager->id]);

        $response = $this->actingAsForApi(User::factory()->viewUsers()->create())
            ->getJson(route('api.users.index', [
                'manages_locations_count' => 2,
            ]))
            ->assertOk();

        $response->assertJson(function (AssertableJson $json) {
            $json->has('rows', 1)
                ->where('rows.0.first_name', 'Manages Locations')
                ->etc();
        });
    }
}
