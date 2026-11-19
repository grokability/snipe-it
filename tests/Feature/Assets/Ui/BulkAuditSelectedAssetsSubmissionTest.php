<?php

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

pest()->group('auditing');

test('permission required to submit', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('hardware.bulk-audit.store'), [
            'selected_assets' => [1, 2, 3],
        ])
        ->assertForbidden();
});

test('submitting without selected assets bounces back', function () {
    $this->actingAs(User::factory()->auditAssets()->create())
        ->post(route('hardware.bulk-audit.store'), [])
        ->assertRedirect(route('hardware.bulk-audit.show'))
        ->assertSessionHas('error');
});

test('bulk audit writes one audit actionlog per asset', function () {
    $assets = Asset::factory()->count(3)->create();

    $this->actingAs(User::factory()->auditAssets()->create())
        ->post(route('hardware.bulk-audit.store'), [
            'selected_assets' => $assets->pluck('id')->toArray(),
            'note' => 'bulk audit test',
        ])
        ->assertRedirect(route('hardware.index'))
        ->assertSessionHas('success');

    foreach ($assets as $asset) {
        $this->assertDatabaseHas('action_logs', [
            'action_type' => 'audit',
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'note' => 'bulk audit test',
        ]);
    }
});

test('bulk audit updates next audit date when provided', function () {
    $assets = Asset::factory()->count(2)->create();

    $this->actingAs(User::factory()->auditAssets()->create())
        ->post(route('hardware.bulk-audit.store'), [
            'selected_assets' => $assets->pluck('id')->toArray(),
            'next_audit_date' => '2099-01-15',
        ])
        ->assertRedirect(route('hardware.index'));

    // next_audit_date has an Attribute accessor that returns a
    // pre-formatted date string, not a Carbon instance, so we
    // check the raw column value instead.
    foreach ($assets as $asset) {
        expect($asset->fresh()->getRawOriginal('next_audit_date'))->toBe('2099-01-15');
    }
});

test('bulk audit updates asset location when update location opt in is checked', function () {
    $location = Location::factory()->create();
    $assets = Asset::factory()->count(2)->create();

    $this->actingAs(User::factory()->auditAssets()->create())
        ->post(route('hardware.bulk-audit.store'), [
            'selected_assets' => $assets->pluck('id')->toArray(),
            'location_id' => $location->id,
            'update_location' => '1',
        ])
        ->assertRedirect(route('hardware.index'));

    foreach ($assets as $asset) {
        expect($asset->fresh()->location_id)->toBe($location->id);
    }
});

test('bulk audit does not move assets when update location is unchecked', function () {
    // Provided location_id + no update_location flag should
    // record the audit's location on each log entry (as "where
    // the audit happened") without touching the asset's actual
    // location_id. Matches the API's update_location=1 gate.
    $originalLocation = Location::factory()->create();
    $auditLocation = Location::factory()->create();
    $assets = Asset::factory()->count(2)->create(['location_id' => $originalLocation->id]);

    $this->actingAs(User::factory()->auditAssets()->create())
        ->post(route('hardware.bulk-audit.store'), [
            'selected_assets' => $assets->pluck('id')->toArray(),
            'location_id' => $auditLocation->id,
            // update_location intentionally omitted (unchecked)
        ])
        ->assertRedirect(route('hardware.index'));

    foreach ($assets as $asset) {
        $fresh = $asset->fresh();
        expect($fresh->location_id)->toBe($originalLocation->id, "Asset {$asset->id} location must not change when update_location is unchecked.");

        // But the audit log DOES record the submitted location.
        $log = Actionlog::where('action_type', 'audit')
            ->where('item_type', Asset::class)
            ->where('item_id', $asset->id)
            ->first();
        expect($log)->not->toBeNull();
        expect((int) $log->location_id)->toBe($auditLocation->id);
    }
});

test('bulk audit attaches uploaded image to every row', function () {
    // Matches the API's bulkAudit behavior: one uploaded image is
    // stamped onto every asset's audit log entry, with the stored
    // filename scoped by asset id (audit-{id}-...).
    Storage::fake();
    $assets = Asset::factory()->count(2)->create();

    $this->actingAs(User::factory()->auditAssets()->create())
        ->post(route('hardware.bulk-audit.store'), [
            'selected_assets' => $assets->pluck('id')->toArray(),
            'file' => [UploadedFile::fake()->image('audit.png')],
        ])
        ->assertRedirect(route('hardware.index'))
        ->assertSessionHas('success');

    foreach ($assets as $asset) {
        $log = Actionlog::where('action_type', 'audit')
            ->where('item_type', Asset::class)
            ->where('item_id', $asset->id)
            ->first();

        expect($log)->not->toBeNull("Audit log missing for asset {$asset->id}");
        expect($log->filename)->not->toBeEmpty("Audit log filename missing for asset {$asset->id}");
        expect($log->filename)->toStartWith('audit-'.$asset->id);
    }
});

test('bulk audit rejects unresolvable location before touching any asset', function () {
    $assets = Asset::factory()->count(2)->create();
    $originalLocations = $assets->pluck('location_id', 'id');

    $this->actingAs(User::factory()->auditAssets()->create())
        ->post(route('hardware.bulk-audit.store'), [
            'selected_assets' => $assets->pluck('id')->toArray(),
            'location_id' => 9999999, // does not exist
        ])
        ->assertRedirect(route('hardware.bulk-audit.show'))
        ->assertSessionHas('error');

    // No audit rows written, no location mutations applied.
    expect(Actionlog::where('action_type', 'audit')->count())->toBe(0);
    foreach ($assets as $asset) {
        expect($asset->fresh()->location_id)->toBe($originalLocations[$asset->id]);
    }
});
