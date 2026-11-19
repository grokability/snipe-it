<?php

use App\Models\Company;
use App\Models\Maintenance;
use App\Models\User;

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('maintenances.show', Maintenance::factory()->create()->id))
        ->assertOk();
});

test('page renders history tab and history table', function () {
    $maintenance = Maintenance::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('maintenances.show', $maintenance))
        ->assertOk()
        ->assertSee(trans('general.history'))
        ->assertSee(route('api.maintenances.history', $maintenance), false);
});

test('user without asset view permission cannot view maintenance', function () {
    $maintenance = Maintenance::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('maintenances.show', $maintenance))
        ->assertForbidden();
});

test('user without asset view permission cannot view maintenance for another company when fmcs enabled', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $userInCompanyA = $companyA->users()->save(User::factory()->create());
    $maintenanceForCompanyB = Maintenance::factory()->create();
    $maintenanceForCompanyB->asset->update(['company_id' => $companyB->id]);

    $this->actingAs($userInCompanyA)
        ->get(route('maintenances.show', $maintenanceForCompanyB))
        ->assertRedirectToRoute('maintenances.index');
});

test('user cannot view maintenance for another company when fmcs enabled', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $userInCompanyA = $companyA->users()->save(User::factory()->editAssets()->make());
    $maintenanceForCompanyB = Maintenance::factory()->create();
    $maintenanceForCompanyB->asset->update(['company_id' => $companyB->id]);

    $this->actingAs($userInCompanyA)
        ->get(route('maintenances.show', $maintenanceForCompanyB))
        ->assertRedirectToRoute('maintenances.index');
});
