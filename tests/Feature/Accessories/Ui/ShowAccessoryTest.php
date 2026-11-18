<?php

use App\Models\Accessory;
use App\Models\Company;
use App\Models\User;

test('requires permission to view accessory', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('accessories.show', Accessory::factory()->create()))
        ->assertForbidden();
});

test('cannot view accessory from another company', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $accessoryForCompanyA = Accessory::factory()->for($companyA)->create();
    $userForCompanyB = User::factory()->forCompany($companyB)->viewAccessories()->create();

    $this->actingAs($userForCompanyB)
        ->get(route('accessories.show', $accessoryForCompanyA))
        ->assertStatus(302);
});

test('can view accessory', function () {
    $accessory = Accessory::factory()->create();

    $this->actingAs(User::factory()->viewAccessories()->create())
        ->get(route('accessories.show', $accessory))
        ->assertOk()
        ->assertViewIs('accessories.view')
        ->assertViewHas(['accessory' => $accessory]);
});

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('accessories.show', Accessory::factory()->create()))
        ->assertOk();
});

test('handles accessory creator not existing', function () {
    $accessory = Accessory::factory()->create(['created_by' => 999999]);

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('accessories.show', $accessory))
        ->assertOk();
});
