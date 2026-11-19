<?php

use App\Models\Manufacturer;
use Illuminate\Support\Facades\Storage;

test('soft delete preserves image file', function () {
    Storage::fake('public');
    $manufacturer = Manufacturer::factory()->create(['image' => 'maker-logo.png']);
    Storage::disk('public')->put('manufacturers/maker-logo.png', 'fake image bytes');

    $manufacturer->delete();

    Storage::disk('public')->assertExists('manufacturers/maker-logo.png');
});

test('force delete wipes image file', function () {
    Storage::fake('public');
    $manufacturer = Manufacturer::factory()->create(['image' => 'maker-logo.png']);
    Storage::disk('public')->put('manufacturers/maker-logo.png', 'fake image bytes');

    $manufacturer->forceDelete();

    Storage::disk('public')->assertMissing('manufacturers/maker-logo.png');
});
