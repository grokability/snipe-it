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
class EmailNotificationsToUserUponCheckoutTest extends TestCase
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

    public function test_email_sent_to_user_when_category_requires_acceptance()
    {
        $this->category->update(['require_acceptance' => true]);

        $this->fireCheckoutEvent();

        $this->assertUserSentEmail();
    }

    public function test_email_sent_to_user_when_category_using_default_eula()
    {
        $this->settings->setEula();

        $this->category->update(['use_default_eula' => true]);

        $this->fireCheckoutEvent();

        $this->assertUserSentEmail();
    }

    public function test_email_sent_to_user_when_category_using_local_eula()
    {
        $this->category->update(['eula_text' => 'Some EULA text']);

        $this->fireCheckoutEvent();

        $this->assertUserSentEmail();
    }

    public function test_email_sent_to_user_when_category_set_to_explicitly_send_email()
    {
        $this->category->update(['checkin_email' => true]);

        $this->fireCheckoutEvent();

        $this->assertUserSentEmail();
    }

    public function test_handles_user_not_having_email_address_set()
    {
        $this->category->update(['checkin_email' => true]);
        $this->user->update(['email' => null]);

        $this->fireCheckoutEvent();

        Mail::assertNothingSent();
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

    private function assertUserSentEmail(): void
    {
        Mail::assertSent(CheckoutAssetMail::class, function (CheckoutAssetMail $mail) {
            return $mail->hasTo($this->user->email);
        });
    }
}
