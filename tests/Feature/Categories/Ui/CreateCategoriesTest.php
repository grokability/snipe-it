<?php

use App\Models\Category;
use App\Models\User;

test('permission required to create categories', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('categories.store'), [
            'name' => 'Test Category',
            'category_type' => 'asset',
        ])
        ->assertForbidden();
});

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('categories.create'))
        ->assertOk();
});

test('user can create categories', function () {
    expect(Category::where('name', 'Test Category')->exists())->toBeFalse();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('categories.store'), [
            'name' => 'Test Category',
            'category_type' => 'asset',
            'eula_text' => 'Sample text',
            'require_acceptance' => '1',
            'notes' => 'My Note',
        ])
        ->assertRedirect(route('categories.index'));

    $this->assertDatabaseHas('categories', [
        'name' => 'Test Category',
        'category_type' => 'asset',
        'eula_text' => 'Sample text',
        'notes' => 'My Note',
        'require_acceptance' => 1,
        'alert_on_response' => 0,
    ]);
});

test('user cannot create categories with invalid type', function () {
    expect(Category::where('name', 'Test Category')->exists())->toBeFalse();

    $this->actingAs(User::factory()->superuser()->create())
        ->from(route('categories.create'))
        ->post(route('categories.store'), [
            'name' => 'Test Category',
            'category_type' => 'invalid',
        ])
        ->assertRedirect(route('categories.create'));

    expect(Category::where('name', 'Test Category')->exists())->toBeFalse();
});
