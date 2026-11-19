<?php

use App\Models\Asset;
use App\Models\Company;
use App\Models\User;

test('clean company is bulk selectable', function () {
    $company = Company::factory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.companies.show', $company))
        ->assertOk()
        ->assertJsonPath('available_actions.bulk_selectable.delete', true);
});

test('company with assets is not bulk selectable', function () {
    $company = Company::factory()->create();
    Asset::factory()->create(['company_id' => $company->id]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.companies.show', $company))
        ->assertOk()
        ->assertJsonPath('available_actions.bulk_selectable.delete', false);
});

test('company with users is not bulk selectable', function () {
    $company = Company::factory()->create();
    $company->users()->syncWithoutDetaching([User::factory()->create()->id]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.companies.show', $company))
        ->assertOk()
        ->assertJsonPath('available_actions.bulk_selectable.delete', false);
});

test('parent company with children is not bulk selectable', function () {
    $parent = Company::factory()->create();
    Company::factory()->create(['parent_id' => $parent->id]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.companies.show', $parent))
        ->assertOk()
        ->assertJsonPath('available_actions.bulk_selectable.delete', false);
});
