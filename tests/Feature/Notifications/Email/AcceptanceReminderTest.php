<?php

namespace Tests\Feature\Notifications\Email;

use App\Mail\CheckoutAccessoryMail;
use App\Mail\CheckoutAssetMail;
use App\Mail\CheckoutConsumableMail;
use App\Mail\CheckoutLicenseMail;
use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Consumable;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Generator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AcceptanceReminderTest extends TestCase
{
    private User $admin;
    private User $assignee;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->admin = User::factory()->canViewReports()->create();
        $this->assignee = User::factory()->create();
    }

    public function testMustHavePermissionToSendReminder()
    {
        $checkoutAcceptance = CheckoutAcceptance::factory()->pending()->create();
        $userWithoutPermission = User::factory()->create();

        $this->actingAs($userWithoutPermission)
            ->post(route('reports/unaccepted_assets_sent_reminder', [
                'acceptance_id' => $checkoutAcceptance->id,
            ]))
            ->assertForbidden();

        Mail::assertNotSent(CheckoutAssetMail::class);
    }

    public function testReminderNotSentIfAcceptanceDoesNotExist()
    {
        $this->actingAs($this->admin)
            ->post(route('reports/unaccepted_assets_sent_reminder', [
                'acceptance_id' => 999999,
            ]));

        Mail::assertNotSent(CheckoutAssetMail::class);
    }

    public function testReminderNotSentIfAcceptanceAlreadyAccepted()
    {
        $checkoutAcceptanceAlreadyAccepted = CheckoutAcceptance::factory()->accepted()->create();

        $this->actingAs($this->admin)
            ->post(route('reports/unaccepted_assets_sent_reminder', [
                'acceptance_id' => $checkoutAcceptanceAlreadyAccepted->id,
            ]));

        Mail::assertNotSent(CheckoutAssetMail::class);
    }

    public static function checkoutAcceptancesToUsersWithoutEmailAddresses(): Generator
    {
        yield 'User with null email address' => [
            function () {
                return CheckoutAcceptance::factory()
                    ->pending()
                    ->forAssignedTo(['email' => null])
                    ->create();
            }
        ];

        yield 'User with empty string email address' => [
            function () {
                return CheckoutAcceptance::factory()
                    ->pending()
                    ->forAssignedTo(['email' => ''])
                    ->create();
            }
        ];
    }

    #[DataProvider('checkoutAcceptancesToUsersWithoutEmailAddresses')]
    public function testUserWithoutEmailAddressHandledGracefully($callback)
    {
        $checkoutAcceptance = $callback();

        $this->actingAs($this->admin)
            ->post(route('reports/unaccepted_assets_sent_reminder', [
                'acceptance_id' => $checkoutAcceptance->id,
            ]))
            // check we didn't crash...
            ->assertRedirect();

        Mail::assertNotSent(CheckoutAssetMail::class);
    }

    public function testReminderIsSentToUserForAccessory()
    {
        $accessory = Accessory::factory()->requiringAcceptance()->create();

        $acceptance = $this->createCheckoutAcceptance($accessory, $this->assignee);

        $this->createActionLogEntry($accessory, $this->admin, $this->assignee, $acceptance);

        AccessoryCheckout::factory()
            ->for($this->admin, 'adminuser')
            ->for($accessory)
            ->for($this->assignee, 'assignedTo')
            ->create();

        $this->actingAs($this->admin)
            ->post(route('reports/unaccepted_assets_sent_reminder', [
                'acceptance_id' => $acceptance->id,
            ]))
            ->assertRedirect(route('reports/unaccepted_assets'));

        Mail::assertSent(CheckoutAccessoryMail::class, 1);

        Mail::assertSent(CheckoutAccessoryMail::class, function (CheckoutAccessoryMail $mail) {
            return $mail->hasTo($this->assignee);
        });

        Mail::assertSent(CheckoutAccessoryMail::class, function (CheckoutAccessoryMail $mail) {
            return $mail->hasSubject(trans('mail.unaccepted_asset_reminder'));
        });

        Mail::assertSent(CheckoutAccessoryMail::class, function (CheckoutAccessoryMail $mail) {
            return str_contains($mail->render(), trans('mail.recent_item_checked'));
        });
    }

    public function testReminderIsSentToUserForAsset()
    {
        $asset = Asset::factory()->requiresAcceptance()->create();

        $acceptance = $this->createCheckoutAcceptance($asset, $this->assignee);

        $this->createActionLogEntry($asset, $this->admin, $this->assignee, $acceptance);

        $this->actingAs($this->admin)
            ->post(route('reports/unaccepted_assets_sent_reminder', [
                'acceptance_id' => $acceptance->id,
            ]))
            ->assertRedirect(route('reports/unaccepted_assets'));

        Mail::assertSent(CheckoutAssetMail::class, 1);

        Mail::assertSent(CheckoutAssetMail::class, function (CheckoutAssetMail $mail) {
            return $mail->hasTo($this->assignee);
        });

        Mail::assertSent(CheckoutAssetMail::class, function (CheckoutAssetMail $mail) {
            return $mail->hasSubject(trans('mail.unaccepted_asset_reminder'));
        });

        Mail::assertSent(CheckoutAssetMail::class, function (CheckoutAssetMail $mail) {
            return str_contains($mail->render(), trans('mail.recent_item_checked'));
        });
    }

    public function testReminderIsSentToUserForConsumable()
    {
        $consumable = Consumable::factory()->requiringAcceptance()->create();

        $acceptance = $this->createCheckoutAcceptance($consumable, $this->assignee);

        $this->createActionLogEntry($consumable, $this->admin, $this->assignee, $acceptance);

        $this->actingAs($this->admin)
            ->post(route('reports/unaccepted_assets_sent_reminder', [
                'acceptance_id' => $acceptance->id,
            ]))
            ->assertRedirect(route('reports/unaccepted_assets'));

        Mail::assertSent(CheckoutConsumableMail::class, 1);

        Mail::assertSent(CheckoutConsumableMail::class, function (CheckoutConsumableMail $mail) {
            return $mail->hasTo($this->assignee);
        });

        Mail::assertSent(CheckoutConsumableMail::class, function (CheckoutConsumableMail $mail) {
            return $mail->hasSubject(trans('mail.unaccepted_asset_reminder'));
        });

        Mail::assertSent(CheckoutConsumableMail::class, function (CheckoutConsumableMail $mail) {
            return str_contains($mail->render(), trans('mail.recent_item_checked'));
        });
    }

    public function testReminderIsSentToUserForLicenseSeat()
    {
        $licenseSeat = LicenseSeat::factory()->requiringAcceptance()->create();

        $acceptance = $this->createCheckoutAcceptance($licenseSeat, $this->assignee);

        $this->createActionLogEntry($licenseSeat, $this->admin, $this->assignee, $acceptance);

        $this->actingAs($this->admin)
            ->post(route('reports/unaccepted_assets_sent_reminder', [
                'acceptance_id' => $acceptance->id,
            ]))
            ->assertRedirect(route('reports/unaccepted_assets'));

        Mail::assertSent(CheckoutLicenseMail::class, 1);

        Mail::assertSent(CheckoutLicenseMail::class, function (CheckoutLicenseMail $mail) {
            return $mail->hasTo($this->assignee);
        });

        Mail::assertSent(CheckoutLicenseMail::class, function (CheckoutLicenseMail $mail) {
            return $mail->hasSubject(trans('mail.unaccepted_asset_reminder'));
        });

        Mail::assertSent(CheckoutLicenseMail::class, function (CheckoutLicenseMail $mail) {
            return str_contains($mail->render(), trans('mail.recent_item_checked'));
        });
    }

    private function createCheckoutAcceptance(Model $item, Model $assignee): CheckoutAcceptance
    {
        return CheckoutAcceptance::factory()
            ->for($item, 'checkoutable')
            ->for($assignee, 'assignedTo')
            ->withoutActionLog()
            ->pending()
            ->create();
    }

    private function createActionLogEntry(Model $item, Model $admin, Model $assignee, CheckoutAcceptance $acceptance): Actionlog
    {
        $itemId = $item->id;
        $itemType = get_class($item);

        if (get_class($item) === LicenseSeat::class) {
            $itemId = $item->license->id;
            $itemType = License::class;
        }

        return Actionlog::factory()
            ->for($admin, 'adminuser')
            ->for($assignee, 'target')
            ->create([
                'action_type' => 'checkout',
                'item_id' => $itemId,
                'item_type' => $itemType,
                'created_at' => $acceptance->created_at,
            ]);
    }
}
