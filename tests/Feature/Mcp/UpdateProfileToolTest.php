<?php

use App\Mcp\Tools\UpdateProfileTool;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

function updateProfileTool(array $args): ResponseFactory
{
    return (new UpdateProfileTool)->handle(new Request($args));
}

test('updates profile fields', function () {
    $content = updateProfileTool([
        'first_name' => 'Updated',
        'last_name' => 'Name',
        'phone' => '555-1234',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('users', [
        'id' => $this->user->id,
        'first_name' => 'Updated',
        'last_name' => 'Name',
        'phone' => '555-1234',
    ]);
});

test('updates locale', function () {
    $content = updateProfileTool(['locale' => 'fr'])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('users', ['id' => $this->user->id, 'locale' => 'fr']);
});

test('updates location with permission', function () {
    $this->actingAs(User::factory()->canEditOwnLocation()->create());
    $location = Location::factory()->create();

    $content = updateProfileTool(['location_id' => $location->id])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($content['location_id'])->toEqual($location->id);
});

test('does not update location without permission', function () {
    $location = Location::factory()->create();

    $originalLocation = $this->user->location_id;

    updateProfileTool(['location_id' => $location->id]);

    $this->assertDatabaseHas('users', ['id' => $this->user->id, 'location_id' => $originalLocation]);
});

test('only updates provided fields', function () {
    $originalLastName = $this->user->last_name;

    updateProfileTool(['first_name' => 'Changed']);

    $this->assertDatabaseHas('users', [
        'id' => $this->user->id,
        'first_name' => 'Changed',
        'last_name' => $originalLastName,
    ]);
});
