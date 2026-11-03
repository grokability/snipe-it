<?php

namespace Tests\Feature\CheckoutAcceptances;

use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AcceptanceSignatureImageValidationTest extends TestCase
{
    private function pendingAcceptance(User $target): CheckoutAcceptance
    {
        $asset = Asset::factory()->assignedToUser($target)->create();

        return CheckoutAcceptance::factory()->pending()->for($asset, 'checkoutable')->create([
            'assigned_to_id' => $target->id,
        ]);
    }

    private function enableRequiredSignatures(): void
    {
        $settings = Setting::query()->firstOrFail();
        $settings->require_accept_signature = 1;
        $settings->save();
        Setting::$_cache = null;
    }

    private function tinyPng(int $width = 2, int $height = 2): string
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
    private function pngWithIhdrDimensions(int $width, int $height): string
    {
        $signature = "\x89PNG\r\n\x1a\n";
        $ihdrData = pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0);
        $ihdrChunk = pack('N', 13).'IHDR'.$ihdrData.pack('N', crc32('IHDR'.$ihdrData));
        $iendChunk = pack('N', 0).'IEND'.pack('N', crc32('IEND'));

        return $signature.$ihdrChunk.$iendChunk;
    }

    public function test_signature_output_with_non_image_bytes_is_rejected_and_nothing_is_written(): void
    {
        Storage::fake();
        $this->enableRequiredSignatures();

        $target = User::factory()->create();
        $acceptance = $this->pendingAcceptance($target);

        $marker = 'SNIPE-CAND-11-NOT-A-PNG';
        $payload = [
            'asset_acceptance' => 'accepted',
            'signature_output' => 'data:image/png;base64,'.base64_encode($marker),
        ];

        $this->actingAs($target)
            ->post(route('account.store-acceptance', $acceptance), $payload)
            ->assertSessionHas('error');

        $acceptance->refresh();
        $this->assertNull($acceptance->accepted_at, 'A rejected signature must not finalize the acceptance.');

        $written = Storage::disk()->files('private_uploads/signatures');
        $this->assertEmpty($written, 'A rejected signature must not land any file under signatures/.');
    }

    public function test_signature_output_with_oversized_declared_dimensions_is_rejected_before_decode(): void
    {
        Storage::fake();
        $this->enableRequiredSignatures();

        $target = User::factory()->create();
        $acceptance = $this->pendingAcceptance($target);

        // A PNG that declares 10000x10000 in its IHDR but is only a few
        // dozen bytes on the wire. getimagesizefromstring reports the
        // declared dimensions, the cap trips, and the request is
        // rejected without imagecreatefromstring ever running.
        $bombHeader = $this->pngWithIhdrDimensions(10000, 10000);
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
        $this->assertSame(
            trans('general.signature_too_large', ['max' => 6000]),
            $response->getSession()->get('error'),
        );

        $acceptance->refresh();
        $this->assertNull($acceptance->accepted_at);
        $this->assertEmpty(Storage::disk()->files('private_uploads/signatures'));
    }

    public function test_signature_dimension_cap_is_operator_tunable_via_config(): void
    {
        // Operators on 8K / scaled high-DPI monitors have been reported
        // to produce legitimate signatures above the 6000 default.
        // Lower the ceiling to prove the config knob is actually read:
        // a tiny real PNG that passes the default 6000 ceiling (and
        // succeeds end-to-end today) now trips the dimension cap when
        // the operator-tunable ceiling is set below its declared size.
        // Covers the inverse direction of the oversized-dimensions test
        // which already proves the default rejects 10000x10000.
        Storage::fake();
        $this->enableRequiredSignatures();
        config(['app.acceptance_signature_max_dimension' => 1]);

        $target = User::factory()->create();
        $acceptance = $this->pendingAcceptance($target);

        // A 2x2 real PNG. Under the default 6000 ceiling this would
        // pass the dimension check and reach the flatten re-encode.
        // With the override at 1px it must be rejected at the
        // dimension cap before any pixel buffer is allocated.
        $payload = [
            'asset_acceptance' => 'accepted',
            'signature_output' => 'data:image/png;base64,'.base64_encode($this->tinyPng()),
        ];

        $response = $this->actingAs($target)
            ->post(route('account.store-acceptance', $acceptance), $payload)
            ->assertSessionHas('error');

        $this->assertSame(
            trans('general.signature_too_large', ['max' => 1]),
            $response->getSession()->get('error'),
        );

        $acceptance->refresh();
        $this->assertNull($acceptance->accepted_at);
        $this->assertEmpty(Storage::disk()->files('private_uploads/signatures'));
    }

    public function test_signature_output_exceeding_2mb_cap_is_rejected_by_validator(): void
    {
        Storage::fake();
        $this->enableRequiredSignatures();

        $target = User::factory()->create();
        $acceptance = $this->pendingAcceptance($target);

        $payload = [
            'asset_acceptance' => 'accepted',
            'signature_output' => 'data:image/png;base64,'.str_repeat('A', 2_100_000),
        ];

        $this->actingAs($target)
            ->post(route('account.store-acceptance', $acceptance), $payload)
            ->assertSessionHasErrors('signature_output');

        $acceptance->refresh();
        $this->assertNull($acceptance->accepted_at);
    }

    /**
     * Reporter-identified residual (Wojciech Ciemski post-fd61abe
     * patch): a header-only PNG (valid signature + IHDR + IEND, no
     * IDAT) passes getimagesizefromstring's shallow check,
     * imagecreatefromstring fails on it, and the earlier shape then
     * kept the original attacker bytes and wrote them under a .png
     * filename. Verify the submission is now rejected and no file is
     * written.
     */
    public function test_signature_output_with_valid_header_but_no_idat_is_rejected(): void
    {
        Storage::fake();
        $this->enableRequiredSignatures();

        $target = User::factory()->create();
        $acceptance = $this->pendingAcceptance($target);

        // Valid signature + IHDR + IEND. No IDAT, so the decoder can't
        // reconstruct a raster even though getimagesizefromstring is
        // satisfied by the IHDR alone. Attacker could tack arbitrary
        // bytes on the end up to the size cap.
        $headerOnlyPng = $this->pngWithIhdrDimensions(2, 2).'ATTACKER-CONTROLLED-TRAILING-BYTES';

        $this->actingAs($target)
            ->post(route('account.store-acceptance', $acceptance), [
                'asset_acceptance' => 'accepted',
                'signature_output' => 'data:image/png;base64,'.base64_encode($headerOnlyPng),
            ])
            ->assertSessionHas('error');

        $acceptance->refresh();
        $this->assertNull($acceptance->accepted_at, 'A header-only PNG must not finalize the acceptance.');
        $this->assertEmpty(Storage::disk()->files('private_uploads/signatures'), 'A header-only PNG must not land any file under signatures/.');
    }

    /**
     * Reporter-identified residual (Wojciech Ciemski post-fd61abe
     * patch): the IMAGETYPE check used to accept any image type
     * getimagesizefromstring recognized, including JPEG / GIF / BMP /
     * WEBP. A valid JPEG then landed under a .png filename.
     */
    public function test_signature_output_with_non_png_image_type_is_rejected(): void
    {
        Storage::fake();
        $this->enableRequiredSignatures();

        $target = User::factory()->create();
        $acceptance = $this->pendingAcceptance($target);

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
        $this->assertNull($acceptance->accepted_at, 'A JPEG masquerading as a PNG must not finalize the acceptance.');
        $this->assertEmpty(Storage::disk()->files('private_uploads/signatures'), 'A non-PNG image type must not land any file under signatures/.');
    }

    public function test_legitimate_tiny_png_signature_still_finalizes_acceptance(): void
    {
        // Companion: the fix must not break the happy path. A real tiny
        // PNG from the signaturepad canvas should continue to validate,
        // flatten, store, and finalize.
        Storage::fake();
        $this->enableRequiredSignatures();

        $target = User::factory()->create();
        $acceptance = $this->pendingAcceptance($target);

        $payload = [
            'asset_acceptance' => 'accepted',
            'signature_output' => 'data:image/png;base64,'.base64_encode($this->tinyPng()),
        ];

        $this->actingAs($target)
            ->post(route('account.store-acceptance', $acceptance), $payload)
            ->assertRedirectToRoute('account.accept')
            ->assertSessionHas('success');

        $acceptance->refresh();
        $this->assertNotNull($acceptance->accepted_at, 'A legitimate PNG signature must still finalize the acceptance.');
        $this->assertCount(1, Storage::disk()->files('private_uploads/signatures'));
    }
}
