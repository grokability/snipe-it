<?php

use App\Enums\ActionType;
use App\Models\Actionlog;
use App\Models\Consumable;
use App\Models\User;

test('requires edit permission', function () {
    $consumable = Consumable::factory()->create(['qty' => 5]);

    $this->actingAsForApi(User::factory()->create())
        ->postJson(route('api.consumables.adjust-quantity', $consumable), [
            'amount' => 3,
            'note' => 'restock',
        ])
        ->assertForbidden();
});

test('signed amount writes quantity adjust log', function () {
    $consumable = Consumable::factory()->create(['qty' => 5]);

    $this->actingAsForApi(User::factory()->editConsumables()->create())
        ->postJson(route('api.consumables.adjust-quantity', $consumable), [
            'amount' => 3,
            'note' => 'restock',
            'order_number' => 'PO-CON',
        ])
        ->assertOk();

    expect((int) $consumable->fresh()->qty)->toBe(8);

    $log = Actionlog::where('item_type', Consumable::class)
        ->where('item_id', $consumable->id)
        ->where('action_type', ActionType::QuantityAdjust->value)
        ->latest('id')
        ->firstOrFail();

    expect((int) $log->quantity)->toBe(3);
    expect($log->orderItem->order->order_number)->toBe('PO-CON');
});

test('note is required', function () {
    $consumable = Consumable::factory()->create(['qty' => 5]);

    $this->actingAsForApi(User::factory()->editConsumables()->create())
        ->postJson(route('api.consumables.adjust-quantity', $consumable), ['amount' => 2])
        ->assertOk()
        ->assertJsonPath('status', 'error');
});
