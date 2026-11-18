<?php

use App\Models\Category;
use App\Models\License;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->editLicenses()->create());
});

function handle(array $args): ResponseFactory
{
    return handle(new Request($args));
}

test('updates license by id', function () {
    $license = License::factory()->create(['name' => 'Original Name']);

    $content = handle([
        'id' => $license->id,
        'new_name' => 'Updated Name',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('licenses', ['id' => $license->id, 'name' => 'Updated Name']);
});

test('updates license by name', function () {
    $license = License::factory()->create(['name' => 'Find By Name License']);

    $content = handle([
        'name' => 'Find By Name License',
        'new_name' => 'Renamed License',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('licenses', ['id' => $license->id, 'name' => 'Renamed License']);
});

test('response includes id name and seats', function () {
    $license = License::factory()->create(['seats' => 5]);

    $content = handle([
        'id' => $license->id,
        'new_name' => 'Response Check License',
    ])->getStructuredContent();

    expect($content)->toHaveKey('id');
    expect($content['name'])->toEqual('Response Check License');
    expect($content['seats'])->toEqual(5);
});

test('updates multiple fields', function () {
    $license = License::factory()->create();
    $category = Category::factory()->create(['category_type' => 'license']);

    handle([
        'id' => $license->id,
        'category_id' => $category->id,
        'serial' => 'SN-NEW-001',
        'purchase_cost' => 199.99,
        'expiration_date' => '2026-12-31',
        'maintained' => true,
        'reassignable' => true,
        'notes' => 'Updated notes',
    ]);

    $this->assertDatabaseHas('licenses', [
        'id' => $license->id,
        'category_id' => $category->id,
        'serial' => 'SN-NEW-001',
    ]);
});

test('returns error when not found', function () {
    expect(handle(['id' => 999999, 'new_name' => 'Ghost'])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $license = License::factory()->create(['name' => 'Unchanged License']);
    $this->actingAs(User::factory()->create());

    expect(handle([
        'id' => $license->id,
        'new_name' => 'Should Not Change',
    ])->responses()->first()->isError())->toBeTrue();

    $this->assertDatabaseHas('licenses', ['id' => $license->id, 'name' => 'Unchanged License']);
});
