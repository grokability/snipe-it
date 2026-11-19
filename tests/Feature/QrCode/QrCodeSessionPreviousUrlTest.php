<?php

use App\Models\Asset;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Setting::factory()->create(['label2_2d_type' => 'QRCODE']);
    Storage::fake('public');
});

test('qr code request does not overwrite session previous url', function () {
    $viewer = User::factory()->viewAssets()->create();
    $asset = Asset::factory()->create();

    $assetViewUrl = route('hardware.show', $asset->id);
    $qrUrl = route('qr_code/common', ['object_type' => 'hardware', 'id' => $asset->id]);

    $this->actingAs($viewer);

    // Simulate the browser's two-request sequence on a view page.
    // First request lands on the asset view page and should be
    // recorded as the session previous URL.
    $this->get($assetViewUrl)->assertOk();

    expect(session()->previousUrl())->toBe($assetViewUrl, 'The asset view page URL should be recorded as the session previous URL.');

    // Second request fetches the QR image. StartSession normally
    // would overwrite the previous URL with this QR endpoint. The
    // controller opts the route out so the view page URL stays
    // put.
    $this->get($qrUrl)->assertOk();

    expect(session()->previousUrl())->toBe($assetViewUrl, 'The QR image request must not overwrite the session previous URL. "Go to previous page" after editing would otherwise land on the raw PNG endpoint.');
});
