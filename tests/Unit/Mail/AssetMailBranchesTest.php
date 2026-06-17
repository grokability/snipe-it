<?php

namespace Tests\Unit\Mail;

use App\Mail\CheckinAssetMail;
use App\Mail\CheckoutAssetMail;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Tests\TestCase;

/**
 * Cubre las ramas de destino (User/Asset/Location) y aceptacion de los
 * mailables de checkout/checkin de activos.
 */
class AssetMailBranchesTest extends TestCase
{
    private function assertBuilds(Mailable $mail): void
    {
        $this->assertNotNull($mail->envelope());
        $this->assertNotNull($mail->content());
        $this->assertIsArray($mail->attachments());
    }

    public function test_checkout_to_location_target(): void
    {
        $mail = new CheckoutAssetMail(
            Asset::factory()->create(),
            Location::factory()->create(),
            User::factory()->create(),
            null,
            'note'
        );

        $this->assertBuilds($mail);
    }

    public function test_checkout_to_asset_target(): void
    {
        $mail = new CheckoutAssetMail(
            Asset::factory()->create(),
            Asset::factory()->create(),
            User::factory()->create(),
            null,
            'note'
        );

        $this->assertBuilds($mail);
    }

    public function test_checkout_first_time_vs_resend(): void
    {
        $asset = Asset::factory()->create();
        $target = User::factory()->create();
        $admin = User::factory()->create();

        $this->assertBuilds(new CheckoutAssetMail($asset, $target, $admin, null, 'n', true));
        $this->assertBuilds(new CheckoutAssetMail($asset, $target, $admin, null, 'n', false));
    }

    public function test_checkin_to_location_target(): void
    {
        $mail = new CheckinAssetMail(
            Asset::factory()->create(),
            Location::factory()->create(),
            User::factory()->create(),
            'note'
        );

        $this->assertBuilds($mail);
    }
}
