<?php

use App\Mcp\Tools\CreateGroupTool;
use App\Models\Group;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->superuser()->create());
});

function createGroupTool(array $args = []): ResponseFactory
{
    return (new CreateGroupTool)->handle(new Request($args));
}

test('creates group', function () {
    $name = 'Test MCP Group '.uniqid();

    createGroupTool(['name' => $name]);

    $this->assertDatabaseHas('permission_groups', ['name' => $name]);
});

test('response includes id and name', function () {
    $name = 'Test MCP Group '.uniqid();

    $content = createGroupTool(['name' => $name])->getStructuredContent();

    expect($content)->toHaveKey('id');
    expect($content)->toHaveKey('name');
    expect($content['name'])->toEqual($name);
    expect($content['success'])->toBeTrue();
});

test('returns error when name missing', function () {
    expect(createGroupTool()->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    $name = 'Unauthorized Group '.uniqid();

    createGroupTool(['name' => $name]);

    $this->assertDatabaseMissing('permission_groups', ['name' => $name]);
});

test('creates group with valid permissions', function () {
    $name = 'Permissions Group '.uniqid();

    $content = createGroupTool([
        'name' => $name,
        'permissions' => json_encode(['assets.view' => 1, 'assets.create' => -1]),
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($content)->toHaveKey('permissions');
    $group = Group::where('name', $name)->first();
    expect($group)->not->toBeNull();
    $decoded = json_decode($group->permissions, true);
    expect($decoded['assets.view'])->toEqual(1);
    expect($decoded['assets.create'])->toEqual(-1);
});

test('returns error for invalid permission key', function () {
    $response = createGroupTool([
        'name' => 'Bad Perms Group '.uniqid(),
        'permissions' => json_encode(['not.a.real.key' => 1]),
    ]);

    expect($response->responses()->first()->isError())->toBeTrue();
});

test('returns error for invalid permission value', function () {
    $response = createGroupTool([
        'name' => 'Bad Value Group '.uniqid(),
        'permissions' => json_encode(['assets.view' => 0]),
    ]);

    expect($response->responses()->first()->isError())->toBeTrue();
});

test('returns error for malformed permissions json', function () {
    $response = createGroupTool([
        'name' => 'Malformed Group '.uniqid(),
        'permissions' => 'not valid json',
    ]);

    expect($response->responses()->first()->isError())->toBeTrue();
});
