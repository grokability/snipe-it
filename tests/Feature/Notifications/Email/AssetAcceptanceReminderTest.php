<?php

namespace Tests\Feature\Notifications\Email;

use App\Mail\CheckoutAccessoryMail;
use App\Mail\CheckoutAssetMail;
use App\Mail\CheckoutComponentMail;
use App\Mail\CheckoutConsumableMail;
use App\Mail\CheckoutLicenseMail;
use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AssetAcceptanceReminderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    public function testMustHavePermissionToSendReminder()
    {
        $checkoutAcceptance = CheckoutAcceptance::factory()->pending()->create();
        $userWithoutPermission = User::factory()->create();

        $this->actingAs($userWithoutPermission)
            ->post($this->routeFor($checkoutAcceptance))
            ->assertForbidden();

        Mail::assertNotSent(CheckoutAssetMail::class);
    }

    public function testReminderNotSentIfAcceptanceDoesNotExist()
    {
        $this->actingAs(User::factory()->canViewReports()->create())
            ->post(route('reports/unaccepted_assets_sent_reminder', [
                'acceptance_id' => 999999,
            ]));

        Mail::assertNotSent(CheckoutAssetMail::class);
    }

    public function testReminderNotSentIfAcceptanceAlreadyAccepted()
    {
        $checkoutAcceptanceAlreadyAccepted = CheckoutAcceptance::factory()->accepted()->create();

        $this->actingAs(User::factory()->canViewReports()->create())
            ->post($this->routeFor($checkoutAcceptanceAlreadyAccepted));

        Mail::assertNotSent(CheckoutAssetMail::class);
    }

    public static function CheckoutAcceptancesToUsersWithoutEmailAddresses()
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

    #[DataProvider('CheckoutAcceptancesToUsersWithoutEmailAddresses')]
    public function testUserWithoutEmailAddressHandledGracefully($callback)
    {
        $checkoutAcceptance = $callback();

        $this->actingAs(User::factory()->canViewReports()->create())
            ->post($this->routeFor($checkoutAcceptance))
            // check we didn't crash...
            ->assertRedirect();

        Mail::assertNotSent(CheckoutAssetMail::class);
    }

    public function testReminderIsSentToUser()
    {
        $checkedOutBy = User::factory()->canViewReports()->create();

        $checkoutTypes = [
            Asset::class       => CheckoutAssetMail::class,
            Accessory::class   => CheckoutAccessoryMail::class,
            LicenseSeat::class => CheckoutLicenseMail::class,
            Consumable::class  => CheckoutConsumableMail::class,
            //for the future its setup for components, but we dont send reminders for components at the moment.
//            Component::class   => CheckoutComponentMail::class,
        ];

        $assignee = User::factory()->create(['email' => 'test@example.com']);
        foreach ($checkoutTypes as $modelClass => $mailable) {

            $item = $modelClass::factory()->create();
            $acceptance = CheckoutAcceptance::factory()->withoutActionLog()->pending()->create([
                'checkoutable_id' => $item->id,
                'checkoutable_type' => $modelClass,
                'assigned_to_id' => $assignee->id,
            ]);

            if ($modelClass === LicenseSeat::class) {
                $logType = License::class;
                $logId   = $item->license->id;
            } else {
                $logType = $modelClass;
                $logId   = $item->id;
            }

          Actionlog::factory()->create([
                'action_type' => 'checkout',
                'created_by' => $checkedOutBy->id,
                'target_id' => $assignee->id,
                'item_type' => $logType,
                'item_id' => $logId,
                'created_at' => $acceptance->created_at,
            ]);

        $this->actingAs($checkedOutBy)
            ->post($this->routeFor($acceptance))
            ->assertRedirect(route('reports/unaccepted_assets'));
        }

        Mail::assertSent($mailable, 1);
        Mail::assertSent($mailable, function ($mail) use ($assignee) {
            return $mail->hasTo($assignee->email);
        });
    }

    private function routeFor(CheckoutAcceptance $checkoutAcceptance): string
    {
        return route('reports/unaccepted_assets_sent_reminder', [
            'acceptance_id' => $checkoutAcceptance->id,
        ]);
    }
}
