<?php

use App\Enums\ActionType;
use App\Models\Actionlog;
use App\Models\Consumable;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Supplier;
use App\Models\User;

test('consumable create writes matching order and order item', function () {
    $actor = User::factory()->createConsumables()->create();
    $supplier = Supplier::factory()->create();

    $consumable = Consumable::factory()
        ->withInitialAcquisition($supplier, 5.50, '2026-05-01')
        ->create([
            'created_by' => $actor->id,
            'company_id' => $actor->company_id,
            'qty' => 100,
        ]);

    $line = OrderItem::where('item_type', Consumable::class)
        ->where('item_id', $consumable->id)
        ->firstOrFail();

    expect((int) $line->qty)->toBe(100);
    expect((float) $line->price)->toEqual(5.50);
    expect((int) $line->created_by)->toBe($actor->id);

    $order = Order::findOrFail($line->order_id);
    expect((int) $order->supplier_id)->toBe($supplier->id);
    expect($order->company_id)->toBe($actor->company_id);
    expect($order->purchase_date->toDateString())->toBe('2026-05-01');
    expect((int) $order->created_by)->toBe($actor->id);
});

test('create action log links to order item id', function () {
    $consumable = Consumable::factory()->create(['qty' => 20]);

    $line = OrderItem::where('item_type', Consumable::class)
        ->where('item_id', $consumable->id)
        ->firstOrFail();

    $log = Actionlog::where('item_type', Consumable::class)
        ->where('item_id', $consumable->id)
        ->where('action_type', ActionType::Create->value)
        ->firstOrFail();

    expect((int) $log->order_item_id)->toBe($line->id);
});

test('order currency prefers location currency when set', function () {
    $location = Location::factory()->create(['currency' => 'EUR']);
    $consumable = Consumable::factory()->for($location)->create();

    $line = $consumable->orderItems()->firstOrFail();

    expect($line->order->currency)->toBe('EUR');
});
