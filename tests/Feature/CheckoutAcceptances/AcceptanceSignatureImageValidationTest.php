<?php

use App\Models\CheckoutAcceptance;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

function tinyPng(int $width = 2, int $height = 2): string
{
    $canvas = imagecreatetruecolor($width, $height);
    ob_start();
    imagepng($canvas);
    $bytes = (string) ob_get_clean();

    return $bytes;
}

/**
 * Craft a PNG whose IHDR declares the given dimensions. Used to
 * exercise the dimension cap without actually allocating a huge
 * pixel buffer in the test itself. getimagesizefromstring reads
 * the IHDR header and reports these declared dimensions, so the
 * cap triggers before any decoder opens the raster.
 */
function pngWithIhdrDimensions(int $width, int $height): string
{
    $signature = "\x89PNG\r\n\x1a\n";
    $ihdrData = pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0);
    $ihdrChunk = pack('N', 13).'IHDR'.$ihdrData.pack('N', crc32('IHDR'.$ihdrData));
    $iendChunk = pack('N', 0).'IEND'.pack('N', crc32('IEND'));

    return $signature.$ihdrChunk.$iendChunk;
}

test('signature output with non image bytes is rejected and nothing is written', function () {
    Storage::fake();
    $this->settings->enableRequiredSignatures();

    $target = User::factory()->create();
    $acceptance = CheckoutAcceptance::factory()->pending()->for($target, 'assignedTo')->create();

    $marker = 'SNIPE-CAND-11-NOT-A-PNG';
    $payload = [
        'asset_acceptance' => 'accepted',
        'signature_output' => 'data:image/png;base64,'.base64_encode($marker),
    ];

    $this->actingAs($target)
        ->post(route('account.store-acceptance', $acceptance), $payload)
        ->assertSessionHas('error');

    $acceptance->refresh();
    expect($acceptance->accepted_at)->toBeNull('A rejected signature must not finalize the acceptance.');

    $written = Storage::disk()->files('private_uploads/signatures');
    expect($written)->toBeEmpty('A rejected signature must not land any file under signatures/.');
});

test('signature output with oversized declared dimensions is rejected before decode', function () {
    Storage::fake();
    $this->settings->enableRequiredSignatures();

    $target = User::factory()->create();
    $acceptance = CheckoutAcceptance::factory()->pending()->for($target, 'assignedTo')->create();

    // A PNG that declares 10000x10000 in its IHDR but is only a few
    // dozen bytes on the wire. getimagesizefromstring reports the
    // declared dimensions, the cap trips, and the request is
    // rejected without imagecreatefromstring ever running.
    $bombHeader = pngWithIhdrDimensions(10000, 10000);
    $payload = [
        'asset_acceptance' => 'accepted',
        'signature_output' => 'data:image/png;base64,'.base64_encode($bombHeader),
    ];

    $response = $this->actingAs($target)
        ->post(route('account.store-acceptance', $acceptance), $payload)
        ->assertSessionHas('error');

    // Assert the dimension-cap-specific message (not the generic
    // "no signature detected" shitty_browser string) so the user
    // sees why their legitimate browser is being rejected and what
    // to do about it.
    expect($response->getSession()->get('error'))->toBe(trans('general.signature_too_large', ['max' => 6000]));

    $acceptance->refresh();
    expect($acceptance->accepted_at)->toBeNull();
    expect(Storage::disk()->files('private_uploads/signatures'))->toBeEmpty();
});

test('signature dimension cap is operator tunable via config', function () {
    // Operators on 8K / scaled high-DPI monitors have been reported
    // to produce legitimate signatures above the 6000 default.
    // Lower the ceiling to prove the config knob is actually read:
    // a tiny real PNG that passes the default 6000 ceiling (and
    // succeeds end-to-end today) now trips the dimension cap when
    // the operator-tunable ceiling is set below its declared size.
    // Covers the inverse direction of the oversized-dimensions test
    // which already proves the default rejects 10000x10000.
    Storage::fake();
    $this->settings->enableRequiredSignatures();
    config(['app.acceptance_signature_max_dimension' => 1]);

    $target = User::factory()->create();
    $acceptance = CheckoutAcceptance::factory()->pending()->for($target, 'assignedTo')->create();

    // A 2x2 real PNG. Under the default 6000 ceiling this would
    // pass the dimension check and reach the flatten re-encode.
    // With the override at 1px it must be rejected at the
    // dimension cap before any pixel buffer is allocated.
    $payload = [
        'asset_acceptance' => 'accepted',
        'signature_output' => 'data:image/png;base64,'.base64_encode(tinyPng()),
    ];

    $response = $this->actingAs($target)
        ->post(route('account.store-acceptance', $acceptance), $payload)
        ->assertSessionHas('error');

    expect($response->getSession()->get('error'))->toBe(trans('general.signature_too_large', ['max' => 1]));

    $acceptance->refresh();
    expect($acceptance->accepted_at)->toBeNull();
    expect(Storage::disk()->files('private_uploads/signatures'))->toBeEmpty();
});

test('signature output exceeding 2mb cap is rejected by validator', function () {
    Storage::fake();
    $this->settings->enableRequiredSignatures();

    $target = User::factory()->create();
    $acceptance = CheckoutAcceptance::factory()->pending()->for($target, 'assignedTo')->create();

    $payload = [
        'asset_acceptance' => 'accepted',
        'signature_output' => 'data:image/png;base64,'.str_repeat('A', 2_100_000),
    ];

    $this->actingAs($target)
        ->post(route('account.store-acceptance', $acceptance), $payload)
        ->assertSessionHasErrors('signature_output');

    $acceptance->refresh();
    expect($acceptance->accepted_at)->toBeNull();
});

test('signature output with valid header but no idat is rejected', function () {
    Storage::fake();
    $this->settings->enableRequiredSignatures();

    $target = User::factory()->create();
    $acceptance = CheckoutAcceptance::factory()->pending()->for($target, 'assignedTo')->create();

    // Valid signature + IHDR + IEND. No IDAT, so the decoder can't
    // reconstruct a raster even though getimagesizefromstring is
    // satisfied by the IHDR alone. Attacker could tack arbitrary
    // bytes on the end up to the size cap.
    $headerOnlyPng = pngWithIhdrDimensions(2, 2).'ATTACKER-CONTROLLED-TRAILING-BYTES';

    $this->actingAs($target)
        ->post(route('account.store-acceptance', $acceptance), [
            'asset_acceptance' => 'accepted',
            'signature_output' => 'data:image/png;base64,'.base64_encode($headerOnlyPng),
        ])
        ->assertSessionHas('error');

    $acceptance->refresh();
    expect($acceptance->accepted_at)->toBeNull('A header-only PNG must not finalize the acceptance.');
    expect(Storage::disk()->files('private_uploads/signatures'))->toBeEmpty('A header-only PNG must not land any file under signatures/.');
});

test('signature output with non png image type is rejected', function () {
    Storage::fake();
    $this->settings->enableRequiredSignatures();

    $target = User::factory()->create();
    $acceptance = CheckoutAcceptance::factory()->pending()->for($target, 'assignedTo')->create();

    // Real JPEG (not a PNG with lying MIME, actual JPEG bytes).
    // getimagesizefromstring returns IMAGETYPE_JPEG, the strict
    // IMAGETYPE_PNG check now fires, and the request is rejected
    // before flattening.
    $canvas = imagecreatetruecolor(16, 16);
    ob_start();
    imagejpeg($canvas);
    $jpegBytes = (string) ob_get_clean();
    $this->actingAs($target)
        ->post(route('account.store-acceptance', $acceptance), [
            'asset_acceptance' => 'accepted',
            'signature_output' => 'data:image/png;base64,'.base64_encode($jpegBytes),
        ])
        ->assertSessionHas('error');

    $acceptance->refresh();
    expect($acceptance->accepted_at)->toBeNull('A JPEG masquerading as a PNG must not finalize the acceptance.');
    expect(Storage::disk()->files('private_uploads/signatures'))->toBeEmpty('A non-PNG image type must not land any file under signatures/.');
});

test('legitimate tiny png signature still finalizes acceptance', function () {
    // Companion: the fix must not break the happy path. A real tiny
    // PNG from the signaturepad canvas should continue to validate,
    // flatten, store, and finalize.
    Storage::fake();
    $this->settings->enableRequiredSignatures();

    $target = User::factory()->create();
    $acceptance = CheckoutAcceptance::factory()->pending()->for($target, 'assignedTo')->create();

    $payload = [
        'asset_acceptance' => 'accepted',
        'signature_output' => 'data:image/png;base64,'.base64_encode(tinyPng()),
    ];

    $this->actingAs($target)
        ->post(route('account.store-acceptance', $acceptance), $payload)
        ->assertRedirectToRoute('account.accept')
        ->assertSessionHas('success');

    $acceptance->refresh();
    expect($acceptance->accepted_at)->not->toBeNull('A legitimate PNG signature must still finalize the acceptance.');
    expect(Storage::disk()->files('private_uploads/signatures'))->toHaveCount(1);
});
