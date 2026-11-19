<?php

use App\Models\User;

test('handles asset404', function () {
    $this->actingAs(User::factory()->viewAssets()->create())
        ->get(route('hardware.checkout.create', 9999))
        ->assertRedirectToRoute('hardware.index');
});

test('handles maintenance404', function () {
    $this->actingAs(User::factory()->viewAssets()->create())
        ->get(route('maintenances.show', 9999))
        ->assertRedirectToRoute('maintenances.index');
});

test('handles asset model404', function () {
    $this->actingAs(User::factory()->viewAssetModels()->create())
        ->get(route('models.show', 9999))
        ->assertRedirectToRoute('models.index');
});

test('handles license seat404', function () {
    $this->actingAs(User::factory()->viewLicenses()->create())
        ->get(route('licenses.checkin', 9999))
        ->assertRedirectToRoute('licenses.index');
});

test('handles predefined kit404', function () {
    $this->actingAs(User::factory()->viewPredefinedKits()->create())
        ->get(route('kits.show', 9999))
        ->assertRedirectToRoute('kits.index');
});

test('handles report template404', function () {
    $this->actingAs(User::factory()->canViewReports()->create())
        ->get(route('report-templates.show', 9999))
        ->assertRedirectToRoute('reports/custom');
});
