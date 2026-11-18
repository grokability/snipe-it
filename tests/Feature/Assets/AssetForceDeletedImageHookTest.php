<?php

use App\Models\Asset;
use Illuminate\Support\Facades\Storage;

test('soft delete preserves image file', function () {
    Storage::fake('public');
    $asset = Asset::factory()->create(['image' => 'asset-hero.png']);
    Storage::disk('public')->put('assets/asset-hero.png', 'fake image bytes');

    $asset->delete();

    Storage::disk('public')->assertExists('assets/asset-hero.png');
});

test('force delete wipes image file', function () {
    Storage::fake('public');
    $asset = Asset::factory()->create(['image' => 'asset-hero.png']);
    Storage::disk('public')->put('assets/asset-hero.png', 'fake image bytes');

    $asset->forceDelete();

    Storage::disk('public')->assertMissing('assets/asset-hero.png');
});
