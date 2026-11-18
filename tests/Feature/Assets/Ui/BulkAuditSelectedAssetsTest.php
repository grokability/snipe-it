<?php

use App\Models\Asset;
use App\Models\Company;
use App\Models\User;

test('permission required to view form', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('hardware.bulk-audit.show'))
        ->assertForbidden();
});

test('auditor can view form', function () {
    $this->actingAs(User::factory()->auditAssets()->create())
        ->get(route('hardware.bulk-audit.show'))
        ->assertOk()
        ->assertViewIs('hardware.bulk-audit');
});

test('bulk actions dispatcher redirects to show with selected assets flashed', function () {
    // BulkAssetsController::edit gates on view permission before
    // dispatching to any specific action, so the auditor also
    // needs view to reach the audit branch.
    $auditor = User::factory()->viewAssets()->auditAssets()->create();
    $assets = Asset::factory()->count(3)->create();

    $this->actingAs($auditor)
        ->post(route('hardware.bulkedit.show'), [
            'bulk_actions' => 'audit',
            'ids' => $assets->pluck('id')->toArray(),
        ])
        ->assertRedirect(route('hardware.bulk-audit.show'));
});

test('form prefills next audit date from audit interval', function () {
    $this->settings->setAuditInterval(6);

    $this->actingAs(User::factory()->auditAssets()->create())
        ->get(route('hardware.bulk-audit.show'))
        ->assertOk()
        ->assertViewHas('next_audit_date', \Carbon\Carbon::now()->addMonths(6)->toDateString());
});

test('form leaves next audit date blank when no audit interval configured', function () {
    // With no audit_interval set, "today + 0 months" would prefill
    // as today, which reads as "audit again right now" and is
    // useless. The form should start empty and let the user pick
    // a date (or leave blank to skip updating next_audit_date).
    $this->settings->setAuditInterval(null);

    $this->actingAs(User::factory()->auditAssets()->create())
        ->get(route('hardware.bulk-audit.show'))
        ->assertOk()
        ->assertViewHas('next_audit_date', null);
});

test('location controls render unscoped when location scoping is off', function () {
    // FMCS location scoping disabled -> no company-based hiding
    // and no picker scoping regardless of selection distribution.
    $this->actingAs(User::factory()->auditAssets()->create())
        ->withSession(['_old_input' => ['selected_assets' => [1, 2, 3]]])
        ->get(route('hardware.bulk-audit.show'))
        ->assertOk()
        ->assertViewHas('hideLocationFields', false)
        ->assertViewHas('sharedCompanyId', null);
});

test('location controls scope to shared company when selection is uniform', function () {
    // FMCS location scoping on + every selected asset in the same
    // company -> the picker is scoped so only that company's
    // locations are offered. Uses the real dispatch flow so
    // `old('selected_assets')` populates from the same session
    // mechanism prod uses. Create fixtures BEFORE enabling FMCS
    // so the Asset factory's fmcs_company validation rule doesn't
    // fail with no authenticated user during setUp.
    $company = Company::factory()->create();
    $assets = Asset::factory()->count(3)->create(['company_id' => $company->id]);
    $actor = User::factory()->viewAssets()->auditAssets()->forCompany($company)->create();

    $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

    $response = $this->actingAs($actor)
        ->post(route('hardware.bulkedit.show'), [
            'bulk_actions' => 'audit',
            'ids' => $assets->pluck('id')->toArray(),
        ]);

    $this->followRedirects($response)
        ->assertOk()
        ->assertViewHas('hideLocationFields', false)
        ->assertViewHas('sharedCompanyId', $company->id);
});

test('location controls hide when selection spans multiple companies', function () {
    // FMCS location scoping on + selected assets belong to more
    // than one company -> the picker is hidden entirely since no
    // shared location can legitimately fit all of them.
    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $assetA = Asset::factory()->create(['company_id' => $companyA->id]);
    $assetB = Asset::factory()->create(['company_id' => $companyB->id]);
    $actor = User::factory()->superuser()->create();

    $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

    $response = $this->actingAs($actor)
        ->post(route('hardware.bulkedit.show'), [
            'bulk_actions' => 'audit',
            'ids' => [$assetA->id, $assetB->id],
        ]);

    $this->followRedirects($response)
        ->assertOk()
        ->assertViewHas('hideLocationFields', true)
        ->assertViewHas('sharedCompanyId', null);
});
