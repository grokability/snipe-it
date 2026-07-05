<?php

namespace Tests\Feature\Users\Api;

use App\Models\Asset;
use App\Models\Location;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class IndexUsersTest extends TestCase
{
    public function test_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.users.index'))
            ->assertForbidden();
    }

    public function test_returns_managed_users_count_correctly()
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

    public function test_returns_managed_locations_count_correctly()
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

    public function test_returns_assigned_maintenances_count_correctly()
    {
        // The withCount/has filter both target the new assignedMaintenances
        // morphMany. checked_out_to_* on a maintenance is populated by
        // MaintenanceObserver::creating() from the asset's assigned_to —
        // so seed via "asset checked out to user, then maintenance on
        // that asset" rather than passing checked_out_to_* directly.
        $busy = User::factory()->create(['first_name' => 'Busy Breaker']);
        User::factory()->create(['first_name' => 'Quiet User']);

        $busyAsset = Asset::factory()->assignedToUser($busy)->create();
        Maintenance::factory()->create(['asset_id' => $busyAsset->id]);
        Maintenance::factory()->create([
            'asset_id' => $busyAsset->id,
            'completed_at' => now(),
        ]);

        $response = $this->actingAsForApi(User::factory()->viewUsers()->create())
            ->getJson(route('api.users.index', [
                'assigned_maintenances_count' => 2,
            ]))
            ->assertOk();

        $response->assertJson(function (AssertableJson $json) {
            $json->has('rows', 1)
                ->where('rows.0.first_name', 'Busy Breaker')
                ->where('rows.0.assigned_maintenances_count', 2)
                ->etc();
        });
    }

    public function test_can_sort_users_index_by_assigned_maintenances_count()
    {
        $most = User::factory()->create();
        $some = User::factory()->create();

        $mostAsset = Asset::factory()->assignedToUser($most)->create();
        Maintenance::factory()->count(3)->create(['asset_id' => $mostAsset->id]);

        $someAsset = Asset::factory()->assignedToUser($some)->create();
        Maintenance::factory()->create(['asset_id' => $someAsset->id]);

        $response = $this->actingAsForApi(User::factory()->viewUsers()->create())
            ->getJson(route('api.users.index', [
                'sort' => 'assigned_maintenances_count',
                'order' => 'desc',
            ]))
            ->assertOk();

        // Filter to just the two users we created with maintenances; everyone
        // else seeded by factories has count=0 and their relative order is
        // not what we're testing here. Position of $most must precede $some.
        $orderedIds = collect($response->json('rows'))->pluck('id');
        $mostPos = $orderedIds->search($most->id);
        $somePos = $orderedIds->search($some->id);

        $this->assertNotFalse($mostPos, 'User with 3 maintenances should appear in the result');
        $this->assertNotFalse($somePos, 'User with 1 maintenance should appear in the result');
        $this->assertLessThan(
            $somePos,
            $mostPos,
            'User with 3 maintenances must sort before user with 1 when ordering desc by assigned_maintenances_count',
        );
    }

    public function test_gracefully_handles_malformed_filter()
    {
        $this->actingAsForApi(User::factory()->viewUsers()->create())
            ->getJson(route('api.users.index', [
                // filter should be a json encoded array and not a string
                'filter' => 'email:an-email-address@example.com',
            ]))
            ->assertStatusMessageIs('error')
            ->assertJson(function (AssertableJson $json) {
                $json->has('messages.filter')->etc();
            });
    }

    public function test_returns_result_via_filter()
    {

        User::factory()->count(3)->create(['first_name' => 'Awesome', 'last_name' => 'Admin', 'email' => 'awesome@example.org']);
        $this->actingAsForApi(User::factory()->viewUsers()->create())
            ->getJson(route('api.users.index', [
                'filter' => '{"first_name":"Awesome","last_name":"Admin","email":"awesome@example.org"}',
            ]))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());

        $this->actingAsForApi(User::factory()->viewUsers()->create())
            ->getJson(route('api.users.index', [
                'filter' => '{"first_name":"Not Awesome"}',
            ]))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 0)->etc());
    }
}
