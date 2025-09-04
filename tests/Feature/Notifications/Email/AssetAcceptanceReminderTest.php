<?php

namespace Tests\Feature\Notifications\Email;

use App\Mail\CheckoutAssetMail;
use App\Models\CheckoutAcceptance;
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
        $checkoutAcceptance = CheckoutAcceptance::factory()->pending()->create();

        $this->actingAs(User::factory()->canViewReports()->create())
            ->post($this->routeFor($checkoutAcceptance))
            ->assertRedirect(route('reports/unaccepted_assets'));

        Mail::assertSent(CheckoutAssetMail::class, 1);
        Mail::assertSent(CheckoutAssetMail::class, function (CheckoutAssetMail $mail) use ($checkoutAcceptance) {
            return $mail->hasTo($checkoutAcceptance->assignedTo->email)
                && $mail->hasSubject(trans('mail.unaccepted_asset_reminder'));
        });
    }

    private function routeFor(CheckoutAcceptance $checkoutAcceptance): string
    {
        return route('reports/unaccepted_assets_sent_reminder', [
            'acceptance_id' => $checkoutAcceptance->id,
        ]);
    }
}
