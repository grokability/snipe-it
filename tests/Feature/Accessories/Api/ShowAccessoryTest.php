<?php

use App\Models\Accessory;
use App\Models\Company;
use App\Models\User;

test('requires permission', function () {
    $accessory = Accessory::factory()->create();

    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.accessories.show', $accessory))
        ->assertForbidden();
});

test('adheres to full multiple companies support scoping', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $accessoryForCompanyA = Accessory::factory()->for($companyA)->create();

    $superuser = User::factory()->superuser()->create();
    $userForCompanyB = User::factory()->forCompany($companyB)->viewAccessories()->create();

    $this->settings->enableMultipleFullCompanySupport();

    $this->actingAsForApi($userForCompanyB)
        ->getJson(route('api.accessories.show', $accessoryForCompanyA))
        ->assertOk()
        ->assertStatusMessageIs('error');

    $this->actingAsForApi($superuser)
        ->getJson(route('api.accessories.show', $accessoryForCompanyA))
        ->assertOk()
        ->assertJsonFragment([
            'id' => $accessoryForCompanyA->id,
        ]);
});

test('can get single accessory', function () {
    $accessory = Accessory::factory()->checkedOutToUser()->create(['name' => 'My Accessory']);

    $this->actingAsForApi(User::factory()->viewAccessories()->create())
        ->getJson(route('api.accessories.show', $accessory))
        ->assertOk()
        ->assertJsonFragment([
            'id' => $accessory->id,
            'name' => 'My Accessory',
            'checkouts_count' => 1,
        ]);
});
