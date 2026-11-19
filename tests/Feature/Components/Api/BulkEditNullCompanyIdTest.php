<?php

use App\Models\Company;
use App\Models\Component;
use App\Models\User;

test('non superuser cannot null company id via bulk edit under fmcs', function () {
    $this->settings->enableMultipleFullCompanySupport();

    $company = Company::factory()->create();
    $actor = $company->users()->save(
        User::factory()->editComponents()->viewComponents()->make()
    );
    $component = Component::factory()->for($company)->create();

    $this->actingAsForApi($actor)
        ->postJson(route('api.components.bulkedit'), [
            'ids' => [$component->id],
            'null_company_id' => true,
        ])
        ->assertStatus(422);

    expect($component->fresh()->company_id)->toEqual($company->id);
});

test('non superuser cannot null company id via bulk edit in floater mode', function () {
    $this->settings->enableFloaterMode();

    $company = Company::factory()->create();
    $actor = $company->users()->save(
        User::factory()->editComponents()->viewComponents()->make()
    );
    $component = Component::factory()->for($company)->create();

    $this->actingAsForApi($actor)
        ->postJson(route('api.components.bulkedit'), [
            'ids' => [$component->id],
            'null_company_id' => true,
        ])
        ->assertStatus(422);

    expect($component->fresh()->company_id)->toEqual($company->id);
});

test('superuser can still null company id via bulk edit', function () {
    $this->settings->enableMultipleFullCompanySupport();

    $company = Company::factory()->create();
    $component = Component::factory()->for($company)->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.components.bulkedit'), [
            'ids' => [$component->id],
            'null_company_id' => true,
        ])
        ->assertOk();

    expect($component->fresh()->company_id)->toBeNull();
});

test('non superuser can still bulk edit other fields', function () {
    $this->settings->enableMultipleFullCompanySupport();

    $company = Company::factory()->create();
    $actor = $company->users()->save(
        User::factory()->editComponents()->viewComponents()->make()
    );
    $component = Component::factory()->for($company)->create(['min_amt' => 1]);

    $this->actingAsForApi($actor)
        ->postJson(route('api.components.bulkedit'), [
            'ids' => [$component->id],
            'min_amt' => 42,
        ])
        ->assertOk();

    expect((int) $component->fresh()->min_amt)->toBe(42);
    expect($component->fresh()->company_id)->toEqual($company->id);
});
