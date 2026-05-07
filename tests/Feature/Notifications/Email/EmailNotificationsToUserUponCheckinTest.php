<?php

namespace Tests\Feature\Notifications\Email;

use App\Events\CheckoutableCheckedIn;
use App\Mail\CheckinAssetMail;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Consumable;
use App\Models\CustomField;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('notifications')]
class EmailNotificationsToUserUponCheckinTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    public function test_check_in_email_sent_to_user_if_setting_enabled()
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create();

        $asset->model->category->update(['checkin_email' => true]);

        $this->fireCheckInEvent($asset, $user);

        Mail::assertSent(CheckinAssetMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_check_in_email_not_sent_to_user_if_setting_disabled()
    {
        $this->settings->disableAdminCC();

        $user = User::factory()->create();
        $checkoutables = collect([
            Asset::factory()->assignedToUser($user)->create(),
            LicenseSeat::factory()->assignedToUser($user)->create(),
            Accessory::factory()->checkedOutToUser($user)->create(),
            Consumable::factory()->checkedOutToUser($user)->create(),
        ]);

        foreach ($checkoutables as $checkoutable) {

            if ($checkoutable instanceof Asset) {
                $checkoutable->model->category->update([
                    'checkin_email' => false,
                    'eula_text' => null,
                    'require_acceptance' => false,
                ]);
                $checkoutable = $checkoutable->fresh(['model.category']);
            }

            if ($checkoutable instanceof Accessory || $checkoutable instanceof Consumable) {
                $checkoutable->category->update([
                    'checkin_email' => false,
                    'eula_text' => null,
                    'require_acceptance' => false,
                ]);
                $checkoutable = $checkoutable->fresh(['category']);
            }

            if ($checkoutable instanceof LicenseSeat) {
                $checkoutable->license->category->update([
                    'checkin_email' => false,
                    'eula_text' => null,
                    'require_acceptance' => false,
                ]);
                $checkoutable = $checkoutable->fresh(['license.category']);
            }

            // Fire event manually
            $this->fireCheckInEvent($checkoutable, $user);
        }

        Mail::assertNotSent(CheckinAssetMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_handles_user_not_having_email_address_set()
    {
        $user = User::factory()->create(['email' => null]);
        $asset = Asset::factory()->assignedToUser($user)->create();

        $asset->model->category->update(['checkin_email' => true]);

        $this->fireCheckInEvent($asset, $user);

        Mail::assertNothingSent();
    }

    public function test_checkin_email_includes_custom_fields_marked_show_in_email_and_not_encrypted()
    {
        $customField = CustomField::factory()->create([
            'name' => 'Cost Center',
            'show_in_email' => '1',
            'field_encrypted' => '0',
        ])->fresh();

        $user = User::factory()->create();
        $asset = Asset::factory()->hasMultipleCustomFields([$customField])->assignedToUser($user)->create();
        $asset->{$customField->db_column} = 'ENG-42';
        $asset->save();

        $asset->model->category->update(['checkin_email' => true]);

        $this->fireCheckInEvent($asset, $user);

        Mail::assertSent(CheckinAssetMail::class, function (CheckinAssetMail $mail) use ($user) {
            $rendered = $mail->render();

            return $mail->hasTo($user->email)
                && str_contains($rendered, 'Cost Center')
                && str_contains($rendered, 'ENG-42');
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
