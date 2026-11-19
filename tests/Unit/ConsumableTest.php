<?php

use App\Models\Company;
use App\Models\Consumable;
use App\Models\User;

test('percent remaining returns one hundred when nothing is checked out', function () {
    $consumable = new Consumable([
        'qty' => 25,
    ]);
    $consumable->consumables_users_count = 0;

    expect($consumable->percentRemaining())->toEqual(100);
});

test('percent remaining returns expected value when partially checked out', function () {
    $consumable = new Consumable([
        'qty' => 20,
    ]);
    $consumable->consumables_users_count = 5;

    expect($consumable->percentRemaining())->toEqual(75.0);
});

test('percent remaining can go negative when checked out exceeds quantity', function () {
    $consumable = new Consumable([
        'qty' => 3,
    ]);
    $consumable->consumables_users_count = 5;

    expect($consumable->percentRemaining())->toEqualWithDelta(-66.66666666666667, 0.0000000001);
});

test('num checked out does not scope by company', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $userInCompanyB = User::factory()->forCompany($companyB)->create();

    $consumableForCompanyA = Consumable::factory()
        ->for($companyA)
        // this can happen if FMCS was toggled on after checkouts
        ->checkedOutToUser($userInCompanyB)
        ->checkedOutToUser($userInCompanyB)
        ->create(['qty' => 3]);

    $this->actingAs(User::factory()->superuser()->create());
    expect($consumableForCompanyA->fresh()->numCheckedOut())->toEqual(2);

    $this->actingAs(User::factory()->admin()->forCompany($companyA)->create());
    expect($consumableForCompanyA->fresh()->numCheckedOut())->toEqual(2);

    $this->actingAs(User::factory()->forCompany($companyA)->create());
    expect($consumableForCompanyA->fresh()->numCheckedOut())->toEqual(2);
});
