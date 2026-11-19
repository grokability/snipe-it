<?php

use App\Enums\ActionType;
use App\Models\Actionlog;
use App\Models\Component;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Supplier;
use App\Models\User;

test('component create writes matching order and order item', function () {
    $actor = User::factory()->superuser()->create();
    $supplier = Supplier::factory()->create();

    $component = Component::factory()
        ->withInitialAcquisition($supplier, 89.00, '2026-06-01')
        ->create([
            'created_by' => $actor->id,
            'company_id' => $actor->company_id,
            'qty' => 12,
        ]);

    $line = OrderItem::where('item_type', Component::class)
        ->where('item_id', $component->id)
        ->firstOrFail();

    expect((int) $line->qty)->toBe(12);
    expect((float) $line->price)->toEqual(89.00);
    expect((int) $line->created_by)->toBe($actor->id);

    $order = Order::findOrFail($line->order_id);
    expect((int) $order->supplier_id)->toBe($supplier->id);
    expect($order->company_id)->toBe($actor->company_id);
    expect($order->purchase_date->toDateString())->toBe('2026-06-01');
    expect((int) $order->created_by)->toBe($actor->id);
});

test('create action log links to order item id', function () {
    $component = Component::factory()->create(['qty' => 4]);

    $line = OrderItem::where('item_type', Component::class)
        ->where('item_id', $component->id)
        ->firstOrFail();

    $log = Actionlog::where('item_type', Component::class)
        ->where('item_id', $component->id)
        ->where('action_type', ActionType::Create->value)
        ->firstOrFail();

    expect((int) $log->order_item_id)->toBe($line->id);
});
