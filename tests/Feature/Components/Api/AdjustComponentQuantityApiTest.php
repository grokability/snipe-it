<?php

use App\Enums\ActionType;
use App\Models\Actionlog;
use App\Models\Component;
use App\Models\User;

test('requires edit permission', function () {
    $component = Component::factory()->create(['qty' => 5]);

    $this->actingAsForApi(User::factory()->create())
        ->postJson(route('api.components.adjust-quantity', $component), [
            'amount' => 3,
            'note' => 'restock',
        ])
        ->assertForbidden();
});

test('signed amount writes quantity adjust log', function () {
    $component = Component::factory()->create(['qty' => 5]);

    $this->actingAsForApi(User::factory()->editComponents()->create())
        ->postJson(route('api.components.adjust-quantity', $component), [
            'amount' => 3,
            'note' => 'restock',
            'order_number' => 'PO-CMP',
        ])
        ->assertOk();

    expect((int) $component->fresh()->qty)->toBe(8);

    $log = Actionlog::where('item_type', Component::class)
        ->where('item_id', $component->id)
        ->where('action_type', ActionType::QuantityAdjust->value)
        ->latest('id')
        ->firstOrFail();

    expect((int) $log->quantity)->toBe(3);
    expect($log->orderItem->order->order_number)->toBe('PO-CMP');
});

test('note is required', function () {
    $component = Component::factory()->create(['qty' => 5]);

    $this->actingAsForApi(User::factory()->editComponents()->create())
        ->postJson(route('api.components.adjust-quantity', $component), ['amount' => 2])
        ->assertOk()
        ->assertJsonPath('status', 'error');
});
