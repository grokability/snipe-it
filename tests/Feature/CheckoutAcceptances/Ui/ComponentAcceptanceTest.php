<?php

namespace Tests\Feature\CheckoutAcceptances\Ui;

use App\Models\CheckoutAcceptance;
use App\Models\Component;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ComponentAcceptanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
    }

    public function test_component_checkout_accept_page_renders()
    {
        $checkoutAcceptance = CheckoutAcceptance::factory()
            ->pending()
            ->for(Component::factory()->create(), 'checkoutable')
            ->create();

        $this->actingAs($checkoutAcceptance->assignedTo)
            ->get(route('account.accept.item', $checkoutAcceptance))
            ->assertOk()
            ->assertViewIs('account.accept.create');
    }

    public function test_can_accept_component_checkout()
    {
        $assignee = User::factory()->create();
        $component = Component::factory()->create();

        $checkoutAcceptance = CheckoutAcceptance::factory()
            ->pending()
            ->for($assignee, 'assignedTo')
            ->for($component, 'checkoutable')
            ->create(['qty' => 2]);

        $this->actingAs($assignee)
            ->post(route('account.store-acceptance', $checkoutAcceptance), [
                'asset_acceptance' => 'accepted',
                'note' => 'A note here',
            ])
            ->assertRedirect();

        $this->assertNotNull($checkoutAcceptance->refresh()->accepted_at);
        $this->assertEquals('A note here', $checkoutAcceptance->note);

        $this->assertDatabaseHas('action_logs', [
            'action_type' => 'accepted',
            'target_id' => $assignee->id,
            'target_type' => User::class,
            'item_id' => $component->id,
            'item_type' => Component::class,
            'quantity' => 2,
        ]);
    }

    public function test_can_decline_component_checkout()
    {
        $assignee = User::factory()->create();
        $component = Component::factory()->create();

        $checkoutAcceptance = CheckoutAcceptance::factory()
            ->pending()
            ->for($assignee, 'assignedTo')
            ->for($component, 'checkoutable')
            ->create(['qty' => 2]);

        $this->actingAs($assignee)
            ->post(route('account.store-acceptance', $checkoutAcceptance), [
                'asset_acceptance' => 'declined',
                'note' => 'A note here',
            ])
            ->assertRedirect();

        $this->assertNotNull($checkoutAcceptance->refresh()->declined_at);
        $this->assertEquals('A note here', $checkoutAcceptance->note);

        $this->assertDatabaseHas('action_logs', [
            'action_type' => 'declined',
            'target_id' => $assignee->id,
            'target_type' => User::class,
            'item_id' => $component->id,
            'item_type' => Component::class,
            'quantity' => 2,
        ]);
    }
}
