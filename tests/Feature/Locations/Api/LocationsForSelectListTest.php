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

    public function test_location_is_excluded_from_selectlist_when_exclude_id_matches()
    {
        [$locationA, $locationB] = Location::factory()->count(2)->create();

        $this->actingAsForApi(User::factory()->createUsers()->create())
            ->getJson(route('api.locations.selectlist', ['excludeId' => $locationA->id]))
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json->where('results', fn ($results) => collect($results)->doesntContain('id', $locationA->id) &&
                    collect($results)->contains('id', $locationB->id)
            )->etc()
            );
    }

    public function test_locations_are_returned_when_user_is_updating_their_profile_and_has_permission_to_update_location()
    {
        $this->actingAsForApi(User::factory()->canEditOwnLocation()->create())
            ->withHeader('referer', route('profile'))
            ->getJson(route('api.locations.selectlist'))
            ->assertOk();
    }

    public function test_search_result_shows_parent_chain_in_breadcrumb(): void
    {
        // Two data centers each with their own rack. Location::name is
        // `unique_undeleted` today so two children literally named
        // "Rack 1" cannot coexist, but the disambiguation the breadcrumb
        // provides is still valuable whenever the child names share a
        // prefix or the tree is deep.
        $dc1 = Location::factory()->create(['name' => 'DC1']);
        $dc2 = Location::factory()->create(['name' => 'DC2']);
        Location::factory()->create(['name' => 'RackA', 'parent_id' => $dc1->id]);
        Location::factory()->create(['name' => 'RackB', 'parent_id' => $dc2->id]);

        $response = $this->actingAsForApi(User::factory()->createUsers()->create())
            ->getJson(route('api.locations.selectlist', ['search' => 'Rack']))
            ->assertOk();

        $texts = collect($response->json('results'))->pluck('text');
        $this->assertTrue($texts->contains('DC1 › RackA'));
        $this->assertTrue($texts->contains('DC2 › RackB'));
    }

    public function test_search_result_walks_multiple_ancestor_levels(): void
    {
        // Deeper tree: HQ > DC1 > Rack 1. The chain should show every
        // ancestor level.
        $hq = Location::factory()->create(['name' => 'HQ']);
        $dc1 = Location::factory()->create(['name' => 'DC1', 'parent_id' => $hq->id]);
        Location::factory()->create(['name' => 'Rack 1', 'parent_id' => $dc1->id]);

        $response = $this->actingAsForApi(User::factory()->createUsers()->create())
            ->getJson(route('api.locations.selectlist', ['search' => 'Rack 1']))
            ->assertOk();

        $texts = collect($response->json('results'))->pluck('text');
        $this->assertTrue($texts->contains('HQ › DC1 › Rack 1'));
    }

    public function test_search_result_for_top_level_location_has_no_prefix(): void
    {
        // A match at the top of the tree has no ancestors, so its text
        // should be just its own name with no leading breadcrumb.
        Location::factory()->create(['name' => 'Standalone Site']);

        $response = $this->actingAsForApi(User::factory()->createUsers()->create())
            ->getJson(route('api.locations.selectlist', ['search' => 'Standalone']))
            ->assertOk();

        $texts = collect($response->json('results'))->pluck('text');
        $this->assertTrue($texts->contains('Standalone Site'));
    }
}
