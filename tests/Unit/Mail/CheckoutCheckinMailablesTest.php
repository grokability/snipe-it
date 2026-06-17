<?php

namespace Tests\Unit\Mail;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\LicenseSeat;
use App\Models\User;
use App\Mail\CheckinAccessoryMail;
use App\Mail\CheckinAssetMail;
use App\Mail\CheckinComponentMail;
use App\Mail\CheckinLicenseMail;
use App\Mail\CheckoutAccessoryMail;
use App\Mail\CheckoutAssetMail;
use App\Mail\CheckoutComponentMail;
use App\Mail\CheckoutConsumableMail;
use App\Mail\CheckoutLicenseMail;
use Illuminate\Mail\Mailable;
use Tests\TestCase;

/**
 * Construye los mailables de checkout/checkin y ejercita envelope(),
 * content() y attachments() (ensamblado del correo, sin render del Blade).
 */
class CheckoutCheckinMailablesTest extends TestCase
{
    private function assertMailableBuilds(Mailable $mail): void
    {
        $this->assertNotNull($mail->envelope());
        $this->assertNotNull($mail->content());
        $this->assertIsArray($mail->attachments());
    }

    public function test_checkout_asset_mail(): void
    {
        $this->assertMailableBuilds(new CheckoutAssetMail(Asset::factory()->create(), User::factory()->create(), User::factory()->create(), null, 'note'));
    }

    public function test_checkin_asset_mail(): void
    {
        $this->assertMailableBuilds(new CheckinAssetMail(Asset::factory()->create(), User::factory()->create(), User::factory()->create(), 'note'));
    }

    public function test_checkout_accessory_mail(): void
    {
        $this->assertMailableBuilds(new CheckoutAccessoryMail(Accessory::factory()->create(), User::factory()->create(), User::factory()->create(), null, 'note'));
    }

    public function test_checkin_accessory_mail(): void
    {
        $this->assertMailableBuilds(new CheckinAccessoryMail(Accessory::factory()->create(), User::factory()->create(), User::factory()->create(), 'note'));
    }

    public function test_checkout_consumable_mail(): void
    {
        $this->assertMailableBuilds(new CheckoutConsumableMail(Consumable::factory()->create(), User::factory()->create(), User::factory()->create(), null, 'note'));
    }

    public function test_checkout_component_mail(): void
    {
        $this->assertMailableBuilds(new CheckoutComponentMail(Component::factory()->create(), User::factory()->create(), User::factory()->create(), null, 'note'));
    }

    public function test_checkin_component_mail(): void
    {
        $this->assertMailableBuilds(new CheckinComponentMail(Component::factory()->create(), User::factory()->create(), User::factory()->create(), 'note'));
    }

    public function test_checkout_license_mail(): void
    {
        $this->assertMailableBuilds(new CheckoutLicenseMail(LicenseSeat::factory()->create(), User::factory()->create(), User::factory()->create(), null, 'note'));
    }

    public function test_checkin_license_mail(): void
    {
        $this->assertMailableBuilds(new CheckinLicenseMail(LicenseSeat::factory()->create(), User::factory()->create(), User::factory()->create(), 'note'));
    }
}
