<?php

use App\Enums\ActionType;
use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\User;

test('supplier purchase date unit cost and currency land on order and order item', function () {
    $accessory = Accessory::factory()->create(['qty' => 2]);
    $supplier = Supplier::factory()->create();

    $this->actingAsForApi(User::factory()->editAccessories()->create())
        ->postJson(route('api.accessories.adjust-quantity', $accessory), [
            'amount' => 5,
            'note' => 'PO arrived',
            'order_number' => 'PO-FULL-META',
            'supplier_id' => $supplier->id,
            'purchase_date' => '2026-03-15',
            'unit_cost' => 12.3456,
            'currency' => 'EUR',
        ])
        ->assertOk();

    $log = Actionlog::where('item_type', Accessory::class)
        ->where('item_id', $accessory->id)
        ->where('action_type', ActionType::QuantityAdjust->value)
        ->latest('id')
        ->firstOrFail();

    $order = $log->orderItem->order;
    expect($order->order_number)->toBe('PO-FULL-META');
    expect((int) $order->supplier_id)->toBe($supplier->id);
    expect($order->purchase_date->toDateString())->toBe('2026-03-15');
    expect($order->currency)->toBe('EUR');

    $line = $order->orderItems()->firstOrFail();
    expect($line->item_type)->toBe(Accessory::class);
    expect((int) $line->item_id)->toBe($accessory->id);
    expect((int) $line->qty)->toBe(5);
    expect((float) $line->price)->toEqual(12.3456);
});

test('audit only zero delta with no order metadata skips order creation', function () {
    // Zero delta and no supplier / order number / date / cost /
    // currency = pure inventory audit. No Order row should be
    // created for these, since there was no transaction.
    $accessory = Accessory::factory()->create(['qty' => 5]);
    $ordersBefore = Order::count();

    $this->actingAsForApi(User::factory()->editAccessories()->create())
        ->postJson(route('api.accessories.adjust-quantity', $accessory), [
            'amount' => 0,
            'note' => 'shelf count matches',
        ])
        ->assertOk();

    expect(Order::count())->toBe($ordersBefore);

    $log = Actionlog::where('item_type', Accessory::class)
        ->where('item_id', $accessory->id)
        ->where('action_type', ActionType::QuantityAdjust->value)
        ->latest('id')
        ->firstOrFail();

    expect($log->order_item_id)->toBeNull();
});

test('multiple adjusts with same order number dedupe to one order', function () {
    // Repeated qty adjusts referencing the same order_number should
    // reuse the existing Order row (dedupe on order_number +
    // supplier + company + purchase_date), while each adjust still
    // produces its own OrderItem line (one line per event).
    $accessory = Accessory::factory()->create(['qty' => 0]);
    $supplier = Supplier::factory()->create();
    $actor = User::factory()->editAccessories()->create();

    foreach ([3, 4] as $delta) {
        $this->actingAsForApi($actor)
            ->postJson(route('api.accessories.adjust-quantity', $accessory), [
                'amount' => $delta,
                'note' => 'same receipt, split line',
                'order_number' => 'ORD-DEDUPE',
                'supplier_id' => $supplier->id,
                'purchase_date' => '2026-04-01',
            ])
            ->assertOk();
    }

    $orders = Order::where('order_number', 'ORD-DEDUPE')->get();
    expect($orders)->toHaveCount(1, 'Same order_number + supplier + date should reuse the existing Order row.');

    $items = $orders->first()->orderItems;
    expect($items)->toHaveCount(2, 'Each adjust event should have its own OrderItem line.');
    expect($items->pluck('qty')->map(fn ($q) => (int) $q)->all())->toEqualCanonicalizing([3, 4]);
});

test('same order number on different purchase dates creates distinct orders', function () {
    // Snipe-IT has no partial-receipt concept. Every Order is a
    // completed receipt-in-hand, so the same order_number appearing
    // with two different purchase_dates represents two distinct
    // events and each gets its own Order row.
    $accessory = Accessory::factory()->create(['qty' => 0]);
    $supplier = Supplier::factory()->create();
    $actor = User::factory()->editAccessories()->create();

    foreach (['2026-04-01', '2026-04-15'] as $date) {
        $this->actingAsForApi($actor)
            ->postJson(route('api.accessories.adjust-quantity', $accessory), [
                'amount' => 5,
                'note' => 'later receipt on same order number',
                'order_number' => 'ORD-SPLIT-DATE',
                'supplier_id' => $supplier->id,
                'purchase_date' => $date,
            ])
            ->assertOk();
    }

    expect(Order::where('order_number', 'ORD-SPLIT-DATE')->count())->toBe(2, 'Different purchase_dates under the same order_number should be distinct Orders.');
});

test('blank order number creates a distinct order per event', function () {
    // A blank order_number is a distinct transaction each time, not
    // a bucket to pool anonymous receipts into. Each adjust with
    // no order_number gets its own Order row.
    $accessory = Accessory::factory()->create(['qty' => 0]);
    $supplier = Supplier::factory()->create();
    $actor = User::factory()->editAccessories()->create();
    $ordersBefore = Order::count();

    foreach ([2, 3] as $delta) {
        $this->actingAsForApi($actor)
            ->postJson(route('api.accessories.adjust-quantity', $accessory), [
                'amount' => $delta,
                'note' => 'anonymous receipt',
                'supplier_id' => $supplier->id,
            ])
            ->assertOk();
    }

    expect(Order::count())->toBe($ordersBefore + 2);
});
