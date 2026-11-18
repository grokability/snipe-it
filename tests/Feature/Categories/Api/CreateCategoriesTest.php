<?php

use App\Models\Category;
use App\Models\User;

test('requires permission to create category', function () {
    $this->actingAsForApi(User::factory()->create())
        ->postJson(route('api.categories.store'))
        ->assertForbidden();
});

test('can create category with valid category type', function () {
    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.categories.store'), [
            'name' => 'Test Category',
            'eula_text' => 'Test EULA',
            'category_type' => 'accessory',
            'notes' => 'Test Note',
            'require_acceptance' => true,
            'alert_on_response' => true,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success')
        ->assertStatus(200)
        ->json();

    expect(Category::where('name', 'Test Category')->exists())->toBeTrue();

    $category = Category::find($response['payload']['id']);
    expect($category->name)->toEqual('Test Category');
    expect($category->eula_text)->toEqual('Test EULA');
    expect($category->notes)->toEqual('Test Note');
    expect($category->category_type)->toEqual('accessory');
    expect($category->require_acceptance)->toEqual(1);
    expect($category->alert_on_response)->toEqual(1);
});

test('cannot create category without category type', function () {
    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.categories.store'), [
            'name' => 'Test Category',
        ])
        ->assertOk()
        ->assertStatus(200)
        ->assertStatusMessageIs('error')
        ->assertJson([
            'messages' => [
                'category_type' => ['The category type field is required.'],
            ],
        ]);
    expect(Category::where('name', 'Test Category')->exists())->toBeFalse();
});

test('cannot create category with invalid category type', function () {
    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.categories.store'), [
            'name' => 'Test Category',
            'eula_text' => 'Test EULA',
            'category_type' => 'invalid',
        ])
        ->assertOk()
        ->assertStatus(200)
        ->assertStatusMessageIs('error')
        ->assertJson([
            'messages' => [
                'category_type' => ['The selected category type is invalid.'],
            ],
        ]);

    expect(Category::where('name', 'Test Category')->exists())->toBeFalse();
});
