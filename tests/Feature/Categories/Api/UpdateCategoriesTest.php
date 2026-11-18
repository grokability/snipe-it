<?php

use App\Models\Category;
use App\Models\User;

test('requires permission to update category', function () {
    $category = Category::factory()->create();

    $this->actingAsForApi(User::factory()->create())
        ->patchJson(route('api.categories.update', $category))
        ->assertForbidden();
});

test('can update category', function () {
    $category = Category::factory()->forAssets()->create([
        'name' => 'Test Category',
        'require_acceptance' => false,
        'alert_on_response' => false,
    ]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->patchJson(route('api.categories.update', $category), [
            'name' => 'Test Category Edited',
            'notes' => 'Test Note Edited',
            'require_acceptance' => true,
            'alert_on_response' => true,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success')
        ->assertStatus(200);

    $category->refresh();
    expect($category->name)->toEqual('Test Category Edited', 'Name was not updated');
    expect($category->notes)->toEqual('Test Note Edited', 'Note was not updated');
    expect($category->require_acceptance)->toEqual(1, 'Require acceptance was not updated');
    expect($category->alert_on_response)->toBeTrue('Alert on response was not updated');
});

test('can update category via patch without category type', function () {
    $category = Category::factory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->patchJson(route('api.categories.update', $category), [
            'name' => 'Test Category',
            'eula_text' => 'Test EULA',
            'notes' => 'Test Note',
        ])
        ->assertOk()
        ->assertStatusMessageIs('success')
        ->assertStatus(200)
        ->json();

    // dd($response);
    $category->refresh();
    expect($category->name)->toEqual('Test Category', 'Name was not updated');
    expect($category->eula_text)->toEqual('Test EULA', 'EULA was not updated');
    expect($category->notes)->toEqual('Test Note', 'Note was not updated');
});

test('cannot update category via patch with category type', function () {
    $category = Category::factory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->patchJson(route('api.categories.update', $category), [
            'name' => 'Test Category',
            'eula_text' => 'Test EULA',
            'category_type' => 'accessory',
            'note' => 'Test Note',
        ])
        ->assertOk()
        ->assertStatusMessageIs('error')
        ->assertStatus(200)
        ->json();

    $category->refresh();
    $this->assertNotEquals('Test Category', $category->name, 'Name was not updated');
    $this->assertNotEquals('Test EULA', $category->eula_text, 'EULA was not updated');
    $this->assertNotEquals('Test Note', $category->notes, 'Note was not updated');
    $this->assertNotEquals('accessory', $category->category_type, 'EULA was not updated');
});
