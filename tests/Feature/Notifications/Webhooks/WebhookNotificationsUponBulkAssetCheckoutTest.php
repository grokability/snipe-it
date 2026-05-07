<?php

namespace Tests\Feature\Notifications\Webhooks;

use App\Models\Asset;
use App\Models\User;
use App\Notifications\CheckoutAssetNotification;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('notifications')]
class WebhookNotificationsUponBulkAssetCheckoutTest extends TestCase
{
    public function test_webbook_is_sent_upon_bulk_asset_checkout()
    {
        Notification::fake();

        $this->settings->enableSlackWebhook();

        $assets = Asset::factory()->count(2)->create();

        $this->actingAs(User::factory()->checkoutAssets()->viewAssets()->create())
            ->followingRedirects()
            ->post(route('hardware.bulkcheckout.store'), [
                'selected_assets' => $assets->pluck('id')->toArray(),
                'checkout_to_type' => 'user',
                'assigned_user' => User::factory()->create()->id,
                'assigned_asset' => null,
                'checkout_at' => now()->subWeek()->format('Y-m-d'),
                'expected_checkin' => now()->addWeek()->format('Y-m-d'),
                'note' => null,
            ])
            ->assertOk();

        $this->assertSlackNotificationSent(CheckoutAssetNotification::class);
        Notification::assertSentTimes(CheckoutAssetNotification::class, 2);
    }
}
