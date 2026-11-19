<?php

use App\Models\Department;
use Illuminate\Support\Facades\Storage;

test('soft delete preserves image file', function () {
    Storage::fake('public');
    $department = Department::factory()->create(['image' => 'dept-badge.png']);
    Storage::disk('public')->put('departments/dept-badge.png', 'fake image bytes');

    $department->delete();

    Storage::disk('public')->assertExists('departments/dept-badge.png');
});

test('force delete wipes image file', function () {
    Storage::fake('public');
    $department = Department::factory()->create(['image' => 'dept-badge.png']);
    Storage::disk('public')->put('departments/dept-badge.png', 'fake image bytes');

    $department->forceDelete();

    Storage::disk('public')->assertMissing('departments/dept-badge.png');
});
