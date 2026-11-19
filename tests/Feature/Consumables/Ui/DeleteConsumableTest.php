<?php

use App\Models\Company;
use App\Models\Consumable;
use App\Models\User;

test('requires permission to delete consumable', function () {
    $this->actingAs(User::factory()->create())
        ->delete(route('consumables.destroy', Consumable::factory()->create()->id))
        ->assertForbidden();
});

test('cannot delete consumable from another company', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $consumableForCompanyA = Consumable::factory()->for($companyA)->create();
    $userForCompanyB = User::factory()->deleteConsumables()->forCompany($companyB)->create();

    $this->actingAs($userForCompanyB)
        ->delete(route('consumables.destroy', $consumableForCompanyA->id))
        ->assertRedirect(route('consumables.index'));

    $this->assertNotSoftDeleted($consumableForCompanyA);
});

test('can delete consumable', function () {
    $consumable = Consumable::factory()->create();

    $this->actingAs(User::factory()->deleteConsumables()->create())
        ->delete(route('consumables.destroy', $consumable->id))
        ->assertRedirect(route('consumables.index'));

    $this->assertSoftDeleted($consumable);
});
