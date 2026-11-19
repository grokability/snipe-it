<?php

use App\Models\Asset;
use App\Models\Setting;
use App\Models\User;

beforeEach(function () {
    // QR generation is a no-op when label2_2d_type === 'none'.
    // Force a real value so the controller reaches the auth check
    // (rather than short-circuiting with `return false` on the
    // settings guard). The test never actually renders a barcode
    // because the auth / policy branches all abort before then.
    Setting::factory()->create(['label2_2d_type' => 'QRCODE']);
});

test('anonymous request redirects to login for both existing and missing ids', function () {
    $asset = Asset::factory()->create();

    // No differential possible if both requests are absorbed by
    // the `auth` middleware and produce identical redirects.
    $existing = $this->get(route('qr_code/common', ['object_type' => 'assets', 'id' => $asset->id]));
    $missing = $this->get(route('qr_code/common', ['object_type' => 'assets', 'id' => $asset->id + 999_999]));

    $existing->assertRedirect(route('login'));
    $missing->assertRedirect(route('login'));
    expect($missing->status())->toBe($existing->status(), 'Anonymous responses must not vary by whether the id exists.');
});

test('authenticated but unauthorized user gets uniform 404 regardless of id existence', function () {
    // Viewer with a fully empty permission blob, so the `view`
    // policy denies for every asset regardless of ownership.
    $viewer = User::factory()->create();
    $asset = Asset::factory()->create();

    $existing = $this->actingAs($viewer)->get(route('qr_code/common', ['object_type' => 'assets', 'id' => $asset->id]));
    $missing = $this->actingAs($viewer)->get(route('qr_code/common', ['object_type' => 'assets', 'id' => $asset->id + 999_999]));

    $existing->assertStatus(404);
    $missing->assertStatus(404);
    expect($missing->getContent())->toBe($existing->getContent(), 'Existing-but-denied and missing responses must be byte-identical so no oracle exists.');
});

test('unknown object type returns the same uniform 404', function () {
    // The route's regex-constrained `object_type` param would
    // normally 404 at the router for a garbage type. Pin the
    // behavior explicitly so a future route-regex loosening does
    // not silently re-expose an oracle via "unknown type" branch.
    $viewer = User::factory()->create();

    $bogus = $this->actingAs($viewer)->get('/nonsense/1/qr_code');
    $bogus->assertStatus(404);
});
