<?php

namespace Tests\Feature\QrCode;

use App\Http\Controllers\QrCodeController;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssetApiQrUrlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->settings->set(['qr_code' => '1', 'label2_2d_type' => 'QRCODE']);
    }

    public function test_api_qr_url_points_at_the_file_the_qr_code_route_writes(): void
    {
        // Regression for #19156. The API `qr` field linked to
        // qr-{asset_tag}-{id}.png, but the QR code route caches the PNG as
        // qr-hardware-{id}.png, so the API URL never resolved.
        $user = User::factory()->superuser()->create();
        $asset = Asset::factory()->create();

        $this->actingAs($user)
            ->get(route('qr_code/common', ['object_type' => 'hardware', 'id' => $asset->id]))
            ->assertOk();

        $key = QrCodeController::cacheKey('hardware', $asset->id);
        $this->assertTrue(Storage::disk('public')->exists($key));

        $this->actingAsForApi($user)
            ->getJson(route('api.assets.show', $asset))
            ->assertOk()
            ->assertJsonPath('qr', Storage::disk('public')->url($key));
    }
}
