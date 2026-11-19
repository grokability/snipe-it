<?php

use App\Models\Component;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

test('badge counts order items not distinct orders', function () {
    $component = Component::factory()->create();

    // One Order carries three positive-qty lines against the
    // same component. ordersCount would say 1, since it counts
    // distinct Orders. The API returns 3 rows, so the badge
    // must say 3.
    $order = Order::create(['order_number' => 'ORD-SAMEORDER']);
    foreach (range(1, 3) as $_) {
        OrderItem::create([
            'order_id' => $order->id,
            'item_type' => Component::class,
            'item_id' => $component->id,
            'qty' => 1,
        ]);
    }

    expect($component->orderItemsCount())->toBe(3);
});

test('badge excludes zero and negative qty lines', function () {
    // Corrections / consumption events ride on OrderItems with
    // zero or negative qty and are shown on the History tab, not
    // Orders. Must not inflate the Orders-tab badge.
    $component = Component::factory()->create();
    $order = Order::create(['order_number' => 'ORD-MIXEDQTY']);

    OrderItem::create([
        'order_id' => $order->id,
        'item_type' => Component::class,
        'item_id' => $component->id,
        'qty' => 5,
    ]);
    OrderItem::create([
        'order_id' => $order->id,
        'item_type' => Component::class,
        'item_id' => $component->id,
        'qty' => 0,
    ]);
    OrderItem::create([
        'order_id' => $order->id,
        'item_type' => Component::class,
        'item_id' => $component->id,
        'qty' => -2,
    ]);

    expect($component->orderItemsCount())->toBe(1);
});

test('badge excludes initial inventory anchor', function () {
    // writeInitialOrderIfStocked writes an Order with
    // order_number=null + one positive-qty OrderItem whenever
    // a Component is created with qty > 0. That anchor shouldn't
    // count toward the Orders-tab badge or surface in the table,
    // because admins think of "orders" as named acquisitions.
    $component = Component::factory()->create();

    expect($component->orderItemsCount())->toBe(0);
});

test('badge matches api row count', function () {
    $component = Component::factory()->create();
    $order = Order::create(['order_number' => 'ORD-APIMATCH']);

    foreach (range(1, 2) as $_) {
        OrderItem::create([
            'order_id' => $order->id,
            'item_type' => Component::class,
            'item_id' => $component->id,
            'qty' => 4,
        ]);
    }

    $apiRows = $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.order-items.index', [
            'item_type' => Component::class,
            'item_id' => $component->id,
        ]))
        ->assertOk()
        ->json('rows');

    expect($component->orderItemsCount())->toBe(count($apiRows));
    expect($component->orderItemsCount())->toBe(2);
});
