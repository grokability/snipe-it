<?php

namespace Tests\Feature\Accessories;

use App\Enums\ActionType;
use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Supplier;
use App\Models\User;
use Tests\TestCase;

/**
 * HasOrders writes an initial Order + OrderItem for a stocked accessory.
 * Loggable writes the paired `create` action_log entry, linked to that OrderItem via
 * order_item_id so history-tab renders can walk to the acquisition
 * context in one hop.
 */
class AccessoryObserverOrderTest extends TestCase
{
    public function test_accessory_create_writes_matching_order_and_order_item(): void
    {
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

        $this->assertSame(7, (int) $line->qty);
        $this->assertEquals(12.34, (float) $line->price);
        $this->assertSame($actor->id, (int) $line->created_by);

        $order = Order::findOrFail($line->order_id);
        $this->assertSame($supplier->id, (int) $order->supplier_id);
        $this->assertSame($actor->company_id, $order->company_id);
        $this->assertSame('2026-04-01', $order->purchase_date->toDateString());
        $this->assertSame($actor->id, (int) $order->created_by);
    }

    public function test_create_action_log_links_to_order_item_id(): void
    {
        $accessory = Accessory::factory()->create(['qty' => 3]);

        $line = OrderItem::where('item_type', Accessory::class)
            ->where('item_id', $accessory->id)
            ->firstOrFail();

        $log = Actionlog::where('item_type', Accessory::class)
            ->where('item_id', $accessory->id)
            ->where('action_type', ActionType::Create->value)
            ->firstOrFail();

        $this->assertSame($line->id, (int) $log->order_item_id);
    }

    public function test_order_currency_prefers_location_currency_when_set(): void
    {
        $location = Location::factory()->create(['currency' => 'JPY']);
        $accessory = Accessory::factory()->for($location)->create();

        $line = $accessory->orderItems()->firstOrFail();

        $this->assertSame('JPY', $line->order->currency);
    }

    public function test_every_new_accessory_gets_its_own_order_and_line(): void
    {
        $ordersBefore = Order::count();
        $linesBefore = OrderItem::count();

        Accessory::factory()->count(3)->create();

        // Each stocked create fires HasOrders, which writes 1 Order + 1
        // OrderItem. No dedupe on accessory create (order_number is
        // null so blank-order semantic applies).
        $this->assertSame($ordersBefore + 3, Order::count());
        $this->assertSame($linesBefore + 3, OrderItem::count());
    }
}
