<?php

use App\Mcp\Tools\UpdateComponentTool;
use App\Models\Category;
use App\Models\Component;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->editComponents()->create());
});

function updateComponentTool(array $args): ResponseFactory
{
    return (new UpdateComponentTool)->handle(new Request($args));
}

test('updates component by id', function () {
    $component = Component::factory()->create(['qty' => 5]);

    $content = updateComponentTool([
        'id' => $component->id,
        'qty' => 15,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('components', ['id' => $component->id, 'qty' => 15]);
});

test('updates component by name', function () {
    $component = Component::factory()->create(['name' => 'Lookup By Name', 'qty' => 3]);

    $content = updateComponentTool([
        'name' => 'Lookup By Name',
        'qty' => 9,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('components', ['id' => $component->id, 'qty' => 9]);
});

test('renames component via new name', function () {
    $component = Component::factory()->create(['name' => 'Old Component Name']);

    $content = updateComponentTool([
        'id' => $component->id,
        'new_name' => 'New Component Name',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($content['name'])->toEqual('New Component Name');
    $this->assertDatabaseHas('components', ['id' => $component->id, 'name' => 'New Component Name']);
});

test('updates multiple fields at once', function () {
    $component = Component::factory()->create();
    $location = Location::factory()->create();
    $category = Category::factory()->forComponents()->create();

    // order_number moved to the Orders table. Covering the
    // forwarded legacy purchase_cost input path instead.
    updateComponentTool([
        'id' => $component->id,
        'qty' => 25,
        'location_id' => $location->id,
        'category_id' => $category->id,
        'purchase_cost' => 123.45,
        'notes' => 'Updated component note',
    ]);

    $this->assertDatabaseHas('components', [
        'id' => $component->id,
        'qty' => 25,
        'location_id' => $location->id,
        'category_id' => $category->id,
        'default_purchase_cost' => 123.45,
    ]);
});

test('returns error when not found', function () {
    expect(updateComponentTool([
        'id' => 999999,
        'qty' => 5,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when no identifier provided', function () {
    expect(updateComponentTool([
        'qty' => 5,
    ])->responses()->first()->isError())->toBeTrue();
});

test('response includes id and name', function () {
    $component = Component::factory()->create(['name' => 'Response Component']);

    $content = updateComponentTool([
        'id' => $component->id,
        'qty' => 8,
    ])->getStructuredContent();

    expect($content['id'])->toEqual($component->id);
    expect($content['name'])->toEqual('Response Component');
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $component = Component::factory()->create();

    expect(updateComponentTool([
        'id' => $component->id,
        'qty' => 5,
    ])->responses()->first()->isError())->toBeTrue();
});
