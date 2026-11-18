<?php

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Company;
use App\Models\User;

beforeEach(function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$this->companyA, $this->companyB] = Company::factory()->count(2)->create();

    $this->auditor = User::factory()
        ->auditAssets()
        ->viewAssets()
        ->forCompany($this->companyA)
        ->create();

    $this->forbiddenAsset = Asset::factory()->create([
        'company_id' => $this->companyB->id,
    ]);
});

test('ui bulk audit does not audit asset in forbidden company', function () {
    $this->actingAs($this->auditor)
        ->post(route('hardware.bulk-audit.store'), [
            'selected_assets' => [$this->forbiddenAsset->id],
            'note' => 'cross-company probe',
        ]);

    expect(Actionlog::where('action_type', 'audit')
        ->where('item_type', Asset::class)
        ->where('item_id', $this->forbiddenAsset->id)
        ->count())->toBe(0, 'UI bulk audit must not write an audit log for a cross-company asset.');
});

test('web single audit store does not write audit for forbidden asset', function () {
    // Assertion is on the side-effect (no audit log entry), not
    // the HTTP status code. Whichever layer catches the
    // cross-company attempt (route-model binding + scope, policy,
    // or model validation) is fine as long as no audit lands.
    $this->actingAs($this->auditor)
        ->post(route('asset.audit.store', $this->forbiddenAsset), [
            'note' => 'cross-company probe',
        ]);

    expect(Actionlog::where('action_type', 'audit')
        ->where('item_type', Asset::class)
        ->where('item_id', $this->forbiddenAsset->id)
        ->count())->toBe(0, 'Web single-audit POST must not write an audit log for a cross-company asset.');
});

test('api single audit does not write audit for forbidden asset', function () {
    $this->actingAsForApi($this->auditor)
        ->postJson(route('api.asset.audit', $this->forbiddenAsset), [
            'note' => 'cross-company probe',
        ]);

    expect(Actionlog::where('action_type', 'audit')
        ->where('item_type', Asset::class)
        ->where('item_id', $this->forbiddenAsset->id)
        ->count())->toBe(0, 'API single-audit POST must not write an audit log for a cross-company asset.');
});

test('api legacy body lookup audit does not resolve forbidden asset', function () {
    // The legacy body-lookup path in Api\AssetsController::audit()
    // reads asset_tag from the request body and calls
    // Asset::where('asset_tag', ...)->first(). CompanyableScope
    // must fire on that lookup so cross-company tags don't
    // resolve to an audit-writable Asset for a non-admin actor.
    $this->actingAsForApi($this->auditor)
        ->postJson(route('api.asset.audit.legacy'), [
            'asset_tag' => $this->forbiddenAsset->asset_tag,
            'note' => 'cross-company probe',
        ]);

    expect(Actionlog::where('action_type', 'audit')
        ->where('item_type', Asset::class)
        ->where('item_id', $this->forbiddenAsset->id)
        ->count())->toBe(0, 'API body-lookup audit must not resolve or audit a cross-company asset.');
});

test('api bulk audit does not audit forbidden asset', function () {
    $this->actingAsForApi($this->auditor)
        ->postJson(route('api.asset.bulk-audit'), [
            'ids' => [$this->forbiddenAsset->id],
            'note' => 'cross-company probe',
        ]);

    expect(Actionlog::where('action_type', 'audit')
        ->where('item_type', Asset::class)
        ->where('item_id', $this->forbiddenAsset->id)
        ->count())->toBe(0, 'API bulk audit must not write an audit log for a cross-company asset.');
});
