<?php

namespace Tests\Unit\Notifications;

use App\Models\Asset;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\LicenseSeat;
use App\Models\User;
use App\Notifications\CheckinAssetNotification;
use App\Notifications\CheckinComponentNotification;
use App\Notifications\CheckinLicenseSeatNotification;
use App\Notifications\CheckoutComponentNotification;
use App\Notifications\CheckoutConsumableNotification;
use App\Notifications\CheckoutLicenseSeatNotification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Recorre via() de varias notificaciones bajo cada webhook (google/microsoft/none),
 * cubriendo las ramas de seleccion de canal que faltaban.
 */
class NotificationViaBranchesTest extends TestCase
{
    public static function webhookProvider(): array
    {
        return [
            'google'    => ['google'],
            'microsoft' => ['microsoft'],
            'none'      => [''],
            'general'   => ['general'],
        ];
    }

    private function configureWebhook(string $selected): void
    {
        $this->settings->set([
            'webhook_selected' => $selected,
            'webhook_endpoint' => 'https://example.webhook.office.com/hook',
        ]);
    }

    #[DataProvider('webhookProvider')]
    public function test_via_branches_for_checkout_and_checkin(string $selected): void
    {
        $this->configureWebhook($selected);
        $admin = User::factory()->create();
        $target = User::factory()->create();

        $notifications = [
            new CheckinAssetNotification(Asset::factory()->create(), $target, $admin, 'n'),
            new CheckoutComponentNotification(Component::factory()->create(), $target, $admin, null, 'n'),
            new CheckinComponentNotification(Component::factory()->create(), $target, $admin, 'n'),
            new CheckoutConsumableNotification(Consumable::factory()->create(), $target, $admin, null, 'n'),
            new CheckoutLicenseSeatNotification(LicenseSeat::factory()->create(), $target, $admin, null, 'n'),
            new CheckinLicenseSeatNotification(LicenseSeat::factory()->create(), $target, $admin, 'n'),
        ];

        foreach ($notifications as $n) {
            $this->assertIsArray($n->via());
        }
    }
}
