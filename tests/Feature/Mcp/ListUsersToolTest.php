<?php

use App\Mcp\Tools\ListUsersTool;
use App\Models\Company;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->viewUsers()->create());
});

function listUsersTool(array $args = []): ResponseFactory
{
    return (new ListUsersTool)->handle(new Request($args));
}

test('returns list of users', function () {
    User::factory()->count(3)->create();

    $content = listUsersTool()->getStructuredContent();

    expect($content)->toHaveKey('users');
    expect($content)->toHaveKey('total');
    expect($content['total'])->toBeGreaterThanOrEqual(3);
});

test('user records contain expected fields', function () {
    User::factory()->create();

    $content = listUsersTool(['limit' => 1])->getStructuredContent();

    $user = $content['users'][0];
    expect($user)->toHaveKey('id');
    expect($user)->toHaveKey('username');
    expect($user)->toHaveKey('first_name');
    expect($user)->toHaveKey('last_name');
    expect($user)->toHaveKey('email');
    expect($user)->toHaveKey('activated');
    expect($user)->toHaveKey('assets_count');
});

test('filters by company id', function () {
    $company = Company::factory()->create();
    $user = $company->users()->save(User::factory()->make());

    $content = listUsersTool(['company_id' => $company->id])->getStructuredContent();

    $ids = array_column($content['users'], 'id');
    expect($ids)->toContain($user->id);
    foreach ($content['users'] as $u) {
        expect($u['company'])->toEqual($company->name);
    }
});

test('filters by department id', function () {
    $department = Department::factory()->create();
    $user = User::factory()->create(['department_id' => $department->id]);

    $content = listUsersTool(['department_id' => $department->id])->getStructuredContent();

    $ids = array_column($content['users'], 'id');
    expect($ids)->toContain($user->id);
});

test('filters by location id', function () {
    $location = Location::factory()->create();
    $user = User::factory()->create(['location_id' => $location->id]);

    $content = listUsersTool(['location_id' => $location->id])->getStructuredContent();

    $ids = array_column($content['users'], 'id');
    expect($ids)->toContain($user->id);
});

test('filters by activated status', function () {
    $active = User::factory()->create(['activated' => true]);
    $inactive = User::factory()->create(['activated' => false]);

    $content = listUsersTool(['activated' => false])->getStructuredContent();

    $ids = array_column($content['users'], 'id');
    expect($ids)->toContain($inactive->id);
    expect($ids)->not->toContain($active->id);
});

test('search filters by name', function () {
    $target = User::factory()->create(['first_name' => 'Uniquefirstname', 'last_name' => 'McTest']);

    $content = listUsersTool(['search' => 'Uniquefirstname'])->getStructuredContent();

    $ids = array_column($content['users'], 'id');
    expect($ids)->toContain($target->id);
});

test('pagination limit and offset', function () {
    User::factory()->count(10)->create();

    $page1 = listUsersTool(['limit' => 3, 'offset' => 0])->getStructuredContent();
    $page2 = listUsersTool(['limit' => 3, 'offset' => 3])->getStructuredContent();

    expect($page1['users'])->toHaveCount(3);
    expect($page2['users'])->toHaveCount(3);
    $this->assertNotEquals($page1['users'][0]['id'], $page2['users'][0]['id']);
});

test('response contains pagination meta', function () {
    $content = listUsersTool(['limit' => 5, 'offset' => 0])->getStructuredContent();

    expect($content['limit'])->toEqual(5);
    expect($content['offset'])->toEqual(0);
    expect($content)->toHaveKey('total');
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(listUsersTool()->responses()->first()->isError())->toBeTrue();
});

test('excludes soft deleted users by default', function () {
    User::factory()->create(['username' => 'active-one']);
    $deleted = User::factory()->create(['username' => 'deleted-one']);
    $deleted->delete();

    $content = listUsersTool(['limit' => 500])->getStructuredContent();
    $usernames = array_column($content['users'], 'username');

    expect($usernames)->toContain('active-one');
    expect($usernames)->not->toContain('deleted-one');
});

test('include deleted returns both active and soft deleted', function () {
    User::factory()->create(['username' => 'active-one']);
    $deleted = User::factory()->create(['username' => 'deleted-one']);
    $deleted->delete();

    $content = listUsersTool(['include_deleted' => true, 'limit' => 500])->getStructuredContent();
    $usernames = array_column($content['users'], 'username');

    expect($usernames)->toContain('active-one');
    expect($usernames)->toContain('deleted-one');
});

test('only deleted returns soft deleted only', function () {
    User::factory()->create(['username' => 'active-one']);
    $deleted = User::factory()->create(['username' => 'deleted-one']);
    $deleted->delete();

    $content = listUsersTool(['only_deleted' => true, 'limit' => 500])->getStructuredContent();
    $usernames = array_column($content['users'], 'username');

    expect($usernames)->toContain('deleted-one');
    expect($usernames)->not->toContain('active-one');
});

test('response includes deleted at and child counts', function () {
    User::factory()->create(['username' => 'marker-user']);

    $content = listUsersTool(['limit' => 500])->getStructuredContent();
    $row = collect($content['users'])->firstWhere('username', 'marker-user');

    expect($row)->not->toBeNull();
    expect($row)->toHaveKey('deleted_at');
    expect($row)->toHaveKey('last_login');
    expect($row)->toHaveKey('assets_count');
    expect($row)->toHaveKey('licenses_count');
    expect($row)->toHaveKey('accessories_count');
    expect($row)->toHaveKey('consumables_count');
    expect($row)->toHaveKey('notes');
});
