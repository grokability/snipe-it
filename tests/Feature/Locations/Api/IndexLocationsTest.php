<?php

namespace Tests\Feature\Locations\Api;

use App\Models\Location;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class IndexLocationsTest extends TestCase
{
    public function test_viewing_location_index_requires_authentication()
    {
        $this->getJson(route('api.locations.index'))->assertRedirect();
    }

    public function test_viewing_location_index_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.locations.index'))
            ->assertForbidden();
    }

    public function test_location_index_returns_expected_locations()
    {
        Location::factory()->count(3)->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(
                route('api.locations.index', [
                    'sort' => 'name',
                    'order' => 'asc',
                    'offset' => '0',
                    'limit' => '20',
                ]))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
    }

    public function test_search_matches_manager_name()
    {
        // Locations table shows manager as a column; before adding manager
        // to Location's $searchableRelations the search silently returned
        // nothing when typing a manager's name.
        $actor = User::factory()->superuser()->create();

        $manager = User::factory()->create(['first_name' => 'Anastasia', 'last_name' => 'Krupin']);
        $matchingLocation = Location::factory()->create(['manager_id' => $manager->id]);
        $otherLocation = Location::factory()->create();

        $ids = collect($this->actingAsForApi($actor)
            ->getJson(route('api.locations.index', ['search' => 'Anastasia']))
            ->assertOk()
            ->json('rows'))
            ->pluck('id')
            ->all();

        $this->assertContains($matchingLocation->id, $ids);
        $this->assertNotContains($otherLocation->id, $ids);
    }
}
