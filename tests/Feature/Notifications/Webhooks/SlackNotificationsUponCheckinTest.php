<?php

namespace Tests\Feature\Notifications\Webhooks;

use App\Events\CheckoutableCheckedIn;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Component;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\User;
use App\Notifications\CheckinAccessoryNotification;
use App\Notifications\CheckinAssetNotification;
use App\Notifications\CheckinComponentNotification;
use App\Notifications\CheckinLicenseSeatNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('notifications')]
class SlackNotificationsUponCheckinTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public static function assetCheckInTargets(): array
    {
        return [
            'Asset checked out to user' => [fn () => User::factory()->create()],
            'Asset checked out to asset' => [fn () => Asset::factory()->laptopMbp()->create()],
            'Asset checked out to location' => [fn () => Location::factory()->create()],
        ];
    }

    public static function licenseCheckInTargets(): array
    {
        return [
            'License checked out to user' => [fn () => User::factory()->create()],
            'License checked out to asset' => [fn () => Asset::factory()->laptopMbp()->create()],
        ];
    }

    public function test_accessory_checkin_sends_slack_notification_when_setting_enabled()
    {
        $this->settings->enableSlackWebhook();

        $this->fireCheckInEvent(
            Accessory::factory()->create(),
            User::factory()->create(),
        );

        $this->assertSlackNotificationSent(CheckinAccessoryNotification::class);
    }

    public function test_accessory_checkin_does_not_send_slack_notification_when_setting_disabled()
    {
        $this->settings->disableSlackWebhook();

        $this->fireCheckInEvent(
            Accessory::factory()->create(),
            User::factory()->create(),
        );

        $this->assertNoSlackNotificationSent(CheckinAccessoryNotification::class);
    }

    #[DataProvider('assetCheckInTargets')]
    public function test_asset_checkin_sends_slack_notification_when_setting_enabled($checkoutTarget)
    {
        $this->settings->enableSlackWebhook();

        $this->fireCheckInEvent(
            Asset::factory()->create(),
            $checkoutTarget(),
        );

        $this->assertSlackNotificationSent(CheckinAssetNotification::class);
    }

    #[DataProvider('assetCheckInTargets')]
    public function test_asset_checkin_does_not_send_slack_notification_when_setting_disabled($checkoutTarget)
    {
        $this->settings->disableSlackWebhook();

        $this->fireCheckInEvent(
            Asset::factory()->create(),
            $checkoutTarget(),
        );

        $this->assertNoSlackNotificationSent(CheckinAssetNotification::class);
    }

    #[DataProvider('assetCheckInTargets')]
    public function test_component_checkin_sends_slack_notification_when_setting_enabled($checkoutTarget)
    {
        $this->settings->enableSlackWebhook();

        $this->fireCheckInEvent(
            Component::factory()->create(),
            $checkoutTarget(),
        );

        $this->assertSlackNotificationSent(CheckinComponentNotification::class);
    }

    #[DataProvider('assetCheckInTargets')]
    public function test_component_checkin_does_not_send_slack_notification_when_setting_disabled($checkoutTarget)
    {
        $this->settings->disableSlackWebhook();

        $this->fireCheckInEvent(
            Component::factory()->create(),
            $checkoutTarget(),
        );

        $this->assertNoSlackNotificationSent(CheckinComponentNotification::class);
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
        $asset = Asset::factory()->for($assetModel, 'model')->assignedToUser()->create();

        $this->fireCheckInEvent(
            $asset,
            User::factory()->create(),
        );

        $this->assertSlackNotificationSent(CheckinAssetNotification::class);
    }

    public function test_asset_checkin_slack_notification_uses_updated_asset_location()
    {
        $this->settings->enableSlackWebhook();

        $previousLocation = Location::factory()->create(['name' => 'Region B']);
        $newLocation = Location::factory()->create(['name' => 'Office A']);
        $target = User::factory()->create(['location_id' => $previousLocation->id]);
        $asset = Asset::factory()->create(['location_id' => $previousLocation->id]);

        // Simulate post-checkin state while keeping a stale in-memory relation loaded.
        $asset->location_id = $newLocation->id;
        $asset->save();

        $this->fireCheckInEvent($asset, $target);

        Notification::assertSentTo(
            new AnonymousNotifiable,
            CheckinAssetNotification::class,
            fn (CheckinAssetNotification $notification): bool => $notification->item->location?->is($newLocation)
        );
    }

    #[DataProvider('licenseCheckInTargets')]
    public function test_license_checkin_sends_slack_notification_when_setting_enabled($checkoutTarget)
    {
        $this->settings->enableSlackWebhook();

        $this->fireCheckInEvent(
            LicenseSeat::factory()->create(),
            $checkoutTarget(),
        );

        $this->assertSlackNotificationSent(CheckinLicenseSeatNotification::class);
    }

    #[DataProvider('licenseCheckInTargets')]
    public function test_license_checkin_does_not_send_slack_notification_when_setting_disabled($checkoutTarget)
    {
        $this->settings->disableSlackWebhook();

        $this->fireCheckInEvent(
            LicenseSeat::factory()->create(),
            $checkoutTarget(),
        );

        $this->assertNoSlackNotificationSent(CheckinLicenseSeatNotification::class);
    }

    private function fireCheckInEvent(Model $checkoutable, Model $target)
    {
        event(new CheckoutableCheckedIn(
            $checkoutable,
            $target,
            User::factory()->superuser()->create(),
            ''
        ));
    }
}
