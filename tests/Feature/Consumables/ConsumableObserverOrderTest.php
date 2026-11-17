<?php

namespace Tests\Feature\Consumables;

use App\Enums\ActionType;
use App\Models\Actionlog;
use App\Models\Consumable;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Supplier;
use App\Models\User;
use Tests\TestCase;

/**
 * HasOrders writes an initial Order + OrderItem for a stocked consumable.
 * Loggable writes the paired `create` action_log entry, linked
 * back to that OrderItem via order_item_id.
 */
class ConsumableObserverOrderTest extends TestCase
{
    public function test_consumable_create_writes_matching_order_and_order_item(): void
    {
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

        $this->assertSame(100, (int) $line->qty);
        $this->assertEquals(5.50, (float) $line->price);
        $this->assertSame($actor->id, (int) $line->created_by);

        $order = Order::findOrFail($line->order_id);
        $this->assertSame($supplier->id, (int) $order->supplier_id);
        $this->assertSame($actor->company_id, $order->company_id);
        $this->assertSame('2026-05-01', $order->purchase_date->toDateString());
        $this->assertSame($actor->id, (int) $order->created_by);
    }

    public function test_create_action_log_links_to_order_item_id(): void
    {
        $consumable = Consumable::factory()->create(['qty' => 20]);

        $line = OrderItem::where('item_type', Consumable::class)
            ->where('item_id', $consumable->id)
            ->firstOrFail();

        $log = Actionlog::where('item_type', Consumable::class)
            ->where('item_id', $consumable->id)
            ->where('action_type', ActionType::Create->value)
            ->firstOrFail();

        $this->assertSame($line->id, (int) $log->order_item_id);
    }

    public function test_order_currency_prefers_location_currency_when_set(): void
    {
        $location = Location::factory()->create(['currency' => 'EUR']);
        $consumable = Consumable::factory()->for($location)->create();

        $line = $consumable->orderItems()->firstOrFail();

        $this->assertSame('EUR', $line->order->currency);
    }
}
