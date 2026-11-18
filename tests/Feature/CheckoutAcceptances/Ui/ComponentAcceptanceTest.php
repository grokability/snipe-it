<?php

use App\Models\CheckoutAcceptance;
use App\Models\Component;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake();
});

test('component checkout accept page renders', function () {
    $checkoutAcceptance = CheckoutAcceptance::factory()
        ->pending()
        ->for(Component::factory()->create(), 'checkoutable')
        ->create();

    $this->actingAs($checkoutAcceptance->assignedTo)
        ->get(route('account.accept.item', $checkoutAcceptance))
        ->assertOk()
        ->assertViewIs('account.accept.create');
});

test('can accept component checkout', function () {
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

    expect($checkoutAcceptance->refresh()->accepted_at)->not->toBeNull();
    expect($checkoutAcceptance->note)->toEqual('A note here');

    $this->assertDatabaseHas('action_logs', [
        'action_type' => 'accepted',
        'target_id' => $assignee->id,
        'target_type' => User::class,
        'item_id' => $component->id,
        'item_type' => Component::class,
        'quantity' => 2,
    ]);
});

test('can decline component checkout', function () {
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

    expect($checkoutAcceptance->refresh()->declined_at)->not->toBeNull();
    expect($checkoutAcceptance->note)->toEqual('A note here');

    $this->assertDatabaseHas('action_logs', [
        'action_type' => 'declined',
        'target_id' => $assignee->id,
        'target_type' => User::class,
        'item_id' => $component->id,
        'item_type' => Component::class,
        'quantity' => 2,
    ]);
});
