<?php

use App\Models\Group;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->superuser()->create());
});

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('updates group by id', function () {
    $group = Group::factory()->create();
    $newNotes = 'Updated notes '.uniqid();

    handle(['id' => $group->id, 'notes' => $newNotes]);

    $this->assertDatabaseHas('permission_groups', ['id' => $group->id, 'notes' => $newNotes]);
});

test('renames via new name', function () {
    $group = Group::factory()->create();
    $newName = 'Renamed Group '.uniqid();

    handle(['id' => $group->id, 'new_name' => $newName]);

    $this->assertDatabaseHas('permission_groups', ['id' => $group->id, 'name' => $newName]);
});

test('returns error when not found', function () {
    expect(handle(['id' => 999999, 'notes' => 'test'])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $group = Group::factory()->create();

    expect(handle(['id' => $group->id, 'notes' => 'test'])->responses()->first()->isError())->toBeTrue();
});

test('updates permissions', function () {
    $group = Group::factory()->create(['permissions' => json_encode(['assets.view' => 1])]);

    $content = handle([
        'id' => $group->id,
        'permissions' => json_encode(['assets.create' => 1, 'assets.edit' => -1]),
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $decoded = json_decode($group->fresh()->permissions, true);
    expect($decoded)->toHaveKey('assets.create');
    $this->assertArrayNotHasKey('assets.view', $decoded);
});

test('returns error for invalid permission key on update', function () {
    $group = Group::factory()->create();

    $response = handle([
        'id' => $group->id,
        'permissions' => json_encode(['completely.fake.key' => 1]),
    ]);

    expect($response->responses()->first()->isError())->toBeTrue();
});

test('permissions unchanged when not provided', function () {
    $original = json_encode(['assets.view' => 1]);
    $group = Group::factory()->create(['permissions' => $original]);

    handle(['id' => $group->id, 'notes' => 'updated notes']);

    expect($group->fresh()->permissions)->toEqual($original);
});
