<?php

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\User;

test('requires authentication', function () {
    // Give the setup middleware a user so it doesn't short-circuit
    // us to /setup before auth has a chance to run.
    User::factory()->create();

    $this->get(route('profile.storedeula.download', ['filename' => 'anything.pdf']))
        ->assertRedirect(route('login'));
});

test('nonexistent filename redirects with error', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('profile.storedeula.download', ['filename' => 'does-not-exist.pdf']))
        ->assertRedirect(route('home'));
});

test('end user can reach download flow for their own accepted eula', function () {
    $endUser = User::factory()->create();
    $asset = Asset::factory()->create();
    $filename = 'accepted-user-'.uniqid().'.pdf';

    Actionlog::factory()->create([
        'action_type' => 'accepted',
        'item_id' => $asset->id,
        'item_type' => Asset::class,
        'target_id' => $endUser->id,
        'target_type' => User::class,
        'filename' => $filename,
    ]);

    expect($endUser->hasAccess('users.view'))->toBeFalse();

    $this->from(route('account'))
        ->actingAs($endUser)
        ->get(route('profile.storedeula.download', ['filename' => $filename]))
        ->assertRedirect(route('account'))
        ->assertSessionHas('error', trans('general.file_does_not_exist'));
});

test('end user cannot download someone elses eula', function () {
    $acceptingUser = User::factory()->create();
    $otherEndUser = User::factory()->create();
    $asset = Asset::factory()->create();
    $filename = 'someone-elses-'.uniqid().'.pdf';

    Actionlog::factory()->create([
        'action_type' => 'accepted',
        'item_id' => $asset->id,
        'item_type' => Asset::class,
        'target_id' => $acceptingUser->id,
        'target_type' => User::class,
        'filename' => $filename,
    ]);

    $this->actingAs($otherEndUser)
        ->get(route('profile.storedeula.download', ['filename' => $filename]))
        ->assertForbidden();
});

test('user with users view permission can reach download flow for any eula', function () {
    $acceptingUser = User::factory()->create();
    $manager = User::factory()->viewUsers()->create();
    $asset = Asset::factory()->create();
    $filename = 'manager-download-'.uniqid().'.pdf';

    Actionlog::factory()->create([
        'action_type' => 'accepted',
        'item_id' => $asset->id,
        'item_type' => Asset::class,
        'target_id' => $acceptingUser->id,
        'target_type' => User::class,
        'filename' => $filename,
    ]);

    $this->from(route('account'))
        ->actingAs($manager)
        ->get(route('profile.storedeula.download', ['filename' => $filename]))
        ->assertRedirect(route('account'))
        ->assertSessionHas('error', trans('general.file_does_not_exist'));
});

test('filename that matches a non acceptance log row is rejected', function () {
    $endUser = User::factory()->create();
    $filename = 'not-a-eula-'.uniqid().'.pdf';

    // A log row with the same filename but action_type != accepted
    // (e.g. a general file upload) must not be treated as an EULA
    // the end user is entitled to.
    Actionlog::factory()->create([
        'action_type' => 'uploaded',
        'item_id' => $endUser->id,
        'item_type' => User::class,
        'target_id' => $endUser->id,
        'target_type' => User::class,
        'filename' => $filename,
    ]);

    $this->actingAs($endUser)
        ->get(route('profile.storedeula.download', ['filename' => $filename]))
        ->assertRedirect(route('home'));
});
