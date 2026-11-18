<?php

use App\Models\Accessory;
use Illuminate\Support\Facades\Storage;

test('soft delete preserves image file', function () {
    Storage::fake('public');
    $accessory = Accessory::factory()->create(['image' => 'mouse-pad.png']);
    Storage::disk('public')->put('accessories/mouse-pad.png', 'fake image bytes');

    $accessory->delete();

    Storage::disk('public')->assertExists('accessories/mouse-pad.png');
});

test('force delete wipes image file', function () {
    Storage::fake('public');
    $accessory = Accessory::factory()->create(['image' => 'mouse-pad.png']);
    Storage::disk('public')->put('accessories/mouse-pad.png', 'fake image bytes');

    $accessory->forceDelete();

    Storage::disk('public')->assertMissing('accessories/mouse-pad.png');
});
