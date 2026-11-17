<?php

namespace Tests\Feature\QrCode;

use App\Models\Asset;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Regression coverage for #19919. Each entity view page loads the QR
 * code as an <img src>, so a request to the QR route arrives right
 * after the entity view request. Laravel's StartSession middleware
 * stores every GET URL as the session "previous URL" by default. On
 * a webserver that strips the Referer header (Referrer-Policy:
 * no-referrer under cPanel's default, for example), url()->previous()
 * on the subsequent edit page has no Referer to prefer and falls back
 * to the session-recorded last GET, which was the QR URL. "Go to
 * previous page" after an edit then redirected the operator to the
 * raw PNG endpoint instead of the asset view page.
 *
 * The QrCodeController now sets X-Requested-With: XMLHttpRequest on
 * the request so StartSession::storeCurrentUrl() short-circuits and
 * leaves the previous-URL slot alone, keeping it pointed at the real
 * view page.
 */
class QrCodeSessionPreviousUrlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Setting::factory()->create(['label2_2d_type' => 'QRCODE']);
        Storage::fake('public');
    }

    public function test_qr_code_request_does_not_overwrite_session_previous_url(): void
    {
        $viewer = User::factory()->viewAssets()->create();
        $asset = Asset::factory()->create();

        $assetViewUrl = route('hardware.show', $asset->id);
        $qrUrl = route('qr_code/common', ['object_type' => 'hardware', 'id' => $asset->id]);

        $this->actingAs($viewer);

        // Simulate the browser's two-request sequence on a view page.
        // First request lands on the asset view page and should be
        // recorded as the session previous URL.
        $this->get($assetViewUrl)->assertOk();

        $this->assertSame(
            $assetViewUrl,
            session()->previousUrl(),
            'The asset view page URL should be recorded as the session previous URL.',
        );

        // Second request fetches the QR image. StartSession normally
        // would overwrite the previous URL with this QR endpoint. The
        // controller opts the route out so the view page URL stays
        // put.
        $this->get($qrUrl)->assertOk();

        $this->assertSame(
            $assetViewUrl,
            session()->previousUrl(),
            'The QR image request must not overwrite the session previous URL. "Go to previous page" after editing would otherwise land on the raw PNG endpoint.',
        );
    }
}
