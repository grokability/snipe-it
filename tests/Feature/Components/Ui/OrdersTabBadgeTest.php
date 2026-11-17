<?php

namespace Tests\Feature\Components\Ui;

use App\Models\Component;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Tests\TestCase;

/**
 * Regression pin: the Orders-tab badge used to call ordersCount
 * (distinct Orders through HasManyThrough) while the tab's table
 * API returned OrderItems. One Order with multiple positive-qty
 * lines produced badge=1 but table=N rows. The badge now mirrors
 * the API's own filter via orderItemsCount, including the "parent
 * Order must have a non-null order_number" filter that keeps
 * initial-inventory anchors (writeInitialOrderIfStocked) from
 * surfacing in the Orders UI.
 */
class OrdersTabBadgeTest extends TestCase
{
    public function test_badge_counts_order_items_not_distinct_orders(): void
    {
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

        $this->assertSame(3, $component->orderItemsCount());
    }

    public function test_badge_excludes_zero_and_negative_qty_lines(): void
    {
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

        $this->assertSame(1, $component->orderItemsCount());
    }

    public function test_badge_excludes_initial_inventory_anchor(): void
    {
        // writeInitialOrderIfStocked writes an Order with
        // order_number=null + one positive-qty OrderItem whenever
        // a Component is created with qty > 0. That anchor shouldn't
        // count toward the Orders-tab badge or surface in the table,
        // because admins think of "orders" as named acquisitions.
        $component = Component::factory()->create();

        $this->assertSame(0, $component->orderItemsCount());
    }

    public function test_badge_matches_api_row_count(): void
    {
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

        $this->assertSame(count($apiRows), $component->orderItemsCount());
        $this->assertSame(2, $component->orderItemsCount());
    }
}
