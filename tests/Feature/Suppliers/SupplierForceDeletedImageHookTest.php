<?php

use App\Models\Supplier;
use Illuminate\Support\Facades\Storage;

test('soft delete preserves image file', function () {
    Storage::fake('public');
    $supplier = Supplier::factory()->create(['image' => 'vendor-logo.png']);
    Storage::disk('public')->put('suppliers/vendor-logo.png', 'fake image bytes');

    $supplier->delete();

    Storage::disk('public')->assertExists('suppliers/vendor-logo.png');
});

test('force delete wipes image file', function () {
    Storage::fake('public');
    $supplier = Supplier::factory()->create(['image' => 'vendor-logo.png']);
    Storage::disk('public')->put('suppliers/vendor-logo.png', 'fake image bytes');

    $supplier->forceDelete();

    Storage::disk('public')->assertMissing('suppliers/vendor-logo.png');
});
