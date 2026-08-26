<?php

namespace Tests\Feature\Notifications\Webhooks;

use App\Events\CheckoutableCheckedOut;
use App\Listeners\CheckoutableCheckedOutEmailNotification;
use App\Listeners\CheckoutableCheckedOutWebhookNotification;
use App\Mail\CheckoutAssetMail;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\CheckoutAcceptance;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\CheckoutAccessoryNotification;
use App\Notifications\CheckoutAssetNotification;
use App\Notifications\CheckoutComponentNotification;
use App\Notifications\CheckoutConsumableNotification;
use App\Notifications\CheckoutLicenseSeatNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\GoogleChat\GoogleChatMessage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('notifications')]
class IntegrationNotificationsUponCheckoutTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Mail::fake();
    }

    public static function assetCheckoutTargets(): array
    {
        return [
            'Asset checked out to user' => [fn () => User::factory()->create(['email' => null])],
            'Asset checked out to asset' => [fn () => Asset::factory()->laptopMbp()->create()],
            'Asset checked out to location' => [fn () => Location::factory()->create()],
        ];
    }

    public static function checkoutNotificationOrders(): array
    {
        return [
            'discovered listeners' => [null],
            'email first' => [false],
            'webhook first' => [true],
        ];
    }

    #[DataProvider('checkoutNotificationOrders')]
    public function test_checkout_notifications_share_one_acceptance(?bool $webhookFirst): void
    {
        $this->settings->enableSlackWebhook()->disableAdminCC()->disableAdminCCAlways();

        $asset = Asset::factory()->create();
        $asset->model->category->update(['require_acceptance' => true]);
        $user = User::factory()->create();
        $event = new CheckoutableCheckedOut($asset, $user, User::factory()->create(), null);

        if ($webhookFirst === null) {
            event($event);
        } else {
            $emailListener = new CheckoutableCheckedOutEmailNotification;
            $webhookListener = new CheckoutableCheckedOutWebhookNotification;
            $listeners = $webhookFirst
                ? [$webhookListener, $emailListener]
                : [$emailListener, $webhookListener];

            foreach ($listeners as $listener) {
                $listener->handle($event);
            }
        }

        $this->assertDatabaseCount('checkout_acceptances', 1);
        $acceptance = CheckoutAcceptance::firstOrFail();
        $this->assertSame($acceptance->id, $event->checkoutAcceptance?->id);

        Mail::assertSent(CheckoutAssetMail::class, 1);
        Mail::assertSent(CheckoutAssetMail::class, function (CheckoutAssetMail $mail) use ($acceptance, $user): bool {
            return $mail->hasTo($user->email)
                && str_contains($mail->render(), route('account.accept.item', $acceptance));
        });

        Notification::assertSentOnDemandTimes(CheckoutAssetNotification::class, 1);
        Notification::assertSentOnDemand(CheckoutAssetNotification::class, function (CheckoutAssetNotification $notification) use ($acceptance): bool {
            return $notification->acceptance->id === $acceptance->id;
        });
    }

    public static function licenseCheckoutTargets(): array
    {
        return [
            'License checked out to user' => [fn () => User::factory()->create(['email' => null])],
            'License checked out to asset' => [fn () => Asset::factory()->laptopMbp()->create()],
        ];
    }

    public function test_accessory_checkout_sends_slack_notification_when_setting_enabled()
    {
        $this->settings->enableSlackWebhook();

        $this->fireCheckOutEvent(
            Accessory::factory()->create(),
            User::factory()->create(),
        );

        $this->assertSlackNotificationSent(CheckoutAccessoryNotification::class);
    }

    public function test_accessory_checkout_does_not_send_slack_notification_when_setting_disabled()
    {
        $this->settings->disableSlackWebhook();

        $this->fireCheckOutEvent(
            Accessory::factory()->create(),
            User::factory()->create(),
        );

        $this->assertNoSlackNotificationSent(CheckoutAccessoryNotification::class);
    }

    #[DataProvider('assetCheckoutTargets')]
    public function test_asset_checkout_sends_slack_notification_when_setting_enabled($checkoutTarget)
    {
        $this->settings->enableSlackWebhook();

        $this->fireCheckOutEvent(
            Asset::factory()->create(),
            $checkoutTarget(),
        );

        $this->assertSlackNotificationSent(CheckoutAssetNotification::class);
    }

    #[DataProvider('assetCheckoutTargets')]
    public function test_asset_checkout_does_not_send_slack_notification_when_setting_disabled($checkoutTarget)
    {
        $this->settings->disableSlackWebhook();

        $this->fireCheckOutEvent(
            Asset::factory()->create(),
            $checkoutTarget(),
        );

        $this->assertNoSlackNotificationSent(CheckoutAssetNotification::class);
    }

    #[DataProvider('assetCheckoutTargets')]
    public function test_component_checkout_sends_slack_notification_when_setting_enabled($checkoutTarget)
    {
        $this->settings->enableSlackWebhook();
        $component = Component::factory()->create([
            'category_id' => Category::factory()->create([
                'require_acceptance' => false,
                'eula_text' => null,
            ]),
        ]);
        $this->fireCheckOutEvent(
            $component,
            $checkoutTarget(),
        );

        $this->assertSlackNotificationSent(CheckoutComponentNotification::class);
    }

    #[DataProvider('assetCheckoutTargets')]
    public function test_component_checkout_does_not_send_slack_notification_when_setting_disabled($checkoutTarget)
    {
        $this->settings->disableSlackWebhook();
        $component = Component::factory()->create([
            'category_id' => Category::factory()->create([
                'require_acceptance' => false,
                'eula_text' => null,
            ]),
        ]);
        $this->fireCheckOutEvent(
            $component,
            $checkoutTarget(),
        );

        $this->assertNoSlackNotificationSent(CheckoutComponentNotification::class);
    }

    public function test_slack_notification_is_still_sent_when_category_email_is_not_set_to_send_emails()
    {
        $this->settings->enableSlackWebhook();

        $category = Category::factory()->create([
            'checkin_email' => false,
            'eula_text' => null,
            'require_acceptance' => false,
            'use_default_eula' => false,
        ]);
        $assetModel = AssetModel::factory()->for($category)->create();
        $asset = Asset::factory()->for($assetModel, 'model')->create();

        $this->fireCheckOutEvent(
            $asset,
            User::factory()->create(),
        );

        $this->assertSlackNotificationSent(CheckoutAssetNotification::class);
    }

    public function test_asset_checkout_slack_notification_uses_updated_asset_location()
    {
        $this->settings->enableSlackWebhook();

        $previousLocation = Location::factory()->create(['name' => 'Office A']);
        $newLocation = Location::factory()->create(['name' => 'Region B']);
        $target = User::factory()->create(['location_id' => $newLocation->id, 'email' => null]);
        $asset = Asset::factory()->create(['location_id' => $previousLocation->id]);

        // Simulate post-checkout state while keeping a stale in-memory relation loaded.
        $asset->location_id = $newLocation->id;
        $asset->save();

        $this->fireCheckOutEvent($asset, $target);

        Notification::assertSentTo(
            new AnonymousNotifiable,
            CheckoutAssetNotification::class,
            fn (CheckoutAssetNotification $notification): bool => $notification->item->location?->is($newLocation)
        );
    }

    public function test_consumable_checkout_sends_slack_notification_when_setting_enabled()
    {
        $this->settings->enableSlackWebhook();

        $this->fireCheckOutEvent(
            Consumable::factory()->create(),
            User::factory()->create(),
        );

        $this->assertSlackNotificationSent(CheckoutConsumableNotification::class);
    }

    public function test_consumable_checkout_does_not_send_slack_notification_when_setting_disabled()
    {
        $this->settings->disableSlackWebhook();

        $this->fireCheckOutEvent(
            Consumable::factory()->create(),
            User::factory()->create(),
        );

        $this->assertNoSlackNotificationSent(CheckoutConsumableNotification::class);
    }

    #[DataProvider('licenseCheckoutTargets')]
    public function test_license_checkout_sends_slack_notification_when_setting_enabled($checkoutTarget)
    {
        $this->settings->enableSlackWebhook();

        $this->fireCheckOutEvent(
            LicenseSeat::factory()->create(),
            $checkoutTarget(),
        );

        $this->assertSlackNotificationSent(CheckoutLicenseSeatNotification::class);
    }

    #[DataProvider('licenseCheckoutTargets')]
    public function test_license_checkout_does_not_send_slack_notification_when_setting_disabled($checkoutTarget)
    {
        $this->settings->disableSlackWebhook();

        $this->fireCheckOutEvent(
            LicenseSeat::factory()->create(),
            $checkoutTarget(),
        );

        $this->assertNoSlackNotificationSent(CheckoutLicenseSeatNotification::class);
    }

    private function fireCheckOutEvent(Model $checkoutable, Model $target)
    {
        event(new CheckoutableCheckedOut(
            $checkoutable,
            $target,
            User::factory()->superuser()->create(),
            '',
        ));
    }

    #[DataProvider('checkoutNotificationCases')]
    public function test_checkout_slack_notifications_can_be_built($createNotification)
    {
        $this->settings->enableSlackWebhook();

        $notification = $createNotification(Setting::getSettings());

        $this->assertInstanceOf(
            SlackMessage::class,
            $notification->toSlack()
        );
    }

    #[DataProvider('checkoutNotificationCases')]
    public function test_checkout_google_chat_notifications_can_be_built($createNotification)
    {
        $this->settings->enableGoogleChatWebhook();
        $notification = $createNotification(Setting::getSettings());

        $this->assertInstanceOf(
            GoogleChatMessage::class,
            $notification->toGoogleChat()
        );
    }

    #[DataProvider('checkoutNotificationCases')]
    public function test_checkout_microsoft_teams_workflow_notifications_can_be_built($createNotification)
    {
        $this->settings->enableMicrosoftTeamsWebhook();
        $notification = $createNotification(Setting::getSettings());

        $message = $notification->toMicrosoftTeams();

        $this->assertIsArray($message);
        $this->assertCount(2, $message);
        $this->assertIsString($message[0]);
        $this->assertIsArray($message[1]);
    }

    public static function checkoutNotificationCases(): array
    {
        return [
            'Accessory' => [
                fn ($webhookSource) => new CheckoutAccessoryNotification(
                    Accessory::factory()->create(),
                    User::factory()->create(),
                    User::factory()->superuser()->create(),
                    null,
                    'Test note',
                    $webhookSource,
                ),
            ],

            'Asset' => [
                fn ($webhookSource) => new CheckoutAssetNotification(
                    Asset::factory()->create(),
                    User::factory()->create(),
                    User::factory()->superuser()->create(),
                    null,
                    'Test note',
                    $webhookSource,
                ),
            ],

            'Component' => [
                fn ($webhookSource) => new CheckoutComponentNotification(
                    Component::factory()->create(),
                    User::factory()->create(),
                    User::factory()->superuser()->create(),
                    null,
                    'Test note',
                    $webhookSource,
                ),
            ],

            'Consumable' => [
                fn ($webhookSource) => new CheckoutConsumableNotification(
                    Consumable::factory()->create(),
                    User::factory()->create(),
                    User::factory()->superuser()->create(),
                    null,
                    'Test note',
                    $webhookSource,
                ),
            ],

            'License seat' => [
                fn ($webhookSource) => new CheckoutLicenseSeatNotification(
                    LicenseSeat::factory()->create(),
                    User::factory()->create(),
                    User::factory()->superuser()->create(),
                    null,
                    'Test note',
                    $webhookSource,
                ),
            ],
        ];
    }
}
