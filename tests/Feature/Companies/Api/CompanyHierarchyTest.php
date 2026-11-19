<?php

use App\Models\Asset;
use App\Models\Company;
use App\Models\Location;
use App\Models\User;

test('can create company with parent id', function () {
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
});

test('can update company to set parent id', function () {
    $parent = Company::factory()->create();
    $orphan = Company::factory()->create();

    $this->actingAsForApi(User::factory()->editCompanies()->create())
        ->patchJson(route('api.companies.update', ['company' => $orphan->id]), [
            'name' => $orphan->name,
            'parent_id' => $parent->id,
        ])
        ->assertStatus(200)
        ->assertStatusMessageIs('success');

    expect($orphan->fresh()->parent_id)->toEqual($parent->id);
});

test('company cannot be its own parent', function () {
    $company = Company::factory()->create();

    $this->actingAsForApi(User::factory()->editCompanies()->create())
        ->patchJson(route('api.companies.update', ['company' => $company->id]), [
            'name' => $company->name,
            'parent_id' => $company->id,
        ])
        ->assertStatus(200)
        ->assertStatusMessageIs('error')
        ->assertJsonStructure(['messages' => ['parent_id']]);

    expect($company->fresh()->parent_id)->toBeNull();
});

test('cannot create grandchild company', function () {
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
});

test('validation message for grandchild attempt mentions top level', function () {
    $grandparent = Company::factory()->create();
    $parent = Company::factory()->childOf($grandparent)->create();

    $response = $this->actingAsForApi(User::factory()->createCompanies()->create())
        ->postJson(route('api.companies.store'), [
            'name' => 'GreatGrandchildMessageCheck',
            'parent_id' => $parent->id,
        ])
        ->assertStatus(200)
        ->json();

    expect($response['messages']['parent_id'])->not->toBeEmpty();
    $message = implode(' ', (array) $response['messages']['parent_id']);
    $this->assertStringContainsString('top-level', $message);
    $this->assertStringNotContainsString('children of its own', $message, 'Grandchild attempt should not show the "row has children" message');
});

test('validation message for demoting parent mentions children', function () {
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

    expect($response['messages']['parent_id'])->not->toBeEmpty();
    $message = implode(' ', (array) $response['messages']['parent_id']);
    $this->assertStringContainsString('children of its own', $message);
    $this->assertStringNotContainsString('top-level', $message, 'Demote-with-children attempt should not show the "parent must be top-level" message');
});

test('cannot assign parent to company that has children', function () {
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

    expect($parent->fresh()->parent_id)->toBeNull();
});

test('can clear parent id by passing null', function () {
    $parent = Company::factory()->create();
    $child = Company::factory()->childOf($parent)->create();

    $this->actingAsForApi(User::factory()->editCompanies()->create())
        ->patchJson(route('api.companies.update', ['company' => $child->id]), [
            'name' => $child->name,
            'parent_id' => null,
        ])
        ->assertStatus(200)
        ->assertStatusMessageIs('success');

    expect($child->fresh()->parent_id)->toBeNull();
});

test('parent id of zero is stored as null', function () {
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
});

test('parent id of empty string is stored as null', function () {
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
});

test('parent id must reference existing company', function () {
    $this->actingAsForApi(User::factory()->createCompanies()->create())
        ->postJson(route('api.companies.store'), [
            'name' => 'Orphaned',
            'parent_id' => 999999,
        ])
        ->assertStatus(200)
        ->assertStatusMessageIs('error')
        ->assertJsonStructure(['messages' => ['parent_id']]);
});

test('cannot delete company that has children', function () {
    $parent = Company::factory()->create();
    Company::factory()->childOf($parent)->create();

    $this->actingAsForApi(User::factory()->deleteCompanies()->create())
        ->deleteJson(route('api.companies.destroy', $parent))
        ->assertStatusMessageIs('error');

    $this->assertDatabaseHas('companies', ['id' => $parent->id, 'deleted_at' => null]);
});

test('index exposes parent and children count', function () {
    $parent = Company::factory()->create();
    Company::factory()->count(2)->childOf($parent)->create();

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.companies.index', ['search' => $parent->name]))
        ->assertOk()
        ->json();

    $parentRow = collect($response['rows'])->firstWhere('id', $parent->id);

    expect($parentRow)->not->toBeNull();
    expect($parentRow['parent'])->toBeNull();
    expect($parentRow['children_count'])->toEqual(2);
});

test('index loads for fmcs scoped non superuser', function () {
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
});

test('index can sort by parent company name', function () {
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
    expect($alphaChildPos)->toBeLessThan($zuluChildPos, 'AlphaParent child should sort before ZuluParent child when sorting by parent name asc');
});

test('show exposes parent block for child company', function () {
    $parent = Company::factory()->create();
    $child = Company::factory()->childOf($parent)->create();

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.companies.show', $child))
        ->assertOk()
        ->json();

    expect($response['parent']['id'])->toEqual($parent->id);

    // Transformer emits e($parent->name) — mirror that on the LHS so a
    // faker name containing an apostrophe or other special char round-trips.
    expect($response['parent']['name'])->toEqual(e($parent->name));
});

test('user in parent company has fmcs access to child company assets', function () {
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

    expect($foundIds)->toContain($assetInChild->id, 'User in parent should see child-company assets');
});

test('user in child company does not see parent company assets', function () {
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

    expect($foundIds)->not->toContain($assetInParent->id, 'User in child should not see parent-company assets');
});

test('user in parent can filter companies selectlist and see children', function () {
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

    expect($ids)->toContain($parent->id);
    expect($ids)->toContain($child->id);
    expect($ids)->not->toContain($unrelated->id);
});

test('selectlist groups children under their parent with indented text', function () {
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
    expect($byId[$alphaParent->id]['text'])->toEqual('AlphaParent');
    expect($byId[$alphaChild->id]['text'])->toEqual('AlphaParent › AlphaChild');
    expect($byId[$zuluParent->id]['text'])->toEqual('ZuluParent');
    expect($byId[$zuluChild->id]['text'])->toEqual('ZuluParent › ZuluChild');

    // Children appear directly after their own parent, not lumped at the end.
    $positions = collect($response['results'])->pluck('id')->flip();
    expect($positions[$alphaChild->id])->toBeLessThan($positions[$zuluParent->id], 'Child should appear under its own parent, before the next top-level company');
});

test('selectlist returns flat text when searching', function () {
    $parent = Company::factory()->create(['name' => 'SearchableParent']);
    $child = Company::factory()->childOf($parent)->create(['name' => 'SearchableChild']);

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.companies.selectlist', ['search' => 'Searchable']))
        ->assertOk()
        ->json();

    $byId = collect($response['results'])->keyBy('id');

    expect($byId[$parent->id]['text'])->toEqual('SearchableParent');
    expect($byId[$child->id]['text'])->toEqual('SearchableChild', 'Children should render flat (no indent) when searching');
});

test('selectlist marks children disabled when only top level is set', function () {
    $top = Company::factory()->create(['name' => 'TopLevelSelect']);
    $child = Company::factory()->childOf($top)->create(['name' => 'ChildSelect']);

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.companies.selectlist', ['onlyTopLevel' => 1]))
        ->assertOk()
        ->json();

    $byId = collect($response['results'])->keyBy('id');

    $this->assertArrayNotHasKey('disabled', $byId[$top->id], 'Top-level company should not be disabled');
    expect($byId[$child->id]['disabled'] ?? false)->toBeTrue('Child company should be marked disabled');
});

test('selectlist does not mark disabled without only top level', function () {
    $top = Company::factory()->create();
    $child = Company::factory()->childOf($top)->create();

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.companies.selectlist'))
        ->assertOk()
        ->json();

    $byId = collect($response['results'])->keyBy('id');

    $this->assertArrayNotHasKey('disabled', $byId[$top->id]);
    $this->assertArrayNotHasKey('disabled', $byId[$child->id], 'Without onlyTopLevel, no rows should be disabled');
});

test('selectlist excludes company passed as exclude id', function () {
    $excluded = Company::factory()->create(['name' => 'ExcludedFromList']);
    $included = Company::factory()->create(['name' => 'IncludedInList']);

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.companies.selectlist', ['excludeId' => $excluded->id]))
        ->assertOk()
        ->json();

    $ids = collect($response['results'])->pluck('id')->all();

    expect($ids)->not->toContain($excluded->id);
    expect($ids)->toContain($included->id);
});

test('selectlist surfaces orphaned child when parent is out of scope', function () {
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
    expect($byId)->toHaveKey($child->id, 'Orphaned child should still appear');
    expect($byId[$child->id]['text'])->toEqual('VisibleChild', 'Orphan should be flat (no indent) since its parent is hidden');
});

test('expand company hierarchy on assets includes parent and children', function () {
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
    expect($exactIds)->toContain($childAsset->id);
    expect($exactIds)->not->toContain($parentAsset->id);

    // With expand_company_hierarchy=1: child page includes parent's items
    $expanded = $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.assets.index', ['company_id' => $child->id, 'expand_company_hierarchy' => 1]))
        ->assertOk()
        ->json();
    $expandedIds = collect($expanded['rows'])->pluck('id')->all();
    expect($expandedIds)->toContain($childAsset->id);
    expect($expandedIds)->toContain($parentAsset->id, 'Parent asset should appear on the child page when hierarchy is expanded');
    expect($expandedIds)->not->toContain($unrelatedAsset->id);
});

test('expand company hierarchy on users includes parent and children members', function () {
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
    expect($exactIds)->toContain($childMember->id);
    expect($exactIds)->not->toContain($parentMember->id);

    // With the flag: child page also shows users inherited from the parent
    $expanded = $this->actingAsForApi(User::factory()->superuser()->viewUsers()->create())
        ->getJson(route('api.users.index', ['company_id' => $child->id, 'expand_company_hierarchy' => 1]))
        ->assertOk()
        ->json();
    $expandedIds = collect($expanded['rows'])->pluck('id')->all();
    expect($expandedIds)->toContain($childMember->id);
    expect($expandedIds)->toContain($parentMember->id, 'Parent member should appear on the child page when hierarchy is expanded');
});

test('asset at parent can check out to location at child', function () {
    // Regression: Asset.canCheckoutTo(Location) used strict company_id
    // equality; without the hierarchy expansion, a parent-company asset
    // couldn't be moved to a child-company location even though the user
    // had full visibility into both.
    $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

    $parent = Company::factory()->create();
    $child = Company::factory()->childOf($parent)->create();

    $asset = Asset::factory()->create(['company_id' => $parent->id]);
    $location = Location::factory()->create(['company_id' => $child->id]);

    expect($asset->canCheckoutTo($location))->toBeTrue();
});

test('asset at child can check out to location at parent', function () {
    $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

    $parent = Company::factory()->create();
    $child = Company::factory()->childOf($parent)->create();

    $asset = Asset::factory()->create(['company_id' => $child->id]);
    $location = Location::factory()->create(['company_id' => $parent->id]);

    expect($asset->canCheckoutTo($location))->toBeTrue();
});

test('asset cannot check out to location at unrelated company', function () {
    $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

    $parent = Company::factory()->create();
    $child = Company::factory()->childOf($parent)->create();
    $unrelated = Company::factory()->create();

    $asset = Asset::factory()->create(['company_id' => $child->id]);
    $location = Location::factory()->create(['company_id' => $unrelated->id]);

    expect($asset->canCheckoutTo($location))->toBeFalse('Strict company match should still apply for companies outside the hierarchy');
});

test('asset at child cannot check out to location at sibling child', function () {
    // Hierarchy expansion is parent ↔ child only — sibling-to-sibling is
    // intentionally not auto-allowed (mirrors User::canReceiveFromCompany,
    // where a user in child A can't receive an item from sibling child B).
    $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

    $parent = Company::factory()->create();
    $childA = Company::factory()->childOf($parent)->create();
    $childB = Company::factory()->childOf($parent)->create();

    $asset = Asset::factory()->create(['company_id' => $childA->id]);
    $location = Location::factory()->create(['company_id' => $childB->id]);

    expect($asset->canCheckoutTo($location))->toBeFalse('Sibling-child to sibling-child should require the parent-company user, not auto-allow');
});

test('fmcs floater mode still works with hierarchy', function () {
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

    expect($foundIds)->toContain($assetInParent->id, 'Parent asset should be visible');
    expect($foundIds)->toContain($assetInChild->id, 'Child asset should be visible via hierarchy');
    expect($foundIds)->toContain($floaterAsset->id, 'Floater asset should be visible via null_company_is_floater');
});

test('scoped actor cannot reparent own company under a foreign top level company', function () {
    // Under FMCS, Company::getCurrentUserCompanyIds() walks parent+
    // children. Re-parenting Company A under Company B would silently
    // expose every Company A resource to every Company B member. The
    // structural parent_must_be_top_level + must_have_no_children
    // rules accept the write because they only look at the parent's
    // shape, not the caller's scope on it. The parent_within_scope
    // rule closes that gap.
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $actor = $companyA->users()->save(User::factory()->editCompanies()->make());

    $response = $this->actingAsForApi($actor)
        ->patchJson(route('api.companies.update', ['company' => $companyA->id]), [
            'name' => $companyA->name,
            'parent_id' => $companyB->id,
        ])
        ->assertStatus(200)
        ->assertStatusMessageIs('error')
        ->assertJsonStructure(['messages' => ['parent_id']]);

    expect($companyA->fresh()->parent_id)->toBeNull('Company A must not be re-parented under a company outside the actor\'s scope.');

    $message = implode(' ', (array) $response->json('messages.parent_id'));
    $this->assertStringContainsString('outside your assigned scope', $message);
});

test('scoped actor cannot create child of foreign company', function () {
    // Sibling of the update case for the create path. Same scope
    // gap, same rule.
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $actor = $companyA->users()->save(User::factory()->createCompanies()->make());

    $this->actingAsForApi($actor)
        ->postJson(route('api.companies.store'), [
            'name' => 'Attempted-Child-Of-B',
            'parent_id' => $companyB->id,
        ])
        ->assertStatus(200)
        ->assertStatusMessageIs('error')
        ->assertJsonStructure(['messages' => ['parent_id']]);

    $this->assertDatabaseMissing('companies', ['name' => 'Attempted-Child-Of-B']);
});

test('scoped actor can reparent within their own scope', function () {
    // Happy path: user in Company A can re-parent one of their own
    // companies (an orphan they also belong to) under another of
    // their companies. Guards against a fix that would reject every
    // reparent from a non-superuser.
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $actor = User::factory()->editCompanies()->create();
    $actor->companies()->sync([$companyA->id, $companyB->id]);

    $orphan = $companyA;
    $newParent = $companyB;

    $this->actingAsForApi($actor)
        ->patchJson(route('api.companies.update', ['company' => $orphan->id]), [
            'name' => $orphan->name,
            'parent_id' => $newParent->id,
        ])
        ->assertStatus(200)
        ->assertStatusMessageIs('success');

    expect($orphan->fresh()->parent_id)->toBe($newParent->id);
});

test('superuser can reparent across any companies under fmcs', function () {
    // Regression guard for the fix. Superusers bypass FMCS scope
    // entirely, so they must still be able to re-parent any company
    // under any other, matching pre-fix behavior.
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->patchJson(route('api.companies.update', ['company' => $companyA->id]), [
            'name' => $companyA->name,
            'parent_id' => $companyB->id,
        ])
        ->assertStatus(200)
        ->assertStatusMessageIs('success');

    expect($companyA->fresh()->parent_id)->toBe($companyB->id);
});

test('scoped actor can clear parent id on their own company', function () {
    // Sending parent_id = null (or 0, per the model mutator) never
    // exposes new scope so it must pass regardless of what the
    // parent used to be. Guards against a fix that would insist on
    // scope on the CURRENT parent instead of the NEW one.
    $this->settings->enableMultipleFullCompanySupport();

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    $child = Company::factory()->create(['parent_id' => $companyB->id]);

    $actor = $companyA->users()->save(User::factory()->editCompanies()->make());
    $actor->companies()->attach($child->id);

    $this->actingAsForApi($actor)
        ->patchJson(route('api.companies.update', ['company' => $child->id]), [
            'name' => $child->name,
            'parent_id' => null,
        ])
        ->assertStatus(200)
        ->assertStatusMessageIs('success');

    expect($child->fresh()->parent_id)->toBeNull();
});

test('soft deleted child does not block parent deletion', function () {
    // isDeletable() calls Gate::allows('delete', ...) so we need an
    // authenticated superuser for the gate to pass through to the
    // children-count check the regression pins.
    $this->actingAs(User::factory()->superuser()->create());

    $parent = Company::factory()->create();
    $child = Company::factory()->create(['parent_id' => $parent->id]);

    expect($parent->fresh()->isDeletable())->toBeFalse('Parent with an active child should not be deletable.');

    $child->delete();

    expect($parent->fresh()->children()->count())->toBe(0, 'Soft-deleted children must not show up in ->children().');
    expect($parent->fresh()->isDeletable())->toBeTrue('Parent should be deletable once its only child is soft-deleted.');
});

test('soft deleted parent is not returned on children parent relation', function () {
    $parent = Company::factory()->create();
    $child = Company::factory()->create(['parent_id' => $parent->id]);

    $parent->delete();

    expect($child->fresh()->parent)->toBeNull('A soft-deleted parent should not surface via ->parent on its child.');
});
