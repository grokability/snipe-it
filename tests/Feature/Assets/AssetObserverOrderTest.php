<?php

use App\Models\Asset;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Supplier;
use App\Models\User;

test('asset create with order number writes matching order and order item', function () {
    $actor = User::factory()->createAssets()->create();
    $supplier = Supplier::factory()->create();

    $asset = Asset::factory()->create([
        'created_by' => $actor->id,
        'company_id' => $actor->company_id,
        'supplier_id' => $supplier->id,
        'order_number' => 'PO-OBS-1',
        'purchase_date' => '2026-04-01',
        'purchase_cost' => 199.99,
    ]);

    $line = OrderItem::where('item_type', Asset::class)
        ->where('item_id', $asset->id)
        ->firstOrFail();

    expect((int) $line->qty)->toBe(1);
    expect((float) $line->price)->toEqual(199.99);

    $order = Order::findOrFail($line->order_id);
    expect($order->order_number)->toBe('PO-OBS-1');
    expect((int) $order->supplier_id)->toBe($supplier->id);
    expect($order->company_id)->toBe($actor->company_id);
    expect($order->purchase_date->toDateString())->toBe('2026-04-01');
});

test('two assets with same order tuple share one order row', function () {
    $actor = User::factory()->createAssets()->create();
    $supplier = Supplier::factory()->create();

    $shared = [
        'created_by' => $actor->id,
        'company_id' => $actor->company_id,
        'supplier_id' => $supplier->id,
        'order_number' => 'PO-OBS-SHARED',
        'purchase_date' => '2026-04-02',
    ];

    $first = Asset::factory()->create($shared + ['purchase_cost' => 100]);
    $second = Asset::factory()->create($shared + ['purchase_cost' => 200]);

    $orders = Order::where('order_number', 'PO-OBS-SHARED')
        ->where('supplier_id', $supplier->id)
        ->where('company_id', $actor->company_id)
        ->get();

    expect($orders)->toHaveCount(1, 'Same (order_number, supplier, company) tuple must dedupe.');

    $lineIds = OrderItem::where('item_type', Asset::class)
        ->whereIn('item_id', [$first->id, $second->id])
        ->pluck('order_id')
        ->unique()
        ->values();

    expect($lineIds)->toHaveCount(1);
    expect((int) $lineIds->first())->toBe($orders->first()->id);
});

test('blank order number creates a distinct order per asset', function () {
    $actor = User::factory()->createAssets()->create();
    $supplier = Supplier::factory()->create();
    $ordersBefore = Order::count();

    $shared = [
        'created_by' => $actor->id,
        'company_id' => $actor->company_id,
        'supplier_id' => $supplier->id,
        'order_number' => null,
    ];

    Asset::factory()->create($shared);
    Asset::factory()->create($shared);

    // Blank order_number = distinct transaction, so both assets get
    // their own Order rows even though supplier + company match.
    expect(Order::count())->toBe($ordersBefore + 2);
});

test('asset purchase cost change syncs to linked order item', function () {
    // #19564 sync: editing purchase_cost on a saved asset (single
    // edit, bulk edit, importer update, or API) must write through
    // to the linked OrderItem's price so order-side reads don't
    // diverge from the current asset row.
    $supplier = Supplier::factory()->create();

    $asset = Asset::factory()->create([
        'supplier_id' => $supplier->id,
        'order_number' => 'PO-EDIT-COST',
        'purchase_cost' => 100.00,
    ]);

    $asset->purchase_cost = 175.50;
    $asset->save();

    $line = OrderItem::where('item_type', Asset::class)
        ->where('item_id', $asset->id)
        ->firstOrFail();

    expect((float) $line->price)->toEqual(175.50);
});

test('asset order number change repoints line and cleans up orphan', function () {
    // Rename an asset's order_number. The asset's OrderItem must
    // detach from the old Order, attach to a firstOrNew'd Order
    // matching the new (order_number, supplier_id, company_id)
    // tuple, and the old Order must be deleted when it's left
    // with no other OrderItems.
    $supplier = Supplier::factory()->create();

    $asset = Asset::factory()->create([
        'supplier_id' => $supplier->id,
        'order_number' => 'PO-OLD',
    ]);

    $oldOrderId = OrderItem::where('item_type', Asset::class)
        ->where('item_id', $asset->id)
        ->value('order_id');

    expect(Order::find($oldOrderId))->not->toBeNull();

    $asset->order_number = 'PO-NEW';
    $asset->save();

    $line = OrderItem::where('item_type', Asset::class)
        ->where('item_id', $asset->id)
        ->firstOrFail();

    $newOrder = Order::find($line->order_id);

    expect($newOrder->order_number)->toBe('PO-NEW');
    $this->assertNotSame($oldOrderId, $line->order_id);
    expect(Order::find($oldOrderId))->toBeNull('The old Order should be deleted after the only OrderItem moves away.');
});

test('asset order number change preserves old order when it has siblings', function () {
    // Two assets share PO-SHARED. Rename asset A to PO-NEW. Old
    // Order (PO-SHARED) must survive because asset B still points
    // at it.
    $supplier = Supplier::factory()->create();

    $assetA = Asset::factory()->create([
        'supplier_id' => $supplier->id,
        'order_number' => 'PO-SHARED',
    ]);
    $assetB = Asset::factory()->create([
        'supplier_id' => $supplier->id,
        'company_id' => $assetA->company_id,
        'order_number' => 'PO-SHARED',
    ]);

    $sharedOrderId = OrderItem::where('item_type', Asset::class)
        ->where('item_id', $assetA->id)
        ->value('order_id');

    expect(OrderItem::where('item_type', Asset::class)->where('item_id', $assetB->id)->value('order_id'))->toBe($sharedOrderId, 'Precondition: both assets share the same Order.');

    $assetA->order_number = 'PO-NEW';
    $assetA->save();

    expect(Order::find($sharedOrderId))->not->toBeNull('The old Order must survive because asset B still references it.');
});

test('unrelated save does not touch the linked order item', function () {
    // Sanity: touching a field other than purchase_cost /
    // order_number leaves the OrderItem's price and order_id
    // completely untouched.
    $supplier = Supplier::factory()->create();

    $asset = Asset::factory()->create([
        'supplier_id' => $supplier->id,
        'order_number' => 'PO-NOTES',
        'purchase_cost' => 42.00,
    ]);

    $originalLine = OrderItem::where('item_type', Asset::class)
        ->where('item_id', $asset->id)
        ->firstOrFail();

    $asset->notes = 'edited notes';
    $asset->save();

    $freshLine = OrderItem::where('item_type', Asset::class)
        ->where('item_id', $asset->id)
        ->firstOrFail();

    expect($freshLine->id)->toBe($originalLine->id);
    expect($freshLine->order_id)->toBe($originalLine->order_id);
    expect((float) $freshLine->price)->toEqual((float) $originalLine->price);
});
