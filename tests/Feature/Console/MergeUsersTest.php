<?php

use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Consumable;
use App\Models\LicenseSeat;
use App\Models\User;

test('assets are transferred on user merge', function () {
    $user1 = User::factory()->create(['username' => 'user1']);
    $user_to_merge_into = User::factory()->create(['username' => 'user1@example.com']);

    Asset::factory()->count(3)->assignedToUser($user1)->create();
    Asset::factory()->count(3)->assignedToUser($user_to_merge_into)->create();

    $this->artisan('snipeit:merge-users')->assertExitCode(0);

    expect($user_to_merge_into->refresh()->assets->count())->toEqual(6);
    expect($user1->refresh()->assets->count())->toEqual(0);
});

test('licenses are transferred on user merge', function () {
    $user1 = User::factory()->create(['username' => 'user1']);
    $user_to_merge_into = User::factory()->create(['username' => 'user1@example.com']);

    LicenseSeat::factory()->count(3)->create(['assigned_to' => $user1->id]);
    LicenseSeat::factory()->count(3)->create(['assigned_to' => $user_to_merge_into->id]);

    expect($user_to_merge_into->refresh()->licenses->count())->toEqual(3);

    $this->artisan('snipeit:merge-users')->assertExitCode(0);

    expect($user_to_merge_into->refresh()->licenses->count())->toEqual(6);
    expect($user1->refresh()->licenses->count())->toEqual(0);
});

test('accessories transferred on user merge', function () {
    $user1 = User::factory()->create(['username' => 'user1']);
    $user_to_merge_into = User::factory()->create(['username' => 'user1@example.com']);

    Accessory::factory()->count(3)->checkedOutToUser($user1)->create();
    Accessory::factory()->count(3)->checkedOutToUser($user_to_merge_into)->create();

    expect($user_to_merge_into->refresh()->accessories->count())->toEqual(3);

    $this->artisan('snipeit:merge-users')->assertExitCode(0);

    expect($user_to_merge_into->refresh()->accessories->count())->toEqual(6);
    expect($user1->refresh()->accessories->count())->toEqual(0);
});

test('consumables transferred on user merge', function () {
    $user1 = User::factory()->create(['username' => 'user1']);
    $user_to_merge_into = User::factory()->create(['username' => 'user1@example.com']);

    Consumable::factory()->count(3)->checkedOutToUser($user1)->create();
    Consumable::factory()->count(3)->checkedOutToUser($user_to_merge_into)->create();

    expect($user_to_merge_into->refresh()->consumables->count())->toEqual(3);

    $this->artisan('snipeit:merge-users')->assertExitCode(0);

    expect($user_to_merge_into->refresh()->consumables->count())->toEqual(6);
    expect($user1->refresh()->consumables->count())->toEqual(0);
});

test('files are transferred on user merge', function () {
    $user1 = User::factory()->create(['username' => 'user1']);
    $user_to_merge_into = User::factory()->create(['username' => 'user1@example.com']);

    Actionlog::factory()->count(3)->filesUploaded()->create(['item_id' => $user1->id]);
    Actionlog::factory()->count(3)->filesUploaded()->create(['item_id' => $user_to_merge_into->id]);

    expect($user_to_merge_into->refresh()->uploads->count())->toEqual(3);

    $this->artisan('snipeit:merge-users')->assertExitCode(0);

    expect($user_to_merge_into->refresh()->uploads->count())->toEqual(6);
    expect($user1->refresh()->uploads->count())->toEqual(0);
});

test('acceptances are transferred on user merge', function () {
    $user1 = User::factory()->create(['username' => 'user1']);
    $user_to_merge_into = User::factory()->create(['username' => 'user1@example.com']);

    Actionlog::factory()->count(3)->acceptedSignature()->create(['target_id' => $user1->id]);
    Actionlog::factory()->count(3)->acceptedSignature()->create(['target_id' => $user_to_merge_into->id]);

    expect($user_to_merge_into->refresh()->acceptances->count())->toEqual(3);

    $this->artisan('snipeit:merge-users')->assertExitCode(0);

    expect($user_to_merge_into->refresh()->acceptances->count())->toEqual(6);
    expect($user1->refresh()->acceptances->count())->toEqual(0);
});

test('user update history is transferred on user merge', function () {
    $user1 = User::factory()->create(['username' => 'user1']);
    $user_to_merge_into = User::factory()->create(['username' => 'user1@example.com']);

    Actionlog::factory()->count(3)->userUpdated()->create(['target_id' => $user1->id, 'item_id' => $user1->id]);
    Actionlog::factory()->count(3)->userUpdated()->create(['target_id' => $user_to_merge_into->id, 'item_id' => $user_to_merge_into->id]);

    expect($user_to_merge_into->refresh()->userlog->count())->toEqual(3);

    $this->artisan('snipeit:merge-users')->assertExitCode(0);

    // This needs to be more than the otherwise expected because the merge action itself is logged for the two merging users
    expect($user_to_merge_into->refresh()->userlog->count())->toEqual(7);
    expect($user1->refresh()->userlog->count())->toEqual(1);
});
