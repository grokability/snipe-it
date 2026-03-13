<?php

namespace Tests\Feature\CheckoutAcceptances\Ui;

use App\Models\CheckoutAcceptance;
use App\Models\Consumable;
use App\Models\User;
use Tests\TestCase;

class ConsumableAcceptanceTest extends TestCase
{
    public function test_can_accept_consumable_checkout()
    {
        $assignee = User::factory()->create();
        $consumable = Consumable::factory()->create();

        $checkoutAcceptance = CheckoutAcceptance::factory()
            ->pending()
            ->for($assignee, 'assignedTo')
            ->for($consumable, 'checkoutable')
            ->create(['qty' => 2]);

        $this->actingAs($assignee)
            ->post(route('account.store-acceptance', $checkoutAcceptance), [
                'asset_acceptance' => 'accepted',
                'note' => 'A note here',
            ])
            ->assertRedirect();

        $this->assertNotNull($checkoutAcceptance->refresh()->accepted_at);
        $this->assertEquals('A note here', $checkoutAcceptance->note);

        $assignee->consumables->contains($consumable);

        $this->assertDatabaseHas('action_logs', [
            'action_type' => 'accepted',
            'target_id' => $assignee->id,
            'target_type' => User::class,
            'item_id' => $consumable->id,
            'item_type' => Consumable::class,
            'quantity' => 2,
        ]);
    }

    public function test_can_decline_consumable_checkout()
    {
        $assignee = User::factory()->create();
        $consumable = Consumable::factory()->create();

        $checkoutAcceptance = CheckoutAcceptance::factory()
            ->pending()
            ->for($assignee, 'assignedTo')
            ->for($consumable, 'checkoutable')
            ->create(['qty' => 2]);

        $this->actingAs($assignee)
            ->post(route('account.store-acceptance', $checkoutAcceptance), [
                'asset_acceptance' => 'declined',
                'note' => 'A note here',
            ])
            ->assertRedirect();

        $this->assertNotNull($checkoutAcceptance->refresh()->declined_at);
        $this->assertEquals('A note here', $checkoutAcceptance->note);

        $assignee->consumables->doesntContain($consumable);

        $this->assertDatabaseHas('action_logs', [
            'action_type' => 'declined',
            'target_id' => $assignee->id,
            'target_type' => User::class,
            'item_id' => $consumable->id,
            'item_type' => Consumable::class,
            'quantity' => 2,
        ]);
    }
}
