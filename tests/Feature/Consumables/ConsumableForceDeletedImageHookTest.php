<?php

use App\Models\Consumable;
use Illuminate\Support\Facades\Storage;

test('soft delete preserves image file', function () {
    Storage::fake('public');
    $consumable = Consumable::factory()->create(['image' => 'ink-cart.png']);
    Storage::disk('public')->put('consumables/ink-cart.png', 'fake image bytes');

    $consumable->delete();

    Storage::disk('public')->assertExists('consumables/ink-cart.png');
});

test('force delete wipes image file', function () {
    Storage::fake('public');
    $consumable = Consumable::factory()->create(['image' => 'ink-cart.png']);
    Storage::disk('public')->put('consumables/ink-cart.png', 'fake image bytes');

    $consumable->forceDelete();

    Storage::disk('public')->assertMissing('consumables/ink-cart.png');
});
