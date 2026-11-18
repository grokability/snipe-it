<?php

use App\Models\Category;
use Illuminate\Support\Facades\Storage;

test('soft delete preserves image file', function () {
    Storage::fake('public');
    $category = Category::factory()->create(['image' => 'cat-icon.png']);
    Storage::disk('public')->put('categories/cat-icon.png', 'fake image bytes');

    $category->delete();

    Storage::disk('public')->assertExists('categories/cat-icon.png');
});

test('force delete wipes image file', function () {
    Storage::fake('public');
    $category = Category::factory()->create(['image' => 'cat-icon.png']);
    Storage::disk('public')->put('categories/cat-icon.png', 'fake image bytes');

    $category->forceDelete();

    Storage::disk('public')->assertMissing('categories/cat-icon.png');
});
