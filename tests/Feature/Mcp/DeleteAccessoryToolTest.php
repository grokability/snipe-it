<?php

use App\Mcp\Tools\DeleteAccessoryTool;
use App\Models\Accessory;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->deleteAccessories()->create());
});

function deleteAccessoryTool(array $args): ResponseFactory
{
    return (new DeleteAccessoryTool)->handle(new Request($args));
}

test('deletes accessory by id', function () {
    $accessory = Accessory::factory()->create();

    $content = deleteAccessoryTool(['id' => $accessory->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('accessories', ['id' => $accessory->id]);
});

test('deletes accessory by name', function () {
    $accessory = Accessory::factory()->create(['name' => 'Delete By Name']);

    $content = deleteAccessoryTool(['name' => 'Delete By Name'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted('accessories', ['id' => $accessory->id]);
});

test('response includes name', function () {
    $accessory = Accessory::factory()->create(['name' => 'Named Accessory']);

    $content = deleteAccessoryTool(['id' => $accessory->id])->getStructuredContent();

    expect($content['name'])->toEqual('Named Accessory');
});

test('returns error when not found', function () {
    expect(deleteAccessoryTool(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when accessory has checkouts', function () {
    $user = User::factory()->create();
    $accessory = Accessory::factory()->checkedOutToUser($user)->create();

    $response = deleteAccessoryTool(['id' => $accessory->id]);

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('accessories', ['id' => $accessory->id]);
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $accessory = Accessory::factory()->create();

    expect(deleteAccessoryTool(['id' => $accessory->id])->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted('accessories', ['id' => $accessory->id]);
});
