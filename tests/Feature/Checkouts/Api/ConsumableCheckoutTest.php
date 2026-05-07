<?php

namespace Tests\Feature\Checkouts\Api;

use App\Mail\CheckoutConsumableMail;
use App\Models\Actionlog;
use App\Models\Consumable;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ConsumableCheckoutTest extends TestCase
{
    public function test_checking_out_consumable_requires_correct_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.consumables.checkout', Consumable::factory()->create()))
            ->assertForbidden();
    }

    public function test_validation_when_checking_out_consumable()
    {
        $this->actingAsForApi(User::factory()->checkoutConsumables()->create())
            ->postJson(route('api.consumables.checkout', Consumable::factory()->create()), [
                // missing assigned_to
            ])
            ->assertStatusMessageIs('error');
    }

    public function test_consumable_must_be_available_when_checking_out()
    {
        $this->actingAsForApi(User::factory()->checkoutConsumables()->create())
            ->postJson(route('api.consumables.checkout', Consumable::factory()->withoutItemsRemaining()->create()), [
                'assigned_to' => User::factory()->create()->id,
            ])
            ->assertStatusMessageIs('error');
    }

    public function test_consumable_can_be_checked_out()
    {
        $consumable = Consumable::factory()->create();
        $user = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutConsumables()->create())
            ->postJson(route('api.consumables.checkout', $consumable), [
                'assigned_to' => $user->id,
            ]);

        $this->assertTrue($user->consumables->contains($consumable));
        $this->assertHasTheseActionLogs($consumable, ['create', 'checkout']);
    }

    public function test_consumable_can_be_checked_out_with_quantity()
    {
        $consumable = Consumable::factory()->create();
        $user = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutConsumables()->create())
            ->postJson(route('api.consumables.checkout', $consumable), [
                'assigned_to' => $user->id,
                'checkout_qty' => 2,
            ]);

        $this->assertDatabaseHas('action_logs', [
            'item_type' => Consumable::class,
            'item_id' => $consumable->id,
            'target_type' => User::class,
            'target_id' => $user->id,
            'action_type' => 'checkout',
            'quantity' => 2,
        ]);
    }

    public function test_user_sent_notification_upon_checkout()
    {
        Mail::fake();

        $consumable = Consumable::factory()->requiringAcceptance()->create();

        $user = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutConsumables()->create())
            ->postJson(route('api.consumables.checkout', $consumable), [
                'assigned_to' => $user->id,
            ]);

        Mail::assertSent(CheckoutConsumableMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_action_log_created_upon_checkout()
    {
        $consumable = Consumable::factory()->create();
        $actor = User::factory()->checkoutConsumables()->create();
        $user = User::factory()->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.consumables.checkout', $consumable), [
                'assigned_to' => $user->id,
                'note' => 'oh hi there',
            ]);

        $this->assertEquals(
            1,
            Actionlog::where([
                'action_type' => 'checkout',
                'target_id' => $user->id,
                'target_type' => User::class,
                'item_id' => $consumable->id,
                'item_type' => Consumable::class,
                'created_by' => $actor->id,
                'note' => 'oh hi there',
            ])->count(),
            'Log entry either does not exist or there are more than expected'
        );
    }
}
