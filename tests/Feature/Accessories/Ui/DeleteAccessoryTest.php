<?php

use App\Models\Accessory;
use App\Models\Company;
use App\Models\User;

test('requires permission to delete accessory', function () {
    $this->actingAs(User::factory()->create())
        ->delete(route('accessories.destroy', Accessory::factory()->create()->id))
        ->assertForbidden();
});

test('cannot delete accessory from another company', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $accessoryForCompanyA = Accessory::factory()->for($companyA)->create();
    $userForCompanyB = User::factory()->forCompany($companyB)->deleteAccessories()->create();

    $this->actingAs($userForCompanyB)->delete(route('accessories.destroy', $accessoryForCompanyA->id));

    expect($accessoryForCompanyA->refresh()->trashed())->toBeFalse('Accessory should not be deleted');
});

test('cannot delete accessory that has checkouts', function (Accessory $accessory) {
    $this->actingAs(User::factory()->deleteAccessories()->create())
        ->delete(route('accessories.destroy', $accessory->id))
        ->assertSessionHas('error')
        ->assertRedirect(route('accessories.index'));

    expect($accessory->refresh()->trashed())->toBeFalse('Accessory should not be deleted');
})->with('checked out accessories');

test('can delete accessory', function () {
    $accessory = Accessory::factory()->create();

    $this->actingAs(User::factory()->deleteAccessories()->create())
        ->delete(route('accessories.destroy', $accessory->id))
        ->assertRedirect(route('accessories.index'));

    expect($accessory->refresh()->trashed())->toBeTrue('Accessory should be deleted');
});
