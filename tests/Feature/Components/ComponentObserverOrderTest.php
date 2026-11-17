<?php

namespace Tests\Feature\Components;

use App\Enums\ActionType;
use App\Models\Actionlog;
use App\Models\Component;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Supplier;
use App\Models\User;
use Tests\TestCase;

/**
 * HasOrders writes an initial Order + OrderItem for a stocked component.
 * Loggable writes the paired `create` action_log entry, linked back
 * to that OrderItem via order_item_id.
 */
class ComponentObserverOrderTest extends TestCase
{
    public function test_component_create_writes_matching_order_and_order_item(): void
    {
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

        $this->assertSame(12, (int) $line->qty);
        $this->assertEquals(89.00, (float) $line->price);
        $this->assertSame($actor->id, (int) $line->created_by);

        $order = Order::findOrFail($line->order_id);
        $this->assertSame($supplier->id, (int) $order->supplier_id);
        $this->assertSame($actor->company_id, $order->company_id);
        $this->assertSame('2026-06-01', $order->purchase_date->toDateString());
        $this->assertSame($actor->id, (int) $order->created_by);
    }

    public function test_create_action_log_links_to_order_item_id(): void
    {
        $component = Component::factory()->create(['qty' => 4]);

        $line = OrderItem::where('item_type', Component::class)
            ->where('item_id', $component->id)
            ->firstOrFail();

        $log = Actionlog::where('item_type', Component::class)
            ->where('item_id', $component->id)
            ->where('action_type', ActionType::Create->value)
            ->firstOrFail();

        $this->assertSame($line->id, (int) $log->order_item_id);
    }
}
