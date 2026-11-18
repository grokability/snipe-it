<?php

use App\Models\CheckoutAcceptance;
use App\Models\Consumable;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake();
});

test('can accept consumable checkout', function () {
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

    expect($checkoutAcceptance->refresh()->accepted_at)->not->toBeNull();
    expect($checkoutAcceptance->note)->toEqual('A note here');

    $assignee->consumables->contains($consumable);

    $this->assertDatabaseHas('action_logs', [
        'action_type' => 'accepted',
        'target_id' => $assignee->id,
        'target_type' => User::class,
        'item_id' => $consumable->id,
        'item_type' => Consumable::class,
        'quantity' => 2,
    ]);
});

test('can decline consumable checkout', function () {
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

    expect($checkoutAcceptance->refresh()->declined_at)->not->toBeNull();
    expect($checkoutAcceptance->note)->toEqual('A note here');

    $assignee->consumables->doesntContain($consumable);

    $this->assertDatabaseHas('action_logs', [
        'action_type' => 'declined',
        'target_id' => $assignee->id,
        'target_type' => User::class,
        'item_id' => $consumable->id,
        'item_type' => Consumable::class,
        'quantity' => 2,
    ]);
});
