<?php

use App\Models\Accessory;
use App\Models\Company;
use App\Models\User;

test('requires permission', function () {
    $accessory = Accessory::factory()->create();

    $this->actingAsForApi(User::factory()->create())
        ->deleteJson(route('api.accessories.destroy', $accessory))
        ->assertForbidden();

    $this->assertNotSoftDeleted($accessory);
});

test('adheres to full multiple companies support scoping', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $accessoryA = Accessory::factory()->for($companyA)->create();
    $accessoryB = Accessory::factory()->for($companyB)->create();
    $accessoryC = Accessory::factory()->for($companyB)->create();

    $superUser = $companyA->users()->save(User::factory()->superuser()->make());
    $userInCompanyA = $companyA->users()->save(User::factory()->deleteAccessories()->make());
    $userInCompanyB = $companyB->users()->save(User::factory()->deleteAccessories()->make());

    $this->settings->enableMultipleFullCompanySupport();

    $this->actingAsForApi($userInCompanyA)
        ->deleteJson(route('api.accessories.destroy', $accessoryB))
        ->assertStatusMessageIs('error');

    $this->actingAsForApi($userInCompanyB)
        ->deleteJson(route('api.accessories.destroy', $accessoryA))
        ->assertStatusMessageIs('error');

    $this->actingAsForApi($superUser)
        ->deleteJson(route('api.accessories.destroy', $accessoryC))
        ->assertStatusMessageIs('success');

    $this->assertNotSoftDeleted($accessoryA);
    $this->assertNotSoftDeleted($accessoryB);
    $this->assertSoftDeleted($accessoryC);
});

test('cannot delete accessory that has checkouts', function (Accessory $accessory) {
    $this->actingAsForApi(User::factory()->deleteAccessories()->create())
        ->deleteJson(route('api.accessories.destroy', $accessory))
        ->assertStatusMessageIs('error');

    $this->assertNotSoftDeleted($accessory);
})->with('checked out accessories');

test('can delete accessory', function () {
    $accessory = Accessory::factory()->create();

    $this->actingAsForApi(User::factory()->deleteAccessories()->create())
        ->deleteJson(route('api.accessories.destroy', $accessory))
        ->assertStatusMessageIs('success');

    $this->assertSoftDeleted($accessory);
});
