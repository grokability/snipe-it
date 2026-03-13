<?php

namespace Tests\Feature\CheckoutAcceptances\Ui;

use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\User;
use App\Notifications\AcceptanceItemAcceptedNotification;
use App\Notifications\AcceptanceItemDeclinedNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AccessoryAcceptanceTest extends TestCase
{
    public function test_can_accept_accessory_checkout()
    {
        $assignee = User::factory()->create();
        $accessory = Accessory::factory()->create();

        $checkoutAcceptance = CheckoutAcceptance::factory()
            ->pending()
            ->for($assignee, 'assignedTo')
            ->for($accessory, 'checkoutable')
            ->create(['qty' => 2]);

        $this->actingAs($assignee)
            ->post(route('account.store-acceptance', $checkoutAcceptance), [
                'asset_acceptance' => 'accepted',
                'note' => 'A note here',
            ])
            ->assertRedirect();

        $this->assertNotNull($checkoutAcceptance->refresh()->accepted_at);
        $this->assertEquals('A note here', $checkoutAcceptance->note);

        $assignee->accessories->contains($accessory);

        $this->assertDatabaseHas('action_logs', [
            'action_type' => 'accepted',
            'target_id' => $assignee->id,
            'target_type' => User::class,
            'item_id' => $accessory->id,
            'item_type' => Accessory::class,
            'quantity' => 2,
        ]);
    }

    public function test_can_decline_accessory_checkout()
    {
        $assignee = User::factory()->create();
        $accessory = Accessory::factory()->create();

        $checkoutAcceptance = CheckoutAcceptance::factory()
            ->pending()
            ->for($assignee, 'assignedTo')
            ->for($accessory, 'checkoutable')
            ->create(['qty' => 2]);

        $this->actingAs($assignee)
            ->post(route('account.store-acceptance', $checkoutAcceptance), [
                'asset_acceptance' => 'declined',
                'note' => 'A note here',
            ])
            ->assertRedirect();

        $this->assertNotNull($checkoutAcceptance->refresh()->declined_at);
        $this->assertEquals('A note here', $checkoutAcceptance->note);

        $assignee->accessories->doesntContain($accessory);

        $this->assertDatabaseHas('action_logs', [
            'action_type' => 'declined',
            'target_id' => $assignee->id,
            'target_type' => User::class,
            'item_id' => $accessory->id,
            'item_type' => Accessory::class,
            'quantity' => 2,
        ]);
    }

    /**
     * This can be absorbed into a bigger test
     */
    public function testUsersNameIsIncludedInAccessoryAcceptedNotification()
    {
        Notification::fake();

        $this->settings->enableAlertEmail();

        $acceptance = CheckoutAcceptance::factory()
            ->pending()
            ->for(Accessory::factory()->appleMouse(), 'checkoutable')
            ->create();

        $this->actingAs($acceptance->assignedTo)
            ->post(route('account.store-acceptance', $acceptance), ['asset_acceptance' => 'accepted'])
            ->assertSessionHasNoErrors();

        $this->assertNotNull($acceptance->fresh()->accepted_at);

        Notification::assertSentTo(
            $acceptance,
            function (AcceptanceItemAcceptedNotification $notification) use ($acceptance) {
                $this->assertStringContainsString(
                    $acceptance->assignedTo->present()->fullName,
                    $notification->toMail()->render()
                );

                return true;
            }
        );
    }

    /**
     * This can be absorbed into a bigger test
     */
    public function testUsersNameIsIncludedInAccessoryDeclinedNotification()
    {
        Notification::fake();

        $this->settings->enableAlertEmail();

        $acceptance = CheckoutAcceptance::factory()
            ->pending()
            ->for(Accessory::factory()->appleMouse(), 'checkoutable')
            ->create();

        $this->actingAs($acceptance->assignedTo)
            ->post(route('account.store-acceptance', $acceptance), ['asset_acceptance' => 'declined'])
            ->assertSessionHasNoErrors();

        $this->assertNotNull($acceptance->fresh()->declined_at);

        Notification::assertSentTo(
            $acceptance,
            function (AcceptanceItemDeclinedNotification $notification) use ($acceptance) {
                $this->assertStringContainsString(
                    $acceptance->assignedTo->present()->fullName,
                    $notification->toMail($acceptance)->render()
                );

                return true;
            }
        );
    }

    public function testUserIsNotAbleToAcceptAnAssetAssignedToADifferentUser()
    {
        Notification::fake();

        $otherUser = User::factory()->create();

        $acceptance = CheckoutAcceptance::factory()
            ->pending()
            ->for(Asset::factory()->laptopMbp(), 'checkoutable')
            ->create();

        $this->actingAs($otherUser)
            ->post(route('account.store-acceptance', $acceptance), ['asset_acceptance' => 'accepted'])
            ->assertSessionHas(['error' => trans('admin/users/message.error.incorrect_user_accepted')]);

        $this->assertNull($acceptance->fresh()->accepted_at);
    }

    /**
     * @link https://github.com/grokability/snipe-it/issues/17589
     */
    public function test_all_accessory_checkout_entries_are_removed_when_user_declines_acceptance()
    {
        $assignee = User::factory()->create();

        $this->actingAs(User::factory()->checkoutAccessories()->create());

        // create accessory that requires acceptance
        $accessory = Accessory::factory()->requiringAcceptance()->create(['qty' => 5]);

        // checkout 3 accessories to the user
        $this->post(route('accessories.checkout.store', $accessory), [
            'assigned_user' => $assignee->id,
            'checkout_qty' => 3,
        ]);

        $originalAccessoryCheckoutCount = AccessoryCheckout::count();

        // find the acceptance to be declined
        $checkoutAcceptance = CheckoutAcceptance::query()
            ->where([
                'assigned_to_id' => $assignee->id,
                'qty' => 3,
            ])
            ->whereNull('accepted_at')
            ->whereNull('declined_at')
            ->whereHasMorph(
                'checkoutable',
                [Accessory::class],
            )
            ->sole();

        // decline the checkout
        $this->actingAs($assignee)
            ->post(route('account.store-acceptance', $checkoutAcceptance), [
                'asset_acceptance' => 'declined',
            ]);

        $this->assertEquals($originalAccessoryCheckoutCount - 3, AccessoryCheckout::count());
    }
}
