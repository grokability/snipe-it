<?php

use App\Enums\ActionType;
use App\Models\Actionlog;
use App\Models\Consumable;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('requires edit permission', function () {
    $consumable = Consumable::factory()->create(['qty' => 5]);

    $this->actingAs(User::factory()->create())
        ->post(route('consumables.adjust-quantity', $consumable), [
            'amount' => 3,
            'note' => 'restock',
        ])
        ->assertForbidden();

    expect((int) $consumable->fresh()->qty)->toBe(5);
});

test('replenish increments qty and logs', function () {
    $actor = User::factory()->editConsumables()->create();
    $consumable = Consumable::factory()->create(['qty' => 20]);

    $this->actingAs($actor)
        ->post(route('consumables.adjust-quantity', $consumable), [
            'amount' => 15,
            'note' => 'PO arrived',
            'order_number' => 'PO-77',
        ])
        ->assertRedirect();

    expect((int) $consumable->fresh()->qty)->toBe(35);

    $log = Actionlog::where('item_type', Consumable::class)
        ->where('item_id', $consumable->id)
        ->where('action_type', ActionType::QuantityAdjust->value)
        ->latest('id')
        ->first();

    expect((int) $log->quantity)->toBe(15);
    expect($log->note)->toBe('PO arrived');
    expect($log->orderItem->order->order_number)->toBe('PO-77');
});

test('decrement below zero is rejected', function () {
    $actor = User::factory()->editConsumables()->create();
    $consumable = Consumable::factory()->create(['qty' => 3]);

    $this->actingAs($actor)
        ->from(route('consumables.show', $consumable))
        ->post(route('consumables.adjust-quantity', $consumable), [
            'amount' => -10,
            'note' => 'oops',
        ])
        ->assertSessionHas('error');

    expect((int) $consumable->fresh()->qty)->toBe(3);
});

test('note is required', function () {
    $actor = User::factory()->editConsumables()->create();
    $consumable = Consumable::factory()->create(['qty' => 5]);

    $this->actingAs($actor)
        ->post(route('consumables.adjust-quantity', $consumable), [
            'amount' => 2,
        ])
        ->assertSessionHasErrors('note');
});

test('uploaded receipt attaches to the same log row and surfaces in files tab', function () {
    Storage::fake();

    $actor = User::factory()->editConsumables()->create();
    $consumable = Consumable::factory()->create(['qty' => 5]);
    $uploadsBefore = $consumable->uploads()->count();

    $this->actingAs($actor)
        ->post(route('consumables.adjust-quantity', $consumable), [
            'amount' => 3,
            'note' => 'restock with invoice',
            'order_number' => 'PO-99',
            'file' => UploadedFile::fake()->create('invoice-99.pdf', 32, 'application/pdf'),
        ])
        ->assertRedirect();

    $log = Actionlog::where('item_type', Consumable::class)
        ->where('item_id', $consumable->id)
        ->where('action_type', ActionType::QuantityAdjust->value)
        ->latest('id')
        ->firstOrFail();

    expect($log->filename)->not->toBeNull('Uploaded receipt filename must be attached to the QuantityAdjust log row.');
    expect($log->filename)->toEndWith('.pdf');
    expect(Actionlog::where('item_type', Consumable::class)
        ->where('item_id', $consumable->id)
        ->where('action_type', 'uploaded')
        ->count())->toBe(0, 'No separate uploaded log entry should have been created.');

    expect($consumable->uploads()->count())->toBe($uploadsBefore + 1);
    expect($consumable->uploads()->where('id', $log->id)->exists())->toBeTrue();
});
