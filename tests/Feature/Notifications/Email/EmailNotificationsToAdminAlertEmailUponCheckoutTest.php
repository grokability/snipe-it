<?php

namespace Tests\Feature\Notifications\Email;

use App\Events\CheckoutableCheckedOut;
use App\Mail\CheckoutAssetMail;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('notifications')]
class EmailNotificationsToAdminAlertEmailUponCheckoutTest extends TestCase
{
    private Asset $asset;
    private AssetModel $assetModel;
    private Category $category;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->category = Category::factory()->create([
            'checkin_email' => false,
            'eula_text' => null,
            'require_acceptance' => false,
            'use_default_eula' => false,
        ]);

        $this->assetModel = AssetModel::factory()->for($this->category)->create();
        $this->asset = Asset::factory()->for($this->assetModel, 'model')->create();

        $this->user = User::factory()->create();
    }

    public function test_admin_alert_email_sends()
    {
        $this->settings->enableAdminCC('cc@example.com');

        $this->category->update(['checkin_email' => true]);

        $this->fireCheckoutEvent();

        Mail::assertSent(CheckoutAssetMail::class, function (CheckoutAssetMail $mail) {
            return $mail->hasTo('cc@example.com');
        });
    }

    public function test_admin_alert_email_still_sent_when_category_is_not_set_to_send_email_to_user()
    {
        $this->settings->enableAdminCC('cc@example.com');

        $this->fireCheckoutEvent();

        Mail::assertSent(CheckoutAssetMail::class, function ($mail) {
            return $mail->hasTo('cc@example.com');
        });
    }

    public function test_admin_alert_email_still_sent_when_user_has_no_email_address()
    {
        $this->settings->enableAdminCC('cc@example.com');

        $this->category->update(['checkin_email' => true]);
        $this->user->update(['email' => null]);

        $this->fireCheckoutEvent();

        Mail::assertSent(CheckoutAssetMail::class, function ($mail) {
            return $mail->hasTo('cc@example.com');
        });
    }

    public function test_admin_alert_email_sent_when_always_send_is_true_and_asset_does_not_require_acceptance()
    {
        $this->settings
            ->enableAdminCC('cc@example.com')
            ->enableAdminCCAlways();

        $this->category->update(['checkin_email' => false]);

        $this->fireCheckoutEvent();

        Mail::assertSent(CheckoutAssetMail::class, function (CheckoutAssetMail $mail) {
            return $mail->hasTo('cc@example.com') || $mail->hasCc('cc@example.com');
        });
    }

    public function test_admin_alert_email_not_sent_when_always_send_is_false_and_asset_does_not_require_acceptance()
    {
        $this->settings
            ->enableAdminCC('cc@example.com')
            ->disableAdminCCAlways();

        $this->category->update(['checkin_email' => false]);

        $this->fireCheckoutEvent();

        Mail::assertNotSent(CheckoutAssetMail::class, function (CheckoutAssetMail $mail) {
            return $mail->hasTo('cc@example.com') || $mail->hasCc('cc@example.com');
        });
    }

    private function fireCheckoutEvent(): void
    {
        event(new CheckoutableCheckedOut(
            $this->asset,
            $this->user,
            User::factory()->superuser()->create(),
            '',
        ));
    }
}
