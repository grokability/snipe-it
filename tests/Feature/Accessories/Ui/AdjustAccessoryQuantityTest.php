<?php

use App\Enums\ActionType;
use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Actionlog;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('requires edit permission', function () {
    $accessory = Accessory::factory()->create(['qty' => 5]);

    $this->actingAs(User::factory()->create())
        ->post(route('accessories.adjust-quantity', $accessory), [
            'amount' => 3,
            'note' => 'restock',
        ])
        ->assertForbidden();

    expect((int) $accessory->fresh()->qty)->toBe(5);
});

test('replenish increments qty and logs', function () {
    $actor = User::factory()->editAccessories()->create();
    $accessory = Accessory::factory()->create(['qty' => 5]);

    $this->actingAs($actor)
        ->post(route('accessories.adjust-quantity', $accessory), [
            'amount' => 3,
            'note' => 'restock from PO',
            'order_number' => 'PO-42',
        ])
        ->assertRedirect();

    expect((int) $accessory->fresh()->qty)->toBe(8);

    $log = Actionlog::where('item_type', Accessory::class)
        ->where('item_id', $accessory->id)
        ->where('action_type', ActionType::QuantityAdjust->value)
        ->latest('id')
        ->first();

    expect($log)->not->toBeNull();
    expect((int) $log->quantity)->toBe(3);
    expect($log->note)->toBe('restock from PO');
    expect($log->orderItem->order->order_number)->toBe('PO-42');
    expect((int) $log->created_by)->toBe($actor->id);
});

test('decrement reduces qty and logs negative quantity', function () {
    $actor = User::factory()->editAccessories()->create();
    $accessory = Accessory::factory()->create(['qty' => 10]);

    $this->actingAs($actor)
        ->post(route('accessories.adjust-quantity', $accessory), [
            'amount' => -4,
            'note' => 'damaged units removed',
        ])
        ->assertRedirect();

    expect((int) $accessory->fresh()->qty)->toBe(6);

    $log = Actionlog::where('item_type', Accessory::class)
        ->where('item_id', $accessory->id)
        ->where('action_type', ActionType::QuantityAdjust->value)
        ->latest('id')
        ->first();

    expect((int) $log->quantity)->toBe(-4);
    expect($log->order_item_id)->toBeNull();
});

test('decrement below zero is rejected', function () {
    $actor = User::factory()->editAccessories()->create();
    $accessory = Accessory::factory()->create(['qty' => 2]);

    $this->actingAs($actor)
        ->from(route('accessories.show', $accessory))
        ->post(route('accessories.adjust-quantity', $accessory), [
            'amount' => -5,
            'note' => 'oops',
        ])
        ->assertRedirect(route('accessories.show', $accessory))
        ->assertSessionHas('error');

    expect((int) $accessory->fresh()->qty)->toBe(2);
    $this->assertDatabaseMissing('action_logs', [
        'item_type' => Accessory::class,
        'item_id' => $accessory->id,
        'action_type' => ActionType::QuantityAdjust->value,
    ]);
});

test('decrement below currently checked out is rejected', function () {
    $actor = User::factory()->editAccessories()->create();
    $accessory = Accessory::factory()->create(['qty' => 10]);
    AccessoryCheckout::factory()->count(4)->create([
        'accessory_id' => $accessory->id,
        'assigned_type' => User::class,
        'assigned_to' => User::factory()->create()->id,
    ]);

    $this->actingAs($actor)
        ->from(route('accessories.show', $accessory))
        ->post(route('accessories.adjust-quantity', $accessory), [
            'amount' => -7, // would leave qty=3, below the 4 in use
            'note' => 'oops',
        ])
        ->assertSessionHas('error');

    expect((int) $accessory->fresh()->qty)->toBe(10);
});

test('note is required', function () {
    $actor = User::factory()->editAccessories()->create();
    $accessory = Accessory::factory()->create(['qty' => 5]);

    $this->actingAs($actor)
        ->post(route('accessories.adjust-quantity', $accessory), [
            'amount' => 2,
        ])
        ->assertSessionHasErrors('note');

    expect((int) $accessory->fresh()->qty)->toBe(5);
});

test('zero amount writes audit log without changing qty', function () {
    // Zero delta is an audit-only submission: the user counted the
    // shelf, it matches the DB, and they recorded that fact. We
    // write a QuantityAdjust log entry (with quantity=0) for
    // provenance but do not touch the on-hand column.
    $actor = User::factory()->editAccessories()->create();
    $accessory = Accessory::factory()->create(['qty' => 5]);

    $this->actingAs($actor)
        ->post(route('accessories.adjust-quantity', $accessory), [
            'amount' => 0,
            'note' => 'shelf count matches',
        ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('success');

    expect((int) $accessory->fresh()->qty)->toBe(5);

    $log = \App\Models\Actionlog::where('item_type', Accessory::class)
        ->where('item_id', $accessory->id)
        ->where('action_type', \App\Enums\ActionType::QuantityAdjust->value)
        ->latest('id')
        ->firstOrFail();

    expect((int) $log->quantity)->toBe(0);
    expect($log->note)->toBe('shelf count matches');
});

test('uploaded receipt attaches to the same log row and surfaces in files tab', function () {
    Storage::fake();

    $actor = User::factory()->editAccessories()->create();
    $accessory = Accessory::factory()->create(['qty' => 5]);
    $uploadsBefore = $accessory->uploads()->count();

    $this->actingAs($actor)
        ->post(route('accessories.adjust-quantity', $accessory), [
            'amount' => 3,
            'note' => 'restock with invoice',
            'order_number' => 'PO-99',
            'file' => UploadedFile::fake()->create('invoice-99.pdf', 32, 'application/pdf'),
        ])
        ->assertRedirect();

    $log = Actionlog::where('item_type', Accessory::class)
        ->where('item_id', $accessory->id)
        ->where('action_type', ActionType::QuantityAdjust->value)
        ->latest('id')
        ->firstOrFail();

    // Attachment lives on the QuantityAdjust log, not a separate
    // 'uploaded' entry (so the history table shows one consolidated line).
    expect($log->filename)->not->toBeNull('Uploaded receipt filename must be attached to the QuantityAdjust log row.');
    expect($log->filename)->toEndWith('.pdf');
    expect(Actionlog::where('item_type', Accessory::class)
        ->where('item_id', $accessory->id)
        ->where('action_type', 'uploaded')
        ->count())->toBe(0, 'No separate uploaded log entry should have been created.');

    // And the same log row surfaces in uploads() so the Files tab shows it.
    expect($accessory->uploads()->count())->toBe($uploadsBefore + 1);
    expect($accessory->uploads()->where('id', $log->id)->exists())->toBeTrue();
});
