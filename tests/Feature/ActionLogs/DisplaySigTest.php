<?php

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\License;
use App\Models\User;

test('requires authentication', function () {
    $actionlog = Actionlog::factory()->acceptedSignature()->create();

    $this->get(route('log.signature.view', ['filename' => $actionlog->accept_signature]))
        ->assertRedirect(route('login'));
});

test('nonexistent filename redirects with error', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('log.signature.view', ['filename' => 'does-not-exist.png']))
        ->assertRedirect(route('home'));
});

test('user without view permission cannot view asset signature', function () {
    $actionlog = Actionlog::factory()->acceptedSignature()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('log.signature.view', ['filename' => $actionlog->accept_signature]))
        ->assertForbidden();
});

test('user with asset view permission can view asset signature', function () {
    $asset = Asset::factory()->create();
    $actionlog = Actionlog::factory()->create([
        'action_type' => 'accepted',
        'item_id' => $asset->id,
        'item_type' => Asset::class,
        'accept_signature' => 'test-asset-sig-'.uniqid().'.png',
    ]);

    $this->actingAs(User::factory()->viewAssets()->create())
        ->get(route('log.signature.view', ['filename' => $actionlog->accept_signature]))
        ->assertOk();
});

test('user with asset view permission cannot view license signature', function () {
    $license = License::factory()->create();
    $actionlog = Actionlog::factory()->create([
        'action_type' => 'accepted',
        'item_id' => $license->id,
        'item_type' => License::class,
        'accept_signature' => 'test-license-sig-'.uniqid().'.png',
    ]);

    $this->actingAs(User::factory()->viewAssets()->create())
        ->get(route('log.signature.view', ['filename' => $actionlog->accept_signature]))
        ->assertForbidden();
});

test('user with license view permission can view license signature', function () {
    $license = License::factory()->create();
    $actionlog = Actionlog::factory()->create([
        'action_type' => 'accepted',
        'item_id' => $license->id,
        'item_type' => License::class,
        'accept_signature' => 'test-license-sig-'.uniqid().'.png',
    ]);

    $this->actingAs(User::factory()->viewLicenses()->create())
        ->get(route('log.signature.view', ['filename' => $actionlog->accept_signature]))
        ->assertOk();
});
