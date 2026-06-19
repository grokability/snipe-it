<?php

namespace Tests\Unit\Mail;

use App\Mail\BulkAssetCheckoutMail;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * Cubre BulkAssetCheckoutMail (antes 0%): construccion + envelope()/content()
 * que ejercitan los helpers privados (subject, introduction, acceptance, eula).
 */
class BulkAssetCheckoutMailTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function mailFor(Collection $assets, $target): BulkAssetCheckoutMail
    {
        return new BulkAssetCheckoutMail(
            assets: $assets,
            target: $target,
            admin: User::factory()->create(),
            checkout_at: now()->toDateString(),
            expected_checkin: now()->addWeek()->toDateString(),
            note: 'checkout masivo',
        );
    }

    public function test_single_asset_to_user(): void
    {
        $assets = Asset::factory()->count(1)->create();
        $mail = $this->mailFor($assets, User::factory()->create());

        $this->assertInstanceOf(Envelope::class, $mail->envelope());
        $this->assertInstanceOf(Content::class, $mail->content());
        $this->assertSame([], $mail->attachments());
    }

    public function test_multiple_assets_to_location(): void
    {
        $assets = Asset::factory()->count(3)->create();
        $mail = $this->mailFor($assets, Location::factory()->create());

        $envelope = $mail->envelope();

        // Con >1 asset el subject usa la variante de conteo.
        $this->assertNotEmpty($envelope->subject);
        $this->assertInstanceOf(Content::class, $mail->content());
    }

    public function test_assets_requiring_acceptance(): void
    {
        $assets = Asset::factory()->count(2)->requiresAcceptance()->create();
        $mail = $this->mailFor($assets, User::factory()->create());

        $this->assertTrue($mail->requires_acceptance);
        // content() ejecuta getRequiresAcceptanceInfo/Prompt en la rama true.
        $this->assertInstanceOf(Content::class, $mail->content());
    }
}
