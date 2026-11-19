<?php

use App\Enums\ActionType;
use App\Models\Actionlog;
use App\Models\Component;
use App\Models\Supplier;
use App\Models\User;

test('qty change via api creates quantity adjust log', function () {
    $component = Component::factory()->create(['qty' => 5]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->patchJson(route('api.components.update', $component), ['qty' => 12, 'order_number' => 'PO-API'])
        ->assertOk();

    expect((int) $component->fresh()->qty)->toBe(12);

    $log = Actionlog::where('item_type', Component::class)
        ->where('item_id', $component->id)
        ->where('action_type', ActionType::QuantityAdjust->value)
        ->latest('id')
        ->firstOrFail();

    expect((int) $log->quantity)->toBe(7);
    expect($log->orderItem->order->order_number)->toBe('PO-API');
    expect($log->note)->not->toBeEmpty();
});

test('default supplier id is editable on update', function () {
    // default_supplier_id on the parent is the "typical supplier"
    // template that seeds new Orders. It stays editable after create;
    // per-acquisition supplier lives on Order.supplier_id and is
    // unaffected by this write.
    $original = Supplier::factory()->create();
    $component = Component::factory()->create(['default_supplier_id' => $original->id]);
    $newSupplier = Supplier::factory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->patchJson(route('api.components.update', $component), ['default_supplier_id' => $newSupplier->id])
        ->assertOk();

    expect((int) $component->fresh()->default_supplier_id)->toBe($newSupplier->id);
});

test('qty only change via api creates no update log entry', function () {
    $component = Component::factory()->create(['qty' => 5]);

    $updateLogsBefore = Actionlog::where('item_type', Component::class)
        ->where('item_id', $component->id)
        ->where('action_type', 'update')
        ->count();

    // company_id has to be echoed back — the controller unconditionally
    // sets $component->company_id = Company::getIdForCurrentUser(...),
    // which coerces a missing company_id in the payload to null and
    // would silently mark the model dirty. Pre-existing quirk, not
    // introduced by the qty-adjust changes.
    $this->actingAsForApi(User::factory()->superuser()->create())
        ->patchJson(route('api.components.update', $component), [
            'qty' => 8,
            'company_id' => $component->company_id,
        ])
        ->assertOk();

    $updateLogsAfter = Actionlog::where('item_type', Component::class)
        ->where('item_id', $component->id)
        ->where('action_type', 'update')
        ->count();

    // Only qty changed. The QuantityAdjust log captures that change;
    // no separate 'update' log should exist because nothing else was dirty.
    expect($updateLogsAfter)->toBe($updateLogsBefore);
    expect(Actionlog::where('item_type', Component::class)
        ->where('item_id', $component->id)
        ->where('action_type', ActionType::QuantityAdjust->value)
        ->count())->toBe(1);
});
