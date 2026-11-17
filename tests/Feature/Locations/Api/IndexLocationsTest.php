<?php

namespace Tests\Feature\Locations\Api;

use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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

    public function test_index_row_includes_direct_parent_and_children()
    {
        $grandparent = Location::factory()->create();
        $parent = Location::factory()->create(['parent_id' => $grandparent->id]);
        $child = Location::factory()->create(['parent_id' => $parent->id]);

        $rows = collect($this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.locations.index'))
            ->assertOk()
            ->json('rows'));

        $parentRow = $rows->firstWhere('id', $parent->id);

        $this->assertEquals($grandparent->id, $parentRow['parent']['id']);
        $this->assertEquals([$child->id], array_column($parentRow['children'], 'id'));
    }

    public function test_index_does_not_crash_when_locations_form_a_parent_cycle()
    {
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();

        DB::table('locations')->where('id', $locationA->id)->update(['parent_id' => $locationB->id]);
        DB::table('locations')->where('id', $locationB->id)->update(['parent_id' => $locationA->id]);

        $rows = collect($this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.locations.index'))
            ->assertOk()
            ->json('rows'));

        $this->assertNotNull($rows->firstWhere('id', $locationB->id));
        $this->assertEquals($locationB->id, $rows->firstWhere('id', $locationA->id)['parent']['id']);
    }
}
