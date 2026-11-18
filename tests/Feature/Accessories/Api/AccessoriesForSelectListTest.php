<?php

use App\Models\Accessory;
use App\Models\Company;
use App\Models\User;

test('requires view selectlists permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.accessories.selectlist'))
        ->assertForbidden();
});

test('adheres to full multiple companies support scoping', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $accessoryA = Accessory::factory()->for($companyA)->create();
    $accessoryB = Accessory::factory()->for($companyB)->create();

    $superuser = User::factory()->superuser()->create();
    $userInCompanyA = $companyA->users()->save(User::factory()->editAccessories()->make());
    $userInCompanyB = $companyB->users()->save(User::factory()->editAccessories()->make());

    $this->settings->enableMultipleFullCompanySupport();

    $this->actingAsForApi($userInCompanyA)
        ->getJson(route('api.accessories.selectlist'))
        ->assertOk()
        ->assertJsonPath('total_count', 1)
        ->assertResponseContainsInResults($accessoryA)
        ->assertResponseDoesNotContainInResults($accessoryB);

    $this->actingAsForApi($userInCompanyB)
        ->getJson(route('api.accessories.selectlist'))
        ->assertOk()
        ->assertJsonPath('total_count', 1)
        ->assertResponseDoesNotContainInResults($accessoryA)
        ->assertResponseContainsInResults($accessoryB);

    $this->actingAsForApi($superuser)
        ->getJson(route('api.accessories.selectlist'))
        ->assertOk()
        ->assertJsonPath('total_count', 2)
        ->assertResponseContainsInResults($accessoryA)
        ->assertResponseContainsInResults($accessoryB);
});

test('can get accessories for select list', function () {
    [$accessoryA, $accessoryB] = Accessory::factory()->count(2)->create();

    $this->actingAsForApi(User::factory()->editAccessories()->create())
        ->getJson(route('api.accessories.selectlist'))
        ->assertOk()
        ->assertJsonPath('total_count', 2)
        ->assertResponseContainsInResults($accessoryA)
        ->assertResponseContainsInResults($accessoryB);
});
