<?php

use App\Enums\ActionType;
use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Supplier;
use App\Models\User;

test('accessory create writes matching order and order item', function () {
    $actor = User::factory()->createAccessories()->create();
    $supplier = Supplier::factory()->create();

    $accessory = Accessory::factory()
        ->withInitialAcquisition($supplier, 12.34, '2026-04-01')
        ->create([
            'created_by' => $actor->id,
            'company_id' => $actor->company_id,
            'qty' => 7,
        ]);

    $line = OrderItem::where('item_type', Accessory::class)
        ->where('item_id', $accessory->id)
        ->firstOrFail();

    expect((int) $line->qty)->toBe(7);
    expect((float) $line->price)->toEqual(12.34);
    expect((int) $line->created_by)->toBe($actor->id);

    $order = Order::findOrFail($line->order_id);
    expect((int) $order->supplier_id)->toBe($supplier->id);
    expect($order->company_id)->toBe($actor->company_id);
    expect($order->purchase_date->toDateString())->toBe('2026-04-01');
    expect((int) $order->created_by)->toBe($actor->id);
});

test('create action log links to order item id', function () {
    $accessory = Accessory::factory()->create(['qty' => 3]);

    $line = OrderItem::where('item_type', Accessory::class)
        ->where('item_id', $accessory->id)
        ->firstOrFail();

    $log = Actionlog::where('item_type', Accessory::class)
        ->where('item_id', $accessory->id)
        ->where('action_type', ActionType::Create->value)
        ->firstOrFail();

    expect((int) $log->order_item_id)->toBe($line->id);
});

test('order currency prefers location currency when set', function () {
    $location = Location::factory()->create(['currency' => 'JPY']);
    $accessory = Accessory::factory()->for($location)->create();

    $line = $accessory->orderItems()->firstOrFail();

    expect($line->order->currency)->toBe('JPY');
});

test('every new accessory gets its own order and line', function () {
    $ordersBefore = Order::count();
    $linesBefore = OrderItem::count();

    Accessory::factory()->count(3)->create();

    // Each stocked create fires HasOrders, which writes 1 Order + 1
    // OrderItem. No dedupe on accessory create (order_number is
    // null so blank-order semantic applies).
    expect(Order::count())->toBe($ordersBefore + 3);
    expect(OrderItem::count())->toBe($linesBefore + 3);
});
