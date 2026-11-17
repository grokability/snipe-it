<?php

namespace Tests\Feature\Notifications\Email;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Consumable;
use App\Models\LicenseSeat;
use App\Models\User;
use App\Notifications\UnacceptedAssetReminderNotification;
use Generator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Reminder-send coverage for the single-row path on
 * /unaccepted-acceptances/send-reminder.
 *
 * Post-v8.9 refactor: every reminder flow (console command,
 * per-user button, bulk UI, API) dispatches
 * UnacceptedAssetReminderNotification. These tests pin that the
 * per-row controller endpoint still fires a notification for each
 * checkoutable type and bails cleanly on the empty-email + missing-
 * acceptance edge cases.
 */
class AcceptanceReminderTest extends TestCase
{
    private User $admin;

    private User $assignee;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        // Any checkout grant passes canCheckoutAtLeastOneItemType,
        // which is what the moved endpoint gates on now.
        $this->admin = User::factory()->checkoutAccessories()->create();
        $this->assignee = User::factory()->create();
    }

    public function test_must_have_permission_to_send_reminder()
    {
        $checkoutAcceptance = CheckoutAcceptance::factory()->pending()->create();
        $userWithoutPermission = User::factory()->create();

        $this->actingAs($userWithoutPermission)
            ->post(route('unaccepted-acceptances.send-reminder', [
                'acceptance_id' => $checkoutAcceptance->id,
            ]))
            ->assertForbidden();

        Notification::assertNothingSent();
    }

    public function test_reminder_not_sent_if_acceptance_does_not_exist()
    {
        $this->actingAs($this->admin)
            ->post(route('unaccepted-acceptances.send-reminder', [
                'acceptance_id' => 999999,
            ]));

        Notification::assertNothingSent();
    }

    public function test_reminder_not_sent_if_acceptance_already_accepted()
    {
        $checkoutAcceptanceAlreadyAccepted = CheckoutAcceptance::factory()->accepted()->create();

        $this->actingAs($this->admin)
            ->post(route('unaccepted-acceptances.send-reminder', [
                'acceptance_id' => $checkoutAcceptanceAlreadyAccepted->id,
            ]));

        Notification::assertNothingSent();
    }

    public static function checkoutAcceptancesToUsersWithoutEmailAddresses(): Generator
    {
        yield 'User with null email address' => [
            function () {
                return CheckoutAcceptance::factory()
                    ->pending()
                    ->forAssignedTo(['email' => null])
                    ->create();
            },
        ];

        yield 'User with empty string email address' => [
            function () {
                return CheckoutAcceptance::factory()
                    ->pending()
                    ->forAssignedTo(['email' => ''])
                    ->create();
            },
        ];
    }

    #[DataProvider('checkoutAcceptancesToUsersWithoutEmailAddresses')]
    public function test_user_without_email_address_handled_gracefully($callback)
    {
        $checkoutAcceptance = $callback();

        $this->actingAs($this->admin)
            ->post(route('unaccepted-acceptances.send-reminder', [
                'acceptance_id' => $checkoutAcceptance->id,
            ]))
            // check we didn't crash
            ->assertRedirect();

        Notification::assertNothingSent();
    }

    public function test_reminder_is_sent_to_user_for_accessory()
    {
        $accessory = Accessory::factory()->requiringAcceptance()->create();
        $acceptance = $this->createCheckoutAcceptance($accessory, $this->assignee);

        $this->actingAs($this->admin)
            ->post(route('unaccepted-acceptances.send-reminder', [
                'acceptance_id' => $acceptance->id,
            ]))
            ->assertRedirect(route('unaccepted-acceptances.index'));

        Notification::assertSentTo($this->assignee, UnacceptedAssetReminderNotification::class);
    }

    public function test_reminder_is_sent_to_user_for_asset()
    {
        $asset = Asset::factory()->requiresAcceptance()->create();
        $acceptance = $this->createCheckoutAcceptance($asset, $this->assignee);

        $this->actingAs($this->admin)
            ->post(route('unaccepted-acceptances.send-reminder', [
                'acceptance_id' => $acceptance->id,
            ]))
            ->assertRedirect(route('unaccepted-acceptances.index'));

        Notification::assertSentTo($this->assignee, UnacceptedAssetReminderNotification::class);
    }

    public function test_reminder_is_sent_to_user_for_consumable()
    {
        $consumable = Consumable::factory()->requiringAcceptance()->create();
        $acceptance = $this->createCheckoutAcceptance($consumable, $this->assignee);

        $this->actingAs($this->admin)
            ->post(route('unaccepted-acceptances.send-reminder', [
                'acceptance_id' => $acceptance->id,
            ]))
            ->assertRedirect(route('unaccepted-acceptances.index'));

        Notification::assertSentTo($this->assignee, UnacceptedAssetReminderNotification::class);
    }

    public function test_reminder_is_sent_to_user_for_license_seat()
    {
        $licenseSeat = LicenseSeat::factory()->requiringAcceptance()->create();
        $acceptance = $this->createCheckoutAcceptance($licenseSeat, $this->assignee);

        $this->actingAs($this->admin)
            ->post(route('unaccepted-acceptances.send-reminder', [
                'acceptance_id' => $acceptance->id,
            ]))
            ->assertRedirect(route('unaccepted-acceptances.index'));

        Notification::assertSentTo($this->assignee, UnacceptedAssetReminderNotification::class);
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
}
