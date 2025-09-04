<?php

namespace Tests\Feature\Notifications\Email;

use App\Events\CheckoutableCheckedIn;
use App\Mail\CheckinAssetMail;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('notifications')]
class EmailNotificationsToAdminAlertEmailUponCheckinTest extends TestCase
{
    private Asset $asset;
    private AssetModel $assetModel;
    private Category $category;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->user = User::factory()->create();

        $this->category = Category::factory()->create([
            'checkin_email' => false,
            'eula_text' => null,
            'require_acceptance' => false,
            'use_default_eula' => false,
        ]);

        $this->assetModel = AssetModel::factory()->for($this->category)->create();

        $this->asset = Asset::factory()
            ->for($this->assetModel, 'model')
            ->assignedToUser($this->user)
            ->create();
    }

    public function test_admin_alert_email_sends()
    {
        $this->settings->enableAdminCC('cc@example.com');

        $this->category->update(['checkin_email' => true]);

        $this->fireCheckInEvent($this->asset, $this->user);

        Mail::assertSentCount(2);
        Mail::assertSent(CheckinAssetMail::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });
        Mail::assertSent(CheckinAssetMail::class, function ($mail) {
            return $mail->hasTo('cc@example.com');
        });
    }

    public function test_admin_alert_email_still_sent_when_category_email_is_not_set_to_send_email_to_user()
    {
        $this->settings->enableAdminCC('cc@example.com');

        $this->category->update(['checkin_email' => false]);

        $this->fireCheckInEvent($this->asset, $this->user);

        Mail::assertSent(CheckinAssetMail::class, function ($mail) {
            return $mail->hasTo('cc@example.com');
        });
    }

    public function test_admin_alert_email_still_sent_when_user_has_no_email_address()
    {
        $this->settings->enableAdminCC('cc@example.com');

        $this->user->update(['email' => null]);

        $this->category->update(['checkin_email' => true]);

        $this->fireCheckInEvent($this->asset, $this->user);

        Mail::assertSent(CheckinAssetMail::class, function ($mail) {
            return $mail->hasTo('cc@example.com');
        });
    }

    public function test_admin_alert_email_sent_when_always_send_is_true_and_asset_does_not_require_acceptance()
    {
        $this->settings
            ->enableAdminCC('cc@example.com')
            ->enableAdminCCAlways();

        $this->category->update(['checkin_email' => false]);

        $this->fireCheckInEvent($this->asset, $this->user);

        Mail::assertSent(CheckinAssetMail::class, function ($mail) {
            return $mail->hasTo('cc@example.com') || $mail->hasCc('cc@example.com');
        });
    }

    public function test_admin_alert_email_not_sent_when_always_send_is_false_and_asset_does_not_require_acceptance()
    {
        $this->settings
            ->enableAdminCC('cc@example.com')
            ->disableAdminCCAlways();

        $this->category->update(['checkin_email' => false]);

        $this->fireCheckInEvent($this->asset, $this->user);

        Mail::assertNotSent(CheckinAssetMail::class, function ($mail) {
            return $mail->hasTo('cc@example.com') || $mail->hasCc('cc@example.com');
        });
    }

    private function fireCheckInEvent($asset, $user): void
    {
        event(new CheckoutableCheckedIn(
            $asset,
            $user,
            User::factory()->checkinAssets()->create(),
            ''
        ));
    }
}
