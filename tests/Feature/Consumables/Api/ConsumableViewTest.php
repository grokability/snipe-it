<?php

use App\Models\Company;
use App\Models\Consumable;
use App\Models\User;

test('consumable view adheres to company scoping', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $consumableA = Consumable::factory()->for($companyA)->create();
    $consumableB = Consumable::factory()->for($companyB)->create();

    $superUser = $companyA->users()->save(User::factory()->superuser()->make());
    $userInCompanyA = $companyA->users()->save(User::factory()->viewConsumables()->make());
    $userInCompanyB = $companyB->users()->save(User::factory()->viewConsumables()->make());

    $this->settings->disableMultipleFullCompanySupport();

    $this->actingAsForApi($superUser)
        ->getJson(route('api.consumables.show', $consumableA))
        ->assertOk();

    $this->actingAsForApi($userInCompanyA)
        ->getJson(route('api.consumables.show', $consumableA))
        ->assertOk();

    $this->actingAsForApi($userInCompanyB)
        ->getJson(route('api.consumables.show', $consumableB))
        ->assertOk();

    $this->settings->enableMultipleFullCompanySupport();

    $this->actingAsForApi($superUser)
        ->getJson(route('api.consumables.show', $consumableA))
        ->assertOk();

    $this->actingAsForApi($userInCompanyA)
        ->getJson(route('api.consumables.index'))
        ->assertOk();

    $this->actingAsForApi($userInCompanyB)
        ->getJson(route('api.consumables.index'))
        ->assertOk();
});
