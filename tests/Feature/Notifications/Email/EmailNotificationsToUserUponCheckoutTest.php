<?php

namespace Tests\Feature\Notifications\Email;

use App\Events\CheckoutableCheckedOut;
use App\Mail\CheckoutAssetMail;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\CheckoutAcceptance;
use App\Models\CustomField;
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

    public function test_email_contains_link_to_confirm_acceptance_when_category_requires_acceptance()
    {
        $this->category->update(['require_acceptance' => true]);

        $this->fireCheckoutEvent();

        Mail::assertSent(CheckoutAssetMail::class, function (CheckoutAssetMail $mail) {
            $acceptance = CheckoutAcceptance::query()
                ->forUser($this->user)
                ->whereCheckoutableId($this->asset->id)
                ->whereCheckoutableType(Asset::class)
                ->firstOrFail();

            $url = route('account.accept.item', $acceptance);

            $mailContents = $mail->render();

            if (! str_contains($mailContents, $url)) {
                $this->fail("Email does not contain the acceptance url. Expected to find $url in email contents.");
            }

            return $mail->hasTo($this->user->email);
        });
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

    public function test_email_includes_custom_fields_marked_show_in_email_and_not_encrypted()
    {
        $customField = CustomField::factory()->create([
            'name' => 'Cost Center',
            'show_in_email' => '1',
            'field_encrypted' => '0',
        ])->fresh();

        $asset = Asset::factory()->hasMultipleCustomFields([$customField])->create();
        $asset->{$customField->db_column} = 'ENG-42';
        $asset->save();

        $this->category = $asset->model->category;
        $this->asset = $asset;
        $this->user = User::factory()->create();

        $this->category->update(['checkin_email' => true]);

        $this->fireCheckoutEvent();

        Mail::assertSent(CheckoutAssetMail::class, function (CheckoutAssetMail $mail) {
            $rendered = $mail->render();

            return $mail->hasTo($this->user->email)
                && str_contains($rendered, 'Cost Center')
                && str_contains($rendered, 'ENG-42');
        });
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
