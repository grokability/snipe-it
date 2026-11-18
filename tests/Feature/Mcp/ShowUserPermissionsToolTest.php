<?php

use App\Models\Group;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('returns self permissions when no identifier provided', function () {
    $user = User::factory()->viewAssets()->create();
    $this->actingAs($user);

    $content = handle()->getStructuredContent();

    expect($content['id'])->toEqual($user->id);
    expect($content['username'])->toEqual($user->username);
    expect($content['allowed_permissions'])->toContain('assets.view');
    expect($content['is_superuser'])->toBeFalse();
});

test('returns target user permissions by id when authorized', function () {
    $viewer = User::factory()->viewUsers()->create();
    $target = User::factory()->viewAssets()->editAssets()->create();
    $this->actingAs($viewer);

    $content = handle(['id' => $target->id])->getStructuredContent();

    expect($content['id'])->toEqual($target->id);
    expect($content['allowed_permissions'])->toContain('assets.view');
    expect($content['allowed_permissions'])->toContain('assets.edit');
});

test('returns target user permissions by username', function () {
    $viewer = User::factory()->viewUsers()->create();
    $target = User::factory()->viewAssets()->create(['username' => 'jdoe']);
    $this->actingAs($viewer);

    $content = handle(['username' => 'jdoe'])->getStructuredContent();

    expect($content['id'])->toEqual($target->id);
});

test('detects superuser', function () {
    $user = User::factory()->superuser()->create();
    $this->actingAs($user);

    $content = handle()->getStructuredContent();

    expect($content['is_superuser'])->toBeTrue();
});

test('includes group memberships', function () {
    $group = Group::factory()->create(['name' => 'Help Desk']);
    $user = User::factory()->create();
    $user->groups()->attach($group->id);
    $this->actingAs($user);

    $content = handle()->getStructuredContent();

    expect($content['groups'])->toHaveCount(1);
    expect($content['groups'][0]['name'])->toEqual('Help Desk');
});

test('reports group sourced permissions', function () {
    $group = Group::factory()->create([
        'name' => 'Asset Viewers',
        'permissions' => json_encode(['assets.view' => '1']),
    ]);
    $user = User::factory()->create();
    $user->groups()->attach($group->id);
    $this->actingAs($user);

    $content = handle()->getStructuredContent();

    expect($content['allowed_permissions'])->toContain('assets.view');
});

test('returns error when user not found', function () {
    $this->actingAs(User::factory()->viewUsers()->create());

    expect(handle(['id' => 999999])->responses()->first()->isError())->toBeTrue();
});

test('returns error when viewer lacks permission', function () {
    $target = User::factory()->create();
    $this->actingAs(User::factory()->create());

    expect(handle(['id' => $target->id])->responses()->first()->isError())->toBeTrue();
});

test('self lookup bypasses view permission gate', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $content = handle(['id' => $user->id])->getStructuredContent();

    expect($content['id'])->toEqual($user->id);
});
