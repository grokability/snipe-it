<?php

namespace Tests\Feature\Companies\Api;

use App\Models\Asset;
use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

class CompanyHierarchyTest extends TestCase
{
    public function test_can_create_company_with_parent_id()
    {
        $parent = Company::factory()->create();

        $this->actingAsForApi(User::factory()->createCompanies()->create())
            ->postJson(route('api.companies.store'), [
                'name' => 'Subsidiary',
                'parent_id' => $parent->id,
            ])
            ->assertStatus(200)
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('companies', [
            'name' => 'Subsidiary',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_can_update_company_to_set_parent_id()
    {
        $parent = Company::factory()->create();
        $orphan = Company::factory()->create();

        $this->actingAsForApi(User::factory()->editCompanies()->create())
            ->patchJson(route('api.companies.update', ['company' => $orphan->id]), [
                'name' => $orphan->name,
                'parent_id' => $parent->id,
            ])
            ->assertStatus(200)
            ->assertStatusMessageIs('success');

        $this->assertEquals($parent->id, $orphan->fresh()->parent_id);
    }

    public function test_company_cannot_be_its_own_parent()
    {
        $company = Company::factory()->create();

        $this->actingAsForApi(User::factory()->editCompanies()->create())
            ->patchJson(route('api.companies.update', ['company' => $company->id]), [
                'name' => $company->name,
                'parent_id' => $company->id,
            ])
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->assertJsonStructure(['messages' => ['parent_id']]);

        $this->assertNull($company->fresh()->parent_id);
    }

    public function test_cannot_create_grandchild_company()
    {
        // Grandparent → Parent already exists; trying to make Child the parent of New should fail.
        $grandparent = Company::factory()->create();
        $parent = Company::factory()->childOf($grandparent)->create();

        $this->actingAsForApi(User::factory()->createCompanies()->create())
            ->postJson(route('api.companies.store'), [
                'name' => 'GreatGrandchild',
                'parent_id' => $parent->id,
            ])
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->assertJsonStructure(['messages' => ['parent_id']]);

        $this->assertDatabaseMissing('companies', ['name' => 'GreatGrandchild']);
    }

    public function test_validation_message_for_grandchild_attempt_mentions_top_level()
    {
        $grandparent = Company::factory()->create();
        $parent = Company::factory()->childOf($grandparent)->create();

        $response = $this->actingAsForApi(User::factory()->createCompanies()->create())
            ->postJson(route('api.companies.store'), [
                'name' => 'GreatGrandchildMessageCheck',
                'parent_id' => $parent->id,
            ])
            ->assertStatus(200)
            ->json();

        $this->assertNotEmpty($response['messages']['parent_id']);
        $message = implode(' ', (array) $response['messages']['parent_id']);
        $this->assertStringContainsString('top-level', $message);
        $this->assertStringNotContainsString('children of its own', $message, 'Grandchild attempt should not show the "row has children" message');
    }

    public function test_validation_message_for_demoting_parent_mentions_children()
    {
        $top = Company::factory()->create();
        $parentWithKids = Company::factory()->create();
        Company::factory()->childOf($parentWithKids)->create();

        $response = $this->actingAsForApi(User::factory()->editCompanies()->create())
            ->patchJson(route('api.companies.update', ['company' => $parentWithKids->id]), [
                'name' => $parentWithKids->name,
                'parent_id' => $top->id,
            ])
            ->assertStatus(200)
            ->json();

        $this->assertNotEmpty($response['messages']['parent_id']);
        $message = implode(' ', (array) $response['messages']['parent_id']);
        $this->assertStringContainsString('children of its own', $message);
        $this->assertStringNotContainsString('top-level', $message, 'Demote-with-children attempt should not show the "parent must be top-level" message');
    }

    public function test_cannot_assign_parent_to_company_that_has_children()
    {
        $grandparentCandidate = Company::factory()->create();
        $parent = Company::factory()->create();
        Company::factory()->childOf($parent)->create();

        $this->actingAsForApi(User::factory()->editCompanies()->create())
            ->patchJson(route('api.companies.update', ['company' => $parent->id]), [
                'name' => $parent->name,
                'parent_id' => $grandparentCandidate->id,
            ])
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->assertJsonStructure(['messages' => ['parent_id']]);

        $this->assertNull($parent->fresh()->parent_id);
    }

    public function test_can_clear_parent_id_by_passing_null()
    {
        $parent = Company::factory()->create();
        $child = Company::factory()->childOf($parent)->create();

        $this->actingAsForApi(User::factory()->editCompanies()->create())
            ->patchJson(route('api.companies.update', ['company' => $child->id]), [
                'name' => $child->name,
                'parent_id' => null,
            ])
            ->assertStatus(200)
            ->assertStatusMessageIs('success');

        $this->assertNull($child->fresh()->parent_id);
    }

    public function test_parent_id_of_zero_is_stored_as_null()
    {
        $this->actingAsForApi(User::factory()->createCompanies()->create())
            ->postJson(route('api.companies.store'), [
                'name' => 'TopLevelViaZero',
                'parent_id' => 0,
            ])
            ->assertStatus(200)
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('companies', [
            'name' => 'TopLevelViaZero',
            'parent_id' => null,
        ]);
    }

    public function test_parent_id_of_empty_string_is_stored_as_null()
    {
        $this->actingAsForApi(User::factory()->createCompanies()->create())
            ->postJson(route('api.companies.store'), [
                'name' => 'TopLevelViaEmpty',
                'parent_id' => '',
            ])
            ->assertStatus(200)
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('companies', [
            'name' => 'TopLevelViaEmpty',
            'parent_id' => null,
        ]);
    }

    public function test_parent_id_must_reference_existing_company()
    {
        $this->actingAsForApi(User::factory()->createCompanies()->create())
            ->postJson(route('api.companies.store'), [
                'name' => 'Orphaned',
                'parent_id' => 999999,
            ])
            ->assertStatus(200)
            ->assertStatusMessageIs('error')
            ->assertJsonStructure(['messages' => ['parent_id']]);
    }

    public function test_cannot_delete_company_that_has_children()
    {
        $parent = Company::factory()->create();
        Company::factory()->childOf($parent)->create();

        $this->actingAsForApi(User::factory()->deleteCompanies()->create())
            ->deleteJson(route('api.companies.destroy', $parent))
            ->assertStatusMessageIs('error');

        $this->assertDatabaseHas('companies', ['id' => $parent->id, 'deleted_at' => null]);
    }

    public function test_index_exposes_parent_and_children_count()
    {
        $parent = Company::factory()->create();
        Company::factory()->count(2)->childOf($parent)->create();

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.companies.index', ['search' => $parent->name]))
            ->assertOk()
            ->json();

        $parentRow = collect($response['rows'])->firstWhere('id', $parent->id);

        $this->assertNotNull($parentRow);
        $this->assertNull($parentRow['parent']);
        $this->assertEquals(2, $parentRow['children_count']);
    }

    public function test_index_loads_for_fmcs_scoped_non_superuser()
    {
        // Regression: CompanyableScope hardcodes `companies.id`, which clashes with
        // the alias Eloquent picks for the children-count self-relation subquery.
        // Reproduce the original "Unknown column laravel_reserved_0.parent_id" by
        // hitting the index as a non-superuser with FMCS enabled.
        $this->settings->enableMultipleFullCompanySupport();

        $parent = Company::factory()->create();
        Company::factory()->count(2)->childOf($parent)->create();

        $user = $parent->users()->save(User::factory()->viewCompanies()->make());

        $this->actingAsForApi($user)
            ->getJson(route('api.companies.index'))
            ->assertOk();
    }

    public function test_index_can_sort_by_parent_company_name()
    {
        $alpha = Company::factory()->create(['name' => 'AlphaParent']);
        $zulu = Company::factory()->create(['name' => 'ZuluParent']);
        $alphaChild = Company::factory()->childOf($alpha)->create(['name' => 'AlphaChild']);
        $zuluChild = Company::factory()->childOf($zulu)->create(['name' => 'ZuluChild']);

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.companies.index', ['sort' => 'parent', 'order' => 'asc', 'limit' => 200]))
            ->assertOk()
            ->json();

        $ids = collect($response['rows'])->pluck('id')->all();
        $alphaChildPos = array_search($alphaChild->id, $ids);
        $zuluChildPos = array_search($zuluChild->id, $ids);

        $this->assertNotFalse($alphaChildPos);
        $this->assertNotFalse($zuluChildPos);
        $this->assertLessThan($zuluChildPos, $alphaChildPos, 'AlphaParent child should sort before ZuluParent child when sorting by parent name asc');
    }

    public function test_show_exposes_parent_block_for_child_company()
    {
        $parent = Company::factory()->create();
        $child = Company::factory()->childOf($parent)->create();

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.companies.show', $child))
            ->assertOk()
            ->json();

        $this->assertEquals($parent->id, $response['parent']['id']);
        // Transformer emits e($parent->name) — mirror that on the LHS so a
        // faker name containing an apostrophe or other special char round-trips.
        $this->assertEquals(e($parent->name), $response['parent']['name']);
    }

    public function test_user_in_parent_company_has_fmcs_access_to_child_company_assets()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $parent = Company::factory()->create();
        $child = Company::factory()->childOf($parent)->create();

        // Asset belongs to the child company.
        $assetInChild = Asset::factory()->create(['company_id' => $child->id]);

        // User is a member of the parent only.
        $userInParent = $parent->users()->save(User::factory()->viewAssets()->create());

        $response = $this->actingAsForApi($userInParent)
            ->getJson(route('api.assets.index'))
            ->assertOk()
            ->json();

        $foundIds = collect($response['rows'])->pluck('id')->all();

        $this->assertContains($assetInChild->id, $foundIds, 'User in parent should see child-company assets');
    }

    public function test_user_in_child_company_does_not_see_parent_company_assets()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $parent = Company::factory()->create();
        $child = Company::factory()->childOf($parent)->create();

        $assetInParent = Asset::factory()->create(['company_id' => $parent->id]);

        $userInChild = $child->users()->save(User::factory()->viewAssets()->create());

        $response = $this->actingAsForApi($userInChild)
            ->getJson(route('api.assets.index'))
            ->assertOk()
            ->json();

        $foundIds = collect($response['rows'])->pluck('id')->all();

        $this->assertNotContains($assetInParent->id, $foundIds, 'User in child should not see parent-company assets');
    }

    public function test_user_in_parent_can_filter_companies_selectlist_and_see_children()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $parent = Company::factory()->create();
        $child = Company::factory()->childOf($parent)->create();
        $unrelated = Company::factory()->create();

        // view.selectlists is gated on having create/update on at least one resource.
        $userInParent = $parent->users()->save(User::factory()->createAssets()->create());

        $response = $this->actingAsForApi($userInParent)
            ->getJson(route('api.companies.selectlist'))
            ->assertOk()
            ->json();

        $ids = collect($response['results'])->pluck('id')->all();

        $this->assertContains($parent->id, $ids);
        $this->assertContains($child->id, $ids);
        $this->assertNotContains($unrelated->id, $ids);
    }

    public function test_selectlist_groups_children_under_their_parent_with_indented_text()
    {
        $alphaParent = Company::factory()->create(['name' => 'AlphaParent']);
        $zuluParent = Company::factory()->create(['name' => 'ZuluParent']);
        $alphaChild = Company::factory()->childOf($alphaParent)->create(['name' => 'AlphaChild']);
        $zuluChild = Company::factory()->childOf($zuluParent)->create(['name' => 'ZuluChild']);

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.companies.selectlist'))
            ->assertOk()
            ->json();

        $byId = collect($response['results'])->keyBy('id');

        // Parents render as their own name, children render with the
        // full breadcrumb path so hierarchy context is always visible.
        // Unified with Location::indenter and the info-panel breadcrumb.
        $this->assertEquals('AlphaParent', $byId[$alphaParent->id]['text']);
        $this->assertEquals('AlphaParent › AlphaChild', $byId[$alphaChild->id]['text']);
        $this->assertEquals('ZuluParent', $byId[$zuluParent->id]['text']);
        $this->assertEquals('ZuluParent › ZuluChild', $byId[$zuluChild->id]['text']);

        // Children appear directly after their own parent, not lumped at the end.
        $positions = collect($response['results'])->pluck('id')->flip();
        $this->assertLessThan($positions[$zuluParent->id], $positions[$alphaChild->id], 'Child should appear under its own parent, before the next top-level company');
    }

    public function test_selectlist_returns_flat_text_when_searching()
    {
        $parent = Company::factory()->create(['name' => 'SearchableParent']);
        $child = Company::factory()->childOf($parent)->create(['name' => 'SearchableChild']);

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.companies.selectlist', ['search' => 'Searchable']))
            ->assertOk()
            ->json();

        $byId = collect($response['results'])->keyBy('id');

        $this->assertEquals('SearchableParent', $byId[$parent->id]['text']);
        $this->assertEquals('SearchableChild', $byId[$child->id]['text'], 'Children should render flat (no indent) when searching');
    }

    public function test_selectlist_marks_children_disabled_when_only_top_level_is_set()
    {
        $top = Company::factory()->create(['name' => 'TopLevelSelect']);
        $child = Company::factory()->childOf($top)->create(['name' => 'ChildSelect']);

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.companies.selectlist', ['onlyTopLevel' => 1]))
            ->assertOk()
            ->json();

        $byId = collect($response['results'])->keyBy('id');

        $this->assertArrayNotHasKey('disabled', $byId[$top->id], 'Top-level company should not be disabled');
        $this->assertTrue($byId[$child->id]['disabled'] ?? false, 'Child company should be marked disabled');
    }

    public function test_selectlist_does_not_mark_disabled_without_only_top_level()
    {
        $top = Company::factory()->create();
        $child = Company::factory()->childOf($top)->create();

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.companies.selectlist'))
            ->assertOk()
            ->json();

        $byId = collect($response['results'])->keyBy('id');

        $this->assertArrayNotHasKey('disabled', $byId[$top->id]);
        $this->assertArrayNotHasKey('disabled', $byId[$child->id], 'Without onlyTopLevel, no rows should be disabled');
    }

    public function test_selectlist_excludes_company_passed_as_exclude_id()
    {
        $excluded = Company::factory()->create(['name' => 'ExcludedFromList']);
        $included = Company::factory()->create(['name' => 'IncludedInList']);

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.companies.selectlist', ['excludeId' => $excluded->id]))
            ->assertOk()
            ->json();

        $ids = collect($response['results'])->pluck('id')->all();

        $this->assertNotContains($excluded->id, $ids);
        $this->assertContains($included->id, $ids);
    }

    public function test_selectlist_surfaces_orphaned_child_when_parent_is_out_of_scope()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $parent = Company::factory()->create(['name' => 'HiddenParent']);
        $child = Company::factory()->childOf($parent)->create(['name' => 'VisibleChild']);

        // User in child only; parent is not in their pivot, so the selectlist
        // scopes it out — but the child should still render (as top-level, no indent).
        $userInChild = $child->users()->save(User::factory()->createAssets()->create());

        $response = $this->actingAsForApi($userInChild)
            ->getJson(route('api.companies.selectlist'))
            ->assertOk()
            ->json();

        $byId = collect($response['results'])->keyBy('id');

        $this->assertArrayNotHasKey($parent->id, $byId, 'Parent should not be visible');
        $this->assertArrayHasKey($child->id, $byId, 'Orphaned child should still appear');
        $this->assertEquals('VisibleChild', $byId[$child->id]['text'], 'Orphan should be flat (no indent) since its parent is hidden');
    }

    public function test_expand_company_hierarchy_on_assets_includes_parent_and_children()
    {
        $parent = Company::factory()->create();
        $child = Company::factory()->childOf($parent)->create();
        $unrelated = Company::factory()->create();

        $parentAsset = Asset::factory()->create(['company_id' => $parent->id]);
        $childAsset = Asset::factory()->create(['company_id' => $child->id]);
        $unrelatedAsset = Asset::factory()->create(['company_id' => $unrelated->id]);

        // Without the flag: only exact-company match
        $exact = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.assets.index', ['company_id' => $child->id]))
            ->assertOk()
            ->json();
        $exactIds = collect($exact['rows'])->pluck('id')->all();
        $this->assertContains($childAsset->id, $exactIds);
        $this->assertNotContains($parentAsset->id, $exactIds);

        // With expand_company_hierarchy=1: child page includes parent's items
        $expanded = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.assets.index', ['company_id' => $child->id, 'expand_company_hierarchy' => 1]))
            ->assertOk()
            ->json();
        $expandedIds = collect($expanded['rows'])->pluck('id')->all();
        $this->assertContains($childAsset->id, $expandedIds);
        $this->assertContains($parentAsset->id, $expandedIds, 'Parent asset should appear on the child page when hierarchy is expanded');
        $this->assertNotContains($unrelatedAsset->id, $expandedIds);
    }

    public function test_expand_company_hierarchy_on_users_includes_parent_and_children_members()
    {
        $parent = Company::factory()->create();
        $child = Company::factory()->childOf($parent)->create();

        $parentMember = $parent->users()->save(User::factory()->create());
        $childMember = $child->users()->save(User::factory()->create());

        // Without the flag: child page only shows direct child members
        $exact = $this->actingAsForApi(User::factory()->superuser()->viewUsers()->create())
            ->getJson(route('api.users.index', ['company_id' => $child->id]))
            ->assertOk()
            ->json();
        $exactIds = collect($exact['rows'])->pluck('id')->all();
        $this->assertContains($childMember->id, $exactIds);
        $this->assertNotContains($parentMember->id, $exactIds);

        // With the flag: child page also shows users inherited from the parent
        $expanded = $this->actingAsForApi(User::factory()->superuser()->viewUsers()->create())
            ->getJson(route('api.users.index', ['company_id' => $child->id, 'expand_company_hierarchy' => 1]))
            ->assertOk()
            ->json();
        $expandedIds = collect($expanded['rows'])->pluck('id')->all();
        $this->assertContains($childMember->id, $expandedIds);
        $this->assertContains($parentMember->id, $expandedIds, 'Parent member should appear on the child page when hierarchy is expanded');
    }

    public function test_asset_at_parent_can_check_out_to_location_at_child()
    {
        // Regression: Asset.canCheckoutTo(Location) used strict company_id
        // equality; without the hierarchy expansion, a parent-company asset
        // couldn't be moved to a child-company location even though the user
        // had full visibility into both.
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

        $parent = Company::factory()->create();
        $child = Company::factory()->childOf($parent)->create();

        $asset = Asset::factory()->create(['company_id' => $parent->id]);
        $location = Location::factory()->create(['company_id' => $child->id]);

        $this->assertTrue($asset->canCheckoutTo($location));
    }

    public function test_asset_at_child_can_check_out_to_location_at_parent()
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

        $parent = Company::factory()->create();
        $child = Company::factory()->childOf($parent)->create();

        $asset = Asset::factory()->create(['company_id' => $child->id]);
        $location = Location::factory()->create(['company_id' => $parent->id]);

        $this->assertTrue($asset->canCheckoutTo($location));
    }

    public function test_asset_cannot_check_out_to_location_at_unrelated_company()
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

        $parent = Company::factory()->create();
        $child = Company::factory()->childOf($parent)->create();
        $unrelated = Company::factory()->create();

        $asset = Asset::factory()->create(['company_id' => $child->id]);
        $location = Location::factory()->create(['company_id' => $unrelated->id]);

        $this->assertFalse(
            $asset->canCheckoutTo($location),
            'Strict company match should still apply for companies outside the hierarchy',
        );
    }

    public function test_asset_at_child_cannot_check_out_to_location_at_sibling_child()
    {
        // Hierarchy expansion is parent ↔ child only — sibling-to-sibling is
        // intentionally not auto-allowed (mirrors User::canReceiveFromCompany,
        // where a user in child A can't receive an item from sibling child B).
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

        $parent = Company::factory()->create();
        $childA = Company::factory()->childOf($parent)->create();
        $childB = Company::factory()->childOf($parent)->create();

        $asset = Asset::factory()->create(['company_id' => $childA->id]);
        $location = Location::factory()->create(['company_id' => $childB->id]);

        $this->assertFalse(
            $asset->canCheckoutTo($location),
            'Sibling-child to sibling-child should require the parent-company user, not auto-allow',
        );
    }

    public function test_fmcs_floater_mode_still_works_with_hierarchy()
    {
        // Floater mode: items with NULL company_id are visible to everyone.
        // A user in a parent company should still see floater items in addition
        // to their parent's items AND their child-company items.
        $this->settings->enableFloaterMode();

        $parent = Company::factory()->create();
        $child = Company::factory()->childOf($parent)->create();

        $assetInParent = Asset::factory()->create(['company_id' => $parent->id]);
        $assetInChild = Asset::factory()->create(['company_id' => $child->id]);
        $floaterAsset = Asset::factory()->create(['company_id' => null]);

        $userInParent = $parent->users()->save(User::factory()->viewAssets()->create());

        $response = $this->actingAsForApi($userInParent)
            ->getJson(route('api.assets.index'))
            ->assertOk()
            ->json();

        $foundIds = collect($response['rows'])->pluck('id')->all();

        $this->assertContains($assetInParent->id, $foundIds, 'Parent asset should be visible');
        $this->assertContains($assetInChild->id, $foundIds, 'Child asset should be visible via hierarchy');
        $this->assertContains($floaterAsset->id, $foundIds, 'Floater asset should be visible via null_company_is_floater');
    }
}
