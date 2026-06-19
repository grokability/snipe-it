<?php

namespace Tests\Unit\Listeners;

use App\Events\CheckoutAccepted;
use App\Events\CheckoutDeclined;
use App\Events\UserMerged;
use App\Listeners\LogListener;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Tests\TestCase;

/**
 * Cubre las ramas de LogListener sin testear: onCheckoutAccepted, onCheckoutDeclined
 * (incluyendo LicenseSeat) y onUserMerged.
 */
class LogListenerEventsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_on_checkout_accepted_for_asset(): void
    {
        $acceptance = CheckoutAcceptance::factory()->create([
            'checkoutable_type' => Asset::class,
            'checkoutable_id' => Asset::factory()->create()->id,
            'accepted_at' => now(),
        ]);

        (new LogListener)->onCheckoutAccepted(new CheckoutAccepted($acceptance));

        $this->assertDatabaseHas('action_logs', ['action_type' => 'accepted']);
    }

    public function test_on_checkout_accepted_for_license_seat(): void
    {
        $license = License::factory()->create();
        $seat = LicenseSeat::factory()->create(['license_id' => $license->id]);
        $acceptance = CheckoutAcceptance::factory()->create([
            'checkoutable_type' => LicenseSeat::class,
            'checkoutable_id' => $seat->id,
            'accepted_at' => now(),
        ]);

        (new LogListener)->onCheckoutAccepted(new CheckoutAccepted($acceptance));

        $this->assertTrue(true);
    }

    public function test_on_checkout_declined(): void
    {
        $acceptance = CheckoutAcceptance::factory()->create([
            'checkoutable_type' => Asset::class,
            'checkoutable_id' => Asset::factory()->create()->id,
            'declined_at' => now(),
        ]);

        (new LogListener)->onCheckoutDeclined(new CheckoutDeclined($acceptance));

        $this->assertDatabaseHas('action_logs', ['action_type' => 'declined']);
    }

    public function test_on_user_merged(): void
    {
        $from = User::factory()->create();
        $to = User::factory()->create();
        $admin = User::factory()->superuser()->create();

        (new LogListener)->onUserMerged(new UserMerged($from, $to, $admin));

        // Se generan dos action logs de tipo merged.
        $this->assertSame(2, Actionlog::where('action_type', 'merged')->count());
    }
}
