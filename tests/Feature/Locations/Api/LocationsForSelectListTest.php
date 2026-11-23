<?php

namespace Tests\Feature\Locations\Api;

use App\Models\Company;
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

    public function test_multiple_locations_are_excluded_when_exclude_ids_is_a_comma_separated_list(): void
    {
        // Used by the bulk-edit form's parent picker so a location can't
        // be picked as its own new parent (or the parent of a sibling
        // in the same batch, which would collapse the hierarchy).
        [$locationA, $locationB, $locationC] = Location::factory()->count(3)->create();

        $response = $this->actingAsForApi(User::factory()->createUsers()->create())
            ->getJson(route('api.locations.selectlist', ['excludeIds' => $locationA->id.','.$locationB->id]))
            ->assertOk();

        $ids = collect($response->json('results'))->pluck('id')->all();
        $this->assertNotContains($locationA->id, $ids);
        $this->assertNotContains($locationB->id, $ids);
        $this->assertContains($locationC->id, $ids);
    }

    public function test_exclude_ids_also_accepts_a_native_array(): void
    {
        // Belt-and-suspenders: js-data-ajax forwards data-exclude-ids as a
        // comma-separated string, but a caller building the URL by hand
        // (Postman, cURL, etc.) could pass excludeIds[] as an array.
        [$locationA, $locationB, $locationC] = Location::factory()->count(3)->create();

        $response = $this->actingAsForApi(User::factory()->createUsers()->create())
            ->getJson(route('api.locations.selectlist', ['excludeIds' => [$locationA->id, $locationB->id]]))
            ->assertOk();

        $ids = collect($response->json('results'))->pluck('id')->all();
        $this->assertNotContains($locationA->id, $ids);
        $this->assertNotContains($locationB->id, $ids);
        $this->assertContains($locationC->id, $ids);
    }

    public function test_self_edit_location_user_can_fetch_selectlist_for_profile_picker()
    {
        $this->actingAsForApi(User::factory()->canEditOwnLocation()->create())
            ->getJson(route('api.locations.selectlist'))
            ->assertOk();
    }

    public function test_referer_header_cannot_be_used_to_bypass_the_view_selectlists_gate()
    {
        $this->actingAsForApi(User::factory()->create())
            ->withHeader('referer', route('profile'))
            ->getJson(route('api.locations.selectlist'))
            ->assertForbidden();
    }

    public function test_self_edit_location_user_sees_only_own_company_locations_under_fmcs_location_scoping()
    {
        // Under FMCS + scope_locations_fmcs, Location's CompanyableTrait +
        // global CompanyableScope filters the query by the caller's company
        // pivot. A self.edit_location-only caller from company A must not
        // see company B's locations through the profile picker.
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $ownLocation = Location::factory()->create(['name' => 'ScopedOwn', 'company_id' => $companyA->id]);
        $otherLocation = Location::factory()->create(['name' => 'ScopedOther', 'company_id' => $companyB->id]);

        $actor = User::factory()->canEditOwnLocation()->forCompany($companyA->id)->create();

        $response = $this->actingAsForApi($actor)
            ->getJson(route('api.locations.selectlist'))
            ->assertOk();

        $ids = collect($response->json('results'))->pluck('id')->all();
        $this->assertContains($ownLocation->id, $ids, 'Own-company location must be visible to a self.edit_location-only caller.');
        $this->assertNotContains($otherLocation->id, $ids, 'Other-company location must not leak under FMCS + location-scoping.');
    }

    public function test_search_result_shows_plain_names_without_parent_chain(): void
    {
        // Per #19398, the location dropdown reverted from the breadcrumb
        // form (`DC1 › RackA`) to plain indentation. Search results are
        // cherry-picked out of the tree so there's no depth to indent by;
        // they render as plain names and the user's search term supplies
        // the disambiguation context.
        $dc1 = Location::factory()->create(['name' => 'DC1']);
        $dc2 = Location::factory()->create(['name' => 'DC2']);
        Location::factory()->create(['name' => 'RackA', 'parent_id' => $dc1->id]);
        Location::factory()->create(['name' => 'RackB', 'parent_id' => $dc2->id]);

        $response = $this->actingAsForApi(User::factory()->createUsers()->create())
            ->getJson(route('api.locations.selectlist', ['search' => 'Rack']))
            ->assertOk();

        $texts = collect($response->json('results'))->pluck('text');
        $this->assertTrue($texts->contains('RackA'));
        $this->assertTrue($texts->contains('RackB'));
    }

    public function test_search_result_for_deeply_nested_match_shows_plain_name(): void
    {
        // Deeper tree: HQ > DC1 > Rack 1. Only the matched leaf's name
        // renders in the search result — no ancestor chain.
        $hq = Location::factory()->create(['name' => 'HQ']);
        $dc1 = Location::factory()->create(['name' => 'DC1', 'parent_id' => $hq->id]);
        Location::factory()->create(['name' => 'Rack 1', 'parent_id' => $dc1->id]);

        $response = $this->actingAsForApi(User::factory()->createUsers()->create())
            ->getJson(route('api.locations.selectlist', ['search' => 'Rack 1']))
            ->assertOk();

        $texts = collect($response->json('results'))->pluck('text');
        $this->assertTrue($texts->contains('Rack 1'));
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

    public function test_unsearched_dropdown_uses_dash_indentation_for_nested_locations(): void
    {
        // Pins the reverted-to-old-style dropdown display. Root shows plain
        // name, children get "-- " prefix, grandchildren "---- ", etc.
        $hq = Location::factory()->create(['name' => 'HQ']);
        $dc1 = Location::factory()->create(['name' => 'DC1', 'parent_id' => $hq->id]);
        Location::factory()->create(['name' => 'Rack 1', 'parent_id' => $dc1->id]);

        $response = $this->actingAsForApi(User::factory()->createUsers()->create())
            ->getJson(route('api.locations.selectlist'))
            ->assertOk();

        $texts = collect($response->json('results'))->pluck('text');
        $this->assertTrue($texts->contains('HQ'), 'Top-level location renders without indent prefix.');
        $this->assertTrue($texts->contains('-- DC1'), 'One-level-deep location gets a two-dash indent.');
        $this->assertTrue($texts->contains('---- Rack 1'), 'Two-levels-deep location gets a four-dash indent.');
    }

    /**
     * #19394 regression: under FMCS + floater mode, a company-scoped user
     * asking for locations narrowed by companyId should also see
     * null-company (floater) locations. Matches the documented "items
     * from any company can be checked out to targets with no company
     * assignment" rule that server-side canCheckoutTo already enforces.
     */
    public function test_floater_locations_appear_in_selectlist_under_floater_mode_when_narrowed_by_company()
    {
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->enableFloaterMode();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $companyLocation = Location::factory()->create(['name' => 'FloaterModeLocA', 'company_id' => $companyA->id]);
        $otherCompanyLocation = Location::factory()->create(['name' => 'FloaterModeLocB', 'company_id' => $companyB->id]);
        $floaterLocation = Location::factory()->create(['name' => 'FloaterModeLocNull', 'company_id' => null]);

        $actor = User::factory()->createUsers()->forCompany($companyA->id)->create();

        $response = $this->actingAsForApi($actor)
            ->getJson(route('api.locations.selectlist', ['companyId' => $companyA->id]))
            ->assertOk();

        $ids = collect($response->json('results'))->pluck('id')->all();
        $this->assertContains($companyLocation->id, $ids, 'Same-company location must be visible.');
        $this->assertContains($floaterLocation->id, $ids, 'Null-company (floater) location must be visible under floater mode.');
        $this->assertNotContains($otherCompanyLocation->id, $ids, 'Other-company location must not leak in.');
    }

    /**
     * #19394 negative counterpart: under FMCS + strict mode (floater OFF),
     * the companyId narrowing must stay exact — null-company locations
     * do not leak into the picker for a company-scoped caller.
     */
    public function test_floater_locations_are_hidden_in_selectlist_under_strict_mode_when_narrowed_by_company()
    {
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();

        $companyA = Company::factory()->create();
        $companyLocation = Location::factory()->create(['name' => 'StrictLocA', 'company_id' => $companyA->id]);
        $floaterLocation = Location::factory()->create(['name' => 'StrictLocNull', 'company_id' => null]);

        $actor = User::factory()->createUsers()->forCompany($companyA->id)->create();

        $response = $this->actingAsForApi($actor)
            ->getJson(route('api.locations.selectlist', ['companyId' => $companyA->id]))
            ->assertOk();

        $ids = collect($response->json('results'))->pluck('id')->all();
        $this->assertContains($companyLocation->id, $ids);
        $this->assertNotContains($floaterLocation->id, $ids, 'Null-company location must not appear under strict mode.');
    }

    public function test_all_1_returns_every_matching_location_past_page_size_and_honors_search(): void
    {
        // #20106: Select All on the advanced-filter multi-select was
        // returning page 1 only (hard cap at 50) when ?all=1 was set,
        // and (via a JS bug) was posting search='' from the wrong DOM
        // location so the server returned everything instead of the
        // operator's typed scope. Server-side half of the fix: the
        // locations selectlist now routes through paginateCollectionOrAll,
        // which honors ?all=1 AND still applies the search filter the
        // controller already runs earlier in the method. Names must be
        // unique per Location's unique_undeleted rule, so each row gets
        // a distinct integer suffix while still sharing the typeahead
        // substring.
        for ($i = 0; $i < 60; $i++) {
            Location::factory()->create(['name' => 'Scope Match 1-'.$i]);
        }
        for ($i = 0; $i < 60; $i++) {
            Location::factory()->create(['name' => 'Scope Match 2-'.$i]);
        }
        for ($i = 0; $i < 10; $i++) {
            Location::factory()->create(['name' => 'Unrelated Zone '.$i]);
        }

        $response = $this->actingAsForApi(User::factory()->createUsers()->create())
            ->getJson(route('api.locations.selectlist', ['all' => 1, 'search' => 'Scope Match']))
            ->assertOk();

        $texts = collect($response->json('results'))->pluck('text')->all();
        $this->assertCount(120, $texts, '?all=1 must return every search-matching row, not just page 1.');
        foreach ($texts as $text) {
            $this->assertStringContainsString('Scope Match', $text, 'Non-matching row leaked past the typeahead filter.');
        }
    }
}
