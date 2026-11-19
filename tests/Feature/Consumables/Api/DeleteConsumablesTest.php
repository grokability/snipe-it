<?php

use App\Models\Company;
use App\Models\Consumable;
use App\Models\User;

test('requires permission', function () {
    $consumable = Consumable::factory()->create();

    $this->actingAsForApi(User::factory()->create())
        ->deleteJson(route('api.consumables.destroy', $consumable))
        ->assertForbidden();

    $this->assertNotSoftDeleted($consumable);
});

test('adheres to full multiple companies support scoping', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $consumableA = Consumable::factory()->for($companyA)->create();
    $consumableB = Consumable::factory()->for($companyB)->create();
    $consumableC = Consumable::factory()->for($companyB)->create();

    $superUser = $companyA->users()->save(User::factory()->superuser()->make());
    $userInCompanyA = $companyA->users()->save(User::factory()->deleteConsumables()->make());
    $userInCompanyB = $companyB->users()->save(User::factory()->deleteConsumables()->make());

    $this->settings->enableMultipleFullCompanySupport();

    $this->actingAsForApi($userInCompanyA)
        ->deleteJson(route('api.consumables.destroy', $consumableB))
        ->assertStatusMessageIs('error');

    $this->actingAsForApi($userInCompanyB)
        ->deleteJson(route('api.consumables.destroy', $consumableA))
        ->assertStatusMessageIs('error');

    $this->actingAsForApi($superUser)
        ->deleteJson(route('api.consumables.destroy', $consumableC))
        ->assertStatusMessageIs('success');

    $this->assertNotSoftDeleted($consumableA);
    $this->assertNotSoftDeleted($consumableB);
    $this->assertSoftDeleted($consumableC);
});

test('can delete consumables', function () {
    $consumable = Consumable::factory()->create();

    $this->actingAsForApi(User::factory()->deleteConsumables()->create())
        ->deleteJson(route('api.consumables.destroy', $consumable))
        ->assertStatusMessageIs('success');

    $this->assertSoftDeleted($consumable);
});
