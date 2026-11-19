<?php

use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Carbon\Carbon;

pest()->group('auditing');

test('permission required to view audit create page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('asset.audit.create', Asset::factory()->create()))
        ->assertForbidden();
});

test('page can be accessed', function () {
    $this->actingAs(User::factory()->auditAssets()->create())
        ->get(route('asset.audit.create', Asset::factory()->create()))
        ->assertStatus(200);
});

test('permission required to audit asset', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('asset.audit.store', Asset::factory()->create()))
        ->assertForbidden();
});

test('audit page is given todays date when audit interval is null', function () {
    $this->settings->setAuditInterval(null);

    $this->actingAs(User::factory()->auditAssets()->create())
        ->get(route('asset.audit.create', Asset::factory()->create()))
        ->assertViewIs('hardware.audit')
        ->assertViewHas('next_audit_date', Carbon::now()->toDateString());
});

test('audit page is given correct date when audit interval is set', function () {
    $this->settings->setAuditInterval(5);

    $this->actingAs(User::factory()->auditAssets()->create())
        ->get(route('asset.audit.create', Asset::factory()->create()))
        ->assertViewIs('hardware.audit')
        ->assertViewHas('next_audit_date', Carbon::now()->addMonths(5)->toDateString());
});

test('asset can be audited', function () {
    $this->settings->setAuditInterval(2);

    [$originalLocation, $anotherLocation] = Location::factory()->count(2)->create();

    $asset = Asset::factory()->create([
        'location_id' => $originalLocation->id,
        'next_audit_date' => null,
    ]);

    $future = now()->addMonths(3)->toDateString();

    $this->actingAs(User::factory()->auditAssets()->create())
        ->post(route('asset.audit.store', $asset), [
            'location_id' => $anotherLocation->id,
            'next_audit_date' => $future,
            'note' => 'A note about the asset',
            'redirect_option' => 'index',
        ])
        ->assertRedirectToRoute('hardware.index');

    $this->assertHasTheseActionLogs($asset, ['create', 'audit']);

    $asset->refresh();
    $auditEntry = $asset->log->firstWhere('action_type', 'audit');

    expect($auditEntry?->location_id)->toEqual($anotherLocation->id);
    expect($auditEntry?->note)->toEqual('A note about the asset');
    expect($asset->next_audit_date)->toEqual($future);
});

test('asset location can be updated when auditing', function () {
    [$originalLocation, $anotherLocation] = Location::factory()->count(2)->create();

    $asset = Asset::factory()->create([
        'location_id' => $originalLocation->id,
        'next_audit_date' => null,
    ]);

    $future = now()->addMonths(3)->toDateString();

    $this->actingAs(User::factory()->auditAssets()->create())
        ->post(route('asset.audit.store', $asset), [
            'location_id' => $anotherLocation->id,
            'update_location' => '1',
            'next_audit_date' => $future,
            'note' => 'A note about the asset',
            'redirect_option' => 'index',
        ])
        ->assertRedirectToRoute('hardware.index');

    $this->assertHasTheseActionLogs($asset, ['create', 'audit']);

    $asset->refresh();
    expect($asset->location_id)->toEqual($anotherLocation->id);
});

test('asset audit post is redirected to asset index if redirect selection is index', function () {
    $asset = Asset::factory()->create();

    $response = $this->actingAs(User::factory()->viewAssets()->editAssets()->auditAssets()->create())
        ->from(route('asset.audit.create', $asset))
        ->post(route('asset.audit.store', $asset),
            [
                'redirect_option' => 'index',
            ])
        ->assertStatus(302)
        ->assertRedirect(route('hardware.index'));
    $this->followRedirects($response)->assertSee('success');

    $this->assertHasTheseActionLogs($asset, ['create', 'audit']);
});

test('asset audit post is redirected to asset page if redirect selection is asset', function () {
    $asset = Asset::factory()->create();

    $response = $this->actingAs(User::factory()->viewAssets()->editAssets()->auditAssets()->create())
        ->from(route('asset.audit.create', $asset))
        ->post(route('asset.audit.store', $asset),
            [
                'redirect_option' => 'item',
            ])
        ->assertStatus(302)
        ->assertRedirect(route('hardware.show', $asset));
    $this->followRedirects($response)->assertSee('success');
    $this->assertHasTheseActionLogs($asset, ['create', 'audit']); // WAT.
});

test('asset audit post is redirected to audit due page if redirect selection is list', function () {
    $asset = Asset::factory()->create();

    $response = $this->actingAs(User::factory()->viewAssets()->editAssets()->auditAssets()->create())
        ->from(route('asset.audit.create', $asset))
        ->post(route('asset.audit.store', $asset),
            [
                'redirect_option' => 'other_redirect',
            ])
        ->assertStatus(302)
        ->assertRedirect(route('assets.audit.due'));
    $this->followRedirects($response)->assertSee('success');
    $this->assertHasTheseActionLogs($asset, ['create', 'audit']);
});
