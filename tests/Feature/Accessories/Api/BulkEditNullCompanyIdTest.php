<?php

use App\Models\Accessory;
use App\Models\Company;
use App\Models\User;

test('non superuser cannot null company id via bulk edit under fmcs', function () {
    $this->settings->enableMultipleFullCompanySupport();

    $company = Company::factory()->create();
    $actor = $company->users()->save(
        User::factory()->editAccessories()->viewAccessories()->make()
    );
    $accessory = Accessory::factory()->for($company)->create();

    $this->actingAsForApi($actor)
        ->postJson(route('api.accessories.bulkedit'), [
            'ids' => [$accessory->id],
            'null_company_id' => true,
        ])
        ->assertStatus(422);

    expect($accessory->fresh()->company_id)->toEqual($company->id);
});

test('non superuser cannot null company id via bulk edit in floater mode', function () {
    $this->settings->enableFloaterMode();

    $company = Company::factory()->create();
    $actor = $company->users()->save(
        User::factory()->editAccessories()->viewAccessories()->make()
    );
    $accessory = Accessory::factory()->for($company)->create();

    $this->actingAsForApi($actor)
        ->postJson(route('api.accessories.bulkedit'), [
            'ids' => [$accessory->id],
            'null_company_id' => true,
        ])
        ->assertStatus(422);

    expect($accessory->fresh()->company_id)->toEqual($company->id);
});

test('superuser can still null company id via bulk edit', function () {
    $this->settings->enableMultipleFullCompanySupport();

    $company = Company::factory()->create();
    $accessory = Accessory::factory()->for($company)->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.accessories.bulkedit'), [
            'ids' => [$accessory->id],
            'null_company_id' => true,
        ])
        ->assertOk();

    expect($accessory->fresh()->company_id)->toBeNull();
});

test('non superuser can still bulk edit other fields', function () {
    $this->settings->enableMultipleFullCompanySupport();

    $company = Company::factory()->create();
    $actor = $company->users()->save(
        User::factory()->editAccessories()->viewAccessories()->make()
    );
    $accessory = Accessory::factory()->for($company)->create(['min_amt' => 1]);

    $this->actingAsForApi($actor)
        ->postJson(route('api.accessories.bulkedit'), [
            'ids' => [$accessory->id],
            'min_amt' => 42,
        ])
        ->assertOk();

    expect((int) $accessory->fresh()->min_amt)->toBe(42);
    expect($accessory->fresh()->company_id)->toEqual($company->id);
});
