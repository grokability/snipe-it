<?php

namespace Tests\Unit\Notifications;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\LicenseSeat;
use App\Models\User;
use App\Notifications\CheckinAccessoryNotification;
use App\Notifications\CheckinAssetNotification;
use App\Notifications\CheckinComponentNotification;
use App\Notifications\CheckinLicenseSeatNotification;
use App\Notifications\CheckoutAccessoryNotification;
use App\Notifications\CheckoutComponentNotification;
use App\Notifications\CheckoutConsumableNotification;
use App\Notifications\CheckoutLicenseSeatNotification;
use Tests\TestCase;

/**
 * Recorre via() + toSlack()/toMicrosoftTeams()/toGoogleChat() de las
 * notificaciones de checkout/checkin de cada tipo de item.
 */
class NotificationChannelsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->settings->set([
            'webhook_selected' => 'slack',
            'webhook_endpoint' => 'https://example.webhook.office.com/hook',
            'webhook_channel' => '#it',
            'webhook_botname' => 'Snipe-Bot',
        ]);
    }

    private function assertAllChannels($notification): void
    {
        $this->assertIsArray($notification->via());
        $this->assertNotNull($notification->toSlack());
        $this->assertNotNull($notification->toMicrosoftTeams());
        $this->assertNotNull($notification->toGoogleChat());
    }

    public function test_checkin_asset_notification(): void
    {
        $n = new CheckinAssetNotification(Asset::factory()->create(), User::factory()->create(), User::factory()->create(), 'note');
        $this->assertAllChannels($n);
    }

    public function test_checkout_accessory_notification(): void
    {
        $accessory = Accessory::factory()->create();
        $accessory->checkout_qty = 1;
        $n = new CheckoutAccessoryNotification($accessory, User::factory()->create(), User::factory()->create(), null, 'note');
        $this->assertAllChannels($n);
        $this->assertNotNull($n->toMail());
    }

    public function test_checkin_accessory_notification(): void
    {
        $n = new CheckinAccessoryNotification(Accessory::factory()->create(), User::factory()->create(), User::factory()->create(), 'note');
        $this->assertAllChannels($n);
    }

    public function test_checkout_consumable_notification(): void
    {
        $n = new CheckoutConsumableNotification(Consumable::factory()->create(), User::factory()->create(), User::factory()->create(), null, 'note');
        $this->assertAllChannels($n);
    }

    public function test_checkout_component_notification(): void
    {
        $n = new CheckoutComponentNotification(Component::factory()->create(), User::factory()->create(), User::factory()->create(), null, 'note');
        $this->assertAllChannels($n);
    }

    public function test_checkin_component_notification(): void
    {
        $n = new CheckinComponentNotification(Component::factory()->create(), User::factory()->create(), User::factory()->create(), 'note');
        $this->assertAllChannels($n);
    }

    public function test_checkout_license_seat_notification(): void
    {
        $seat = LicenseSeat::factory()->create();
        $n = new CheckoutLicenseSeatNotification($seat, User::factory()->create(), User::factory()->create(), null, 'note');
        $this->assertAllChannels($n);
    }

    public function test_checkin_license_seat_notification(): void
    {
        $seat = LicenseSeat::factory()->create();
        $n = new CheckinLicenseSeatNotification($seat, User::factory()->create(), User::factory()->create(), 'note');
        $this->assertAllChannels($n);
    }
}
