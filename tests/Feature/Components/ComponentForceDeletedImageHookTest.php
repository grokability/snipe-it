<?php

use App\Models\Component;
use Illuminate\Support\Facades\Storage;

test('soft delete preserves image file', function () {
    Storage::fake('public');
    $component = Component::factory()->create(['image' => 'ram-stick.png']);
    Storage::disk('public')->put('components/ram-stick.png', 'fake image bytes');

    $component->delete();

    Storage::disk('public')->assertExists('components/ram-stick.png');
});

test('force delete wipes image file', function () {
    Storage::fake('public');
    $component = Component::factory()->create(['image' => 'ram-stick.png']);
    Storage::disk('public')->put('components/ram-stick.png', 'fake image bytes');

    $component->forceDelete();

    Storage::disk('public')->assertMissing('components/ram-stick.png');
});
