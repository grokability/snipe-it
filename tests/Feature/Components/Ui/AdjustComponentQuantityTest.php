<?php

use App\Enums\ActionType;
use App\Models\Actionlog;
use App\Models\Component;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('requires edit permission', function () {
    $component = Component::factory()->create(['qty' => 5]);

    $this->actingAs(User::factory()->create())
        ->post(route('components.adjust-quantity', $component), [
            'amount' => 3,
            'note' => 'restock',
        ])
        ->assertForbidden();

    expect((int) $component->fresh()->qty)->toBe(5);
});

test('replenish increments qty and logs', function () {
    $actor = User::factory()->editComponents()->create();
    $component = Component::factory()->create(['qty' => 12]);

    $this->actingAs($actor)
        ->post(route('components.adjust-quantity', $component), [
            'amount' => 8,
            'note' => 'new shipment',
            'order_number' => 'PO-88',
        ])
        ->assertRedirect();

    expect((int) $component->fresh()->qty)->toBe(20);

    $log = Actionlog::where('item_type', Component::class)
        ->where('item_id', $component->id)
        ->where('action_type', ActionType::QuantityAdjust->value)
        ->latest('id')
        ->first();

    expect((int) $log->quantity)->toBe(8);
    expect($log->orderItem->order->order_number)->toBe('PO-88');
});

test('decrement below zero is rejected', function () {
    $actor = User::factory()->editComponents()->create();
    $component = Component::factory()->create(['qty' => 4]);

    $this->actingAs($actor)
        ->from(route('components.show', $component))
        ->post(route('components.adjust-quantity', $component), [
            'amount' => -100,
            'note' => 'oops',
        ])
        ->assertSessionHas('error');

    expect((int) $component->fresh()->qty)->toBe(4);
});

test('uploaded receipt attaches to the same log row and surfaces in files tab', function () {
    Storage::fake();

    $actor = User::factory()->editComponents()->create();
    $component = Component::factory()->create(['qty' => 5]);
    $uploadsBefore = $component->uploads()->count();

    $this->actingAs($actor)
        ->post(route('components.adjust-quantity', $component), [
            'amount' => 3,
            'note' => 'restock with invoice',
            'order_number' => 'PO-99',
            'file' => UploadedFile::fake()->create('invoice-99.pdf', 32, 'application/pdf'),
        ])
        ->assertRedirect();

    $log = Actionlog::where('item_type', Component::class)
        ->where('item_id', $component->id)
        ->where('action_type', ActionType::QuantityAdjust->value)
        ->latest('id')
        ->firstOrFail();

    expect($log->filename)->not->toBeNull('Uploaded receipt filename must be attached to the QuantityAdjust log row.');
    expect($log->filename)->toEndWith('.pdf');
    expect(Actionlog::where('item_type', Component::class)
        ->where('item_id', $component->id)
        ->where('action_type', 'uploaded')
        ->count())->toBe(0, 'No separate uploaded log entry should have been created.');

    expect($component->uploads()->count())->toBe($uploadsBefore + 1);
    expect($component->uploads()->where('id', $log->id)->exists())->toBeTrue();
});
