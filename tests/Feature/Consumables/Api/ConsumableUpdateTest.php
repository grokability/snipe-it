<?php

use App\Enums\ActionType;
use App\Models\Actionlog;
use App\Models\Category;
use App\Models\Consumable;
use App\Models\Supplier;
use App\Models\User;

test('can update consumable via patch without category type', function () {
    $consumable = Consumable::factory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->patchJson(route('api.consumables.update', $consumable), [
            'name' => 'Test Consumable',
        ])
        ->assertOk()
        ->assertStatusMessageIs('success')
        ->assertStatus(200)
        ->json();

    $consumable->refresh();
    expect($consumable->name)->toEqual('Test Consumable', 'Name was not updated');
});

test('cannot update consumable via patch with invalid category type', function () {
    $category = Category::factory()->create(['category_type' => 'asset']);
    $consumable = Consumable::factory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->patchJson(route('api.consumables.update', $consumable), [
            'name' => 'Test Consumable',
            'category_id' => $category->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('error')
        ->assertStatus(200)
        ->json();

    $consumable->refresh();
    $this->assertNotEquals('Test Consumable', $consumable->name, 'Name was not updated');
    $this->assertNotEquals('consumable', $consumable->category_id, 'Category was not updated');
});

test('qty change via api creates quantity adjust log', function () {
    $consumable = Consumable::factory()->create(['qty' => 5]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->patchJson(route('api.consumables.update', $consumable), ['qty' => 12, 'order_number' => 'PO-API'])
        ->assertOk();

    expect((int) $consumable->fresh()->qty)->toBe(12);

    $log = Actionlog::where('item_type', Consumable::class)
        ->where('item_id', $consumable->id)
        ->where('action_type', ActionType::QuantityAdjust->value)
        ->latest('id')
        ->firstOrFail();

    expect((int) $log->quantity)->toBe(7);
    expect($log->orderItem->order->order_number)->toBe('PO-API');
    expect($log->note)->not->toBeEmpty();
});

test('default supplier id is editable on update', function () {
    // default_supplier_id on the parent is the "typical supplier"
    // template — see ComponentUpdateTest for the full rationale.
    $original = Supplier::factory()->create();
    $consumable = Consumable::factory()->create(['default_supplier_id' => $original->id]);
    $newSupplier = Supplier::factory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->patchJson(route('api.consumables.update', $consumable), ['default_supplier_id' => $newSupplier->id])
        ->assertOk();

    expect((int) $consumable->fresh()->default_supplier_id)->toBe($newSupplier->id);
});
