<?php

use App\Mcp\Tools\UpdateAccessoryTool;
use App\Models\Accessory;
use App\Models\Category;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->editAccessories()->create());
});

function updateAccessoryTool(array $args): ResponseFactory
{
    return (new UpdateAccessoryTool)->handle(new Request($args));
}

test('updates accessory by id', function () {
    $accessory = Accessory::factory()->create(['qty' => 5]);

    $content = updateAccessoryTool([
        'id' => $accessory->id,
        'qty' => 10,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('accessories', ['id' => $accessory->id, 'qty' => 10]);
});

test('updates accessory by name', function () {
    $accessory = Accessory::factory()->create(['name' => 'Lookup By Name', 'qty' => 3]);

    $content = updateAccessoryTool([
        'name' => 'Lookup By Name',
        'qty' => 8,
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('accessories', ['id' => $accessory->id, 'qty' => 8]);
});

test('renames accessory via new name', function () {
    $accessory = Accessory::factory()->create(['name' => 'Old Accessory Name']);

    $content = updateAccessoryTool([
        'id' => $accessory->id,
        'new_name' => 'New Accessory Name',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($content['name'])->toEqual('New Accessory Name');
    $this->assertDatabaseHas('accessories', ['id' => $accessory->id, 'name' => 'New Accessory Name']);
});

test('updates multiple fields at once', function () {
    $accessory = Accessory::factory()->create();
    $location = Location::factory()->create();
    $category = Category::factory()->forAccessories()->create();

    // order_number moved to the Orders table. Swapping it out for
    // default_purchase_cost here to cover the forwarded legacy
    // purchase_cost input path.
    updateAccessoryTool([
        'id' => $accessory->id,
        'qty' => 20,
        'location_id' => $location->id,
        'category_id' => $category->id,
        'purchase_cost' => 42.50,
        'notes' => 'Updated note',
    ]);

    $this->assertDatabaseHas('accessories', [
        'id' => $accessory->id,
        'qty' => 20,
        'location_id' => $location->id,
        'category_id' => $category->id,
        'default_purchase_cost' => 42.50,
    ]);
});

test('returns error when not found', function () {
    expect(updateAccessoryTool([
        'id' => 999999,
        'qty' => 5,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when no identifier provided', function () {
    expect(updateAccessoryTool([
        'qty' => 5,
    ])->responses()->first()->isError())->toBeTrue();
});

test('response includes id and name', function () {
    $accessory = Accessory::factory()->create(['name' => 'Response Accessory']);

    $content = updateAccessoryTool([
        'id' => $accessory->id,
        'qty' => 7,
    ])->getStructuredContent();

    expect($content['id'])->toEqual($accessory->id);
    expect($content['name'])->toEqual('Response Accessory');
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $accessory = Accessory::factory()->create(['name' => 'Locked Accessory']);

    expect(updateAccessoryTool([
        'id' => $accessory->id,
        'qty' => 5,
    ])->responses()->first()->isError())->toBeTrue();
});
