<?php

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Location;
use App\Models\User;

test('checkout to user in same company succeeds with fmcs enabled', function () {
    [$companyA] = Company::factory()->count(1)->create();
    $accessory = Accessory::factory()->for($companyA)->create(['qty' => 5]);
    $user = User::factory()->forCompany($companyA)->create();
    $user->companies()->sync([$companyA->id]);

    $this->settings->enableMultipleFullCompanySupport();

    $actor = User::factory()->superuser()->create();

    $this->actingAs($actor)
        ->post(route('accessories.checkout.store', $accessory), [
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
            'checkout_qty' => 1,
            'redirect_option' => 'index',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('accessories_checkout', [
        'accessory_id' => $accessory->id,
        'assigned_to' => $user->id,
    ]);
});

test('checkout to user in different company is blocked with fmcs enabled', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $accessory = Accessory::factory()->for($companyA)->create(['qty' => 5]);
    $user = User::factory()->forCompany($companyB)->create();
    $user->companies()->sync([$companyB->id]);

    $this->settings->enableMultipleFullCompanySupport();

    $actor = User::factory()->superuser()->create();

    $this->actingAs($actor)
        ->post(route('accessories.checkout.store', $accessory), [
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
            'checkout_qty' => 1,
            'redirect_option' => 'index',
        ])
        ->assertRedirect();

    $this->assertDatabaseMissing('accessories_checkout', [
        'accessory_id' => $accessory->id,
        'assigned_to' => $user->id,
    ]);
});

test('checkout to user is blocked when accessory has no company with fmcs enabled without floater', function () {
    $accessory = Accessory::factory()->create(['qty' => 5, 'company_id' => null]);
    [$companyA] = Company::factory()->count(1)->create();
    $user = User::factory()->forCompany($companyA)->create();
    $user->companies()->sync([$companyA->id]);

    $this->settings->enableMultipleFullCompanySupport();
    $this->settings->disableFloaterMode();

    $actor = User::factory()->superuser()->create();

    $this->actingAs($actor)
        ->post(route('accessories.checkout.store', $accessory), [
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
            'checkout_qty' => 1,
            'redirect_option' => 'index',
        ])
        ->assertRedirect();

    $this->assertDatabaseMissing('accessories_checkout', [
        'accessory_id' => $accessory->id,
        'assigned_to' => $user->id,
    ]);
});

test('checkout to user succeeds when accessory has no company with floater enabled', function () {
    $accessory = Accessory::factory()->create(['qty' => 5, 'company_id' => null]);
    [$companyA] = Company::factory()->count(1)->create();
    $user = User::factory()->forCompany($companyA)->create();
    $user->companies()->sync([$companyA->id]);

    $this->settings->enableFloaterMode();

    $actor = User::factory()->superuser()->create();

    $this->actingAs($actor)
        ->post(route('accessories.checkout.store', $accessory), [
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
            'checkout_qty' => 1,
            'redirect_option' => 'index',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('accessories_checkout', [
        'accessory_id' => $accessory->id,
        'assigned_to' => $user->id,
    ]);
});

test('checkout to asset does not throw when fmcs enabled', function () {
    [$companyA] = Company::factory()->count(1)->create();
    $accessory = Accessory::factory()->for($companyA)->create(['qty' => 5]);
    $asset = Asset::factory()->for($companyA)->create();

    $this->settings->enableMultipleFullCompanySupport();

    $actor = User::factory()->superuser()->create();

    $this->actingAs($actor)
        ->post(route('accessories.checkout.store', $accessory), [
            'checkout_to_type' => 'asset',
            'assigned_asset' => $asset->id,
            'checkout_qty' => 1,
            'redirect_option' => 'index',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('accessories_checkout', [
        'accessory_id' => $accessory->id,
        'assigned_to' => $asset->id,
    ]);
});

test('checkout to null company user blocked in strict mode', function () {
    [$companyA] = Company::factory()->count(1)->create();
    $accessory = Accessory::factory()->for($companyA)->create(['qty' => 5]);
    $nullCompanyUser = User::factory()->withoutCompany()->create();

    $this->settings->enableMultipleFullCompanySupport();

    $actor = User::factory()->superuser()->create();

    $this->actingAs($actor)
        ->post(route('accessories.checkout.store', $accessory), [
            'checkout_to_type' => 'user',
            'assigned_user' => $nullCompanyUser->id,
            'checkout_qty' => 1,
            'redirect_option' => 'index',
        ])
        ->assertRedirect();

    $this->assertDatabaseMissing('accessories_checkout', [
        'accessory_id' => $accessory->id,
        'assigned_to' => $nullCompanyUser->id,
    ]);
});

test('checkout to null company user succeeds in floater mode', function () {
    [$companyA] = Company::factory()->count(1)->create();
    $accessory = Accessory::factory()->for($companyA)->create(['qty' => 5]);
    $nullCompanyUser = User::factory()->withoutCompany()->create();

    $this->settings->enableFloaterMode();

    $actor = User::factory()->superuser()->create();

    $this->actingAs($actor)
        ->post(route('accessories.checkout.store', $accessory), [
            'checkout_to_type' => 'user',
            'assigned_user' => $nullCompanyUser->id,
            'checkout_qty' => 1,
            'redirect_option' => 'index',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('accessories_checkout', [
        'accessory_id' => $accessory->id,
        'assigned_to' => $nullCompanyUser->id,
    ]);
});

test('checkout to location does not throw when fmcs enabled', function () {
    [$companyA] = Company::factory()->count(1)->create();
    $accessory = Accessory::factory()->for($companyA)->create(['qty' => 5]);
    $location = Location::factory()->create();

    $this->settings->enableFloaterMode();

    $actor = User::factory()->superuser()->create();

    $this->actingAs($actor)
        ->post(route('accessories.checkout.store', $accessory), [
            'checkout_to_type' => 'location',
            'assigned_location' => $location->id,
            'checkout_qty' => 1,
            'redirect_option' => 'index',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('accessories_checkout', [
        'accessory_id' => $accessory->id,
        'assigned_to' => $location->id,
    ]);
});
