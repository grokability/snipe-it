<?php

use App\Models\Accessory;
use App\Models\Company;
use App\Models\User;

test('requires permission', function () {
    $accessory = Accessory::factory()->create();

    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.accessories.checkedout', $accessory))
        ->assertForbidden();
});

test('adheres to full multiple companies support scoping', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $accessoryA = Accessory::factory()->for($companyA)->create();
    $accessoryB = Accessory::factory()->for($companyB)->create();

    $superuser = User::factory()->superuser()->create();
    $userInCompanyA = $companyA->users()->save(User::factory()->viewAccessories()->make());
    $userInCompanyB = $companyB->users()->save(User::factory()->viewAccessories()->make());

    $this->settings->enableMultipleFullCompanySupport();

    $this->actingAsForApi($userInCompanyA)
        ->getJson(route('api.accessories.checkedout', $accessoryB))
        ->assertStatusMessageIs('error');

    $this->actingAsForApi($userInCompanyB)
        ->getJson(route('api.accessories.checkedout', $accessoryA))
        ->assertStatusMessageIs('error');

    $this->actingAsForApi($superuser)
        ->getJson(route('api.accessories.checkedout', $accessoryA))
        ->assertOk();
});

test('can get accessory checkouts', function () {
    [$userA, $userB] = User::factory()->count(2)->create();

    $accessory = Accessory::factory()->checkedOutToUsers([$userA, $userB])->create();

    expect($accessory->checkouts()->count())->toEqual(2);

    $this->actingAsForApi(User::factory()->viewAccessories()->create())
        ->getJson(route('api.accessories.checkedout', $accessory))
        ->assertOk()
        ->assertJsonPath('total', 2)
        ->assertJsonPath('rows.0.assigned_to.id', $userA->id)
        ->assertJsonPath('rows.1.assigned_to.id', $userB->id);
});

test('can get accessory checkouts with offset and limit in query string', function () {
    [$userA, $userB, $userC] = User::factory()->count(3)->create();

    $accessory = Accessory::factory()->checkedOutToUsers([$userA, $userB, $userC])->create();

    $actor = $this->actingAsForApi(User::factory()->viewAccessories()->create());

    $actor->getJson(route('api.accessories.checkedout', ['accessory' => $accessory->id, 'limit' => 1]))
        ->assertOk()
        ->assertJsonPath('total', 3)
        ->assertJsonPath('rows.0.assigned_to.id', $userA->id);

    $actor->getJson(route('api.accessories.checkedout', ['accessory' => $accessory->id, 'limit' => 2, 'offset' => 1]))
        ->assertOk()
        ->assertJsonPath('total', 3)
        ->assertJsonPath('rows.0.assigned_to.id', $userB->id)
        ->assertJsonPath('rows.1.assigned_to.id', $userC->id);
});

test('checkout search by company name returns matching users', function () {
    $company = Company::factory()->create(['name' => 'Jedi Order']);
    $jedi = User::factory()->create();
    $company->users()->attach($jedi);
    $sith = User::factory()->create();

    $accessory = Accessory::factory()->checkedOutToUsers([$jedi, $sith])->create();

    $this->actingAsForApi(User::factory()->viewAccessories()->create())
        ->getJson(route('api.accessories.checkedout', ['accessory' => $accessory->id, 'search' => 'Jedi Order']))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('rows.0.assigned_to.id', $jedi->id);
});

test('checkout search by company name does not return users in other companies', function () {
    Company::factory()->create(['name' => 'Jedi Order']);
    $sith = User::factory()->create();

    $accessory = Accessory::factory()->checkedOutToUsers([$sith])->create();

    $this->actingAsForApi(User::factory()->viewAccessories()->create())
        ->getJson(route('api.accessories.checkedout', ['accessory' => $accessory->id, 'search' => 'Jedi Order']))
        ->assertOk()
        ->assertJsonPath('total', 0);
});

test('does not leak assignee pii when caller lacks users view', function () {
    $assignee = User::factory()->create([
        'first_name' => 'Hidden',
        'last_name' => 'Assignee',
        'username' => 'hidden-assignee',
    ]);
    $accessory = Accessory::factory()->checkedOutToUsers([$assignee])->create();

    // viewAccessories only. No viewUsers.
    $actor = User::factory()->viewAccessories()->create();

    $response = $this->actingAsForApi($actor)
        ->getJson(route('api.accessories.checkedout', $accessory))
        ->assertOk()
        ->assertJsonPath('rows.0.assigned_to.id', $assignee->id)
        ->assertJsonPath('rows.0.assigned_to.type', 'user')
        // Display name IS present in the denied fallback - basic identity is fine.
        ->assertJsonPath('rows.0.assigned_to.name', $assignee->display_name);

    $assigned = $response->json('rows.0.assigned_to');

    // The keys that actually get stripped by the denied fallback.
    $this->assertArrayNotHasKey('username', $assigned, 'Login handle must not leak to a caller without users.view');
    $this->assertArrayNotHasKey('companies', $assigned, 'Company relationships must not leak');
    $this->assertArrayNotHasKey('image', $assigned, 'Avatar URL must not leak');
    $this->assertArrayNotHasKey('created_by', $assigned);
    $this->assertArrayNotHasKey('created_at', $assigned);
});

test('returns full assignee identity when caller has users view', function () {
    $assignee = User::factory()->create([
        'first_name' => 'Visible',
        'last_name' => 'Assignee',
        'username' => 'visible-assignee',
    ]);
    $accessory = Accessory::factory()->checkedOutToUsers([$assignee])->create();

    $actor = User::factory()->viewAccessories()->viewUsers()->create();

    $this->actingAsForApi($actor)
        ->getJson(route('api.accessories.checkedout', $accessory))
        ->assertOk()
        ->assertJsonPath('rows.0.assigned_to.id', $assignee->id)
        ->assertJsonPath('rows.0.assigned_to.first_name', 'Visible')
        ->assertJsonPath('rows.0.assigned_to.username', 'visible-assignee');
});
