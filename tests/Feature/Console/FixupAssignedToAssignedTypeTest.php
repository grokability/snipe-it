<?php

use App\Models\Asset;
use App\Models\User;

test('empty assigned type', function () {
    $asset = Asset::factory()->create();
    $user = User::factory()->create();
    $admin = User::factory()->admin()->create();

    $asset->checkOut($user, $admin);
    $asset->assigned_type = null; // blank out the assigned type
    $asset->save();

    $this->artisan('snipeit:assigned-to-fixup --debug')->assertExitCode(0);

    expect($asset->fresh()->assigned_type)->toEqual(User::class);
});

test('invalid assigned to', function () {
    $this->markTestIncomplete();
    $asset = Asset::factory()->create();
    $user = User::factory()->create();
    $admin = User::factory()->admin()->create();

    $asset->checkOut($user, $admin);
    $asset->assigned_type = null;
    $asset->assigned_to = null;
    $asset->saveOrFail(); // *should* generate a 'checkin'?

    $asset->assigned_to = $user->id; // incorrectly mark asset as partially checked-out
    $asset->saveOrFail();

    $this->artisan('snipeit:assigned-to-fixup --debug')->assertExitCode(0);

    expect($asset->fresh()->assigned_to)->toBeNull();
});
