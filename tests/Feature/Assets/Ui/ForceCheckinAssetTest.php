<?php

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\User;

test('permission required to force checkin asset', function () {
    $asset = Asset::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('asset.checkin.force', $asset))
        ->assertForbidden();
});

test('can force checkin asset with orphaned assigned to and missing type', function () {
    $asset = Asset::factory()->create();
    $originalCheckinCounter = (int) $asset->checkin_counter;
    $asset->assigned_to = 999; // Non-existent ID
    $asset->assigned_type = null; // Missing type
    $asset->forceSave();

    $response = $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('asset.checkin.force', $asset));

    $response->assertRedirect(route('hardware.show', $asset))
        ->assertSessionHas('success');

    $asset->refresh();
    expect($asset->assigned_to)->toBeNull();
    expect($asset->assigned_type)->toBeNull();
    expect((int) $asset->checkin_counter)->toBe($originalCheckinCounter);

    $this->assertDatabaseHas((new Actionlog)->getTable(), [
        'item_type' => Asset::class,
        'item_id' => $asset->id,
        'action_type' => 'force checkin',
    ]);

    $forceCheckinLog = Actionlog::query()
        ->where('item_type', Asset::class)
        ->where('item_id', $asset->id)
        ->where('action_type', 'force checkin')
        ->latest('id')
        ->first();

    expect($forceCheckinLog)->not->toBeNull();
    expect($forceCheckinLog->log_meta)->toBeNull();
});

test('can force checkin asset with hard deleted assigned to', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create();
    $asset->assigned_to = $user->id;
    $asset->assigned_type = User::class;
    $asset->save();

    // Hard delete the user
    $user->forceDelete();

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('asset.checkin.force', $asset))
        ->assertRedirect(route('hardware.show', $asset))
        ->assertSessionHas('success');

    $asset->refresh();
    expect($asset->assigned_to)->toBeNull();
    expect($asset->assigned_type)->toBeNull();
});

test('cannot force checkin asset that is not orphaned', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create();
    $asset->assigned_to = $user->id;
    $asset->assigned_type = User::class;
    $asset->save();

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('asset.checkin.force', $asset))
        ->assertRedirect(route('hardware.show', $asset))
        ->assertSessionHas('error');

    $asset->refresh();
    expect($asset->assigned_to)->not->toBeNull();
});

test('cannot force checkin asset with no assignment', function () {
    $asset = Asset::factory()->create();
    $asset->assigned_to = null;
    $asset->assigned_type = null;
    $asset->save();

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('asset.checkin.force', $asset))
        ->assertRedirect(route('hardware.show', $asset))
        ->assertSessionHas('error');
});

test('force checkin button shown on asset view when orphaned', function () {
    $asset = Asset::factory()->create();
    $asset->assigned_to = 999; // Non-existent ID
    $asset->assigned_type = User::class;
    $asset->save();

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('hardware.show', $asset))
        ->assertSeeText(trans('general.force_checkin'));
});

test('force checkin button not shown when asset is not orphaned', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create();
    $asset->assigned_to = $user->id;
    $asset->assigned_type = User::class;
    $asset->save();

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('hardware.show', $asset))
        ->assertDontSeeText(trans('general.force_checkin'));
});
