<?php

use App\Mcp\Tools\CreateUserTool;
use App\Models\Department;
use App\Models\Group;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->createUsers()->create());
});

function createUserTool(array $args): ResponseFactory
{
    return (new CreateUserTool)->handle(new Request($args));
}

test('creates user with required fields', function () {
    $content = createUserTool([
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'username' => 'jane.doe.mcp',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('users', ['username' => 'jane.doe.mcp', 'first_name' => 'Jane']);
});

test('response includes new user id and username', function () {
    $content = createUserTool([
        'first_name' => 'Test',
        'username' => 'new.user.mcp',
    ])->getStructuredContent();

    expect($content)->toHaveKey('id');
    expect($content)->toHaveKey('username');
    expect($content['username'])->toEqual('new.user.mcp');
    expect($content['id'])->toBeInt();
});

test('hashes password when provided', function () {
    createUserTool([
        'first_name' => 'Secure',
        'username' => 'secure.user.mcp',
        'password' => 'secret1234',
    ]);

    $user = User::where('username', 'secure.user.mcp')->first();
    expect($user)->not->toBeNull();
    expect(Hash::check('secret1234', $user->password))->toBeTrue();
});

test('creates user with no password when omitted', function () {
    createUserTool([
        'first_name' => 'Nopass',
        'username' => 'nopass.user.mcp',
    ]);

    $user = User::where('username', 'nopass.user.mcp')->first();
    expect($user)->not->toBeNull();
    expect(Hash::check('', $user->password))->toBeFalse();
});

test('creates user with email and contact fields', function () {
    createUserTool([
        'first_name' => 'Contact',
        'username' => 'contact.user.mcp',
        'email' => 'contact@example.com',
        'phone' => '555-1234',
        'jobtitle' => 'Engineer',
    ]);

    $this->assertDatabaseHas('users', [
        'username' => 'contact.user.mcp',
        'email' => 'contact@example.com',
        'phone' => '555-1234',
        'jobtitle' => 'Engineer',
    ]);
});

test('creates user with department and location', function () {
    $department = Department::factory()->create();
    $location = Location::factory()->create();

    createUserTool([
        'first_name' => 'Located',
        'username' => 'located.user.mcp',
        'department_id' => $department->id,
        'location_id' => $location->id,
    ]);

    $this->assertDatabaseHas('users', [
        'username' => 'located.user.mcp',
        'department_id' => $department->id,
        'location_id' => $location->id,
    ]);
});

test('returns error when username missing', function () {
    expect(createUserTool([
        'first_name' => 'No Username',
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when first name missing', function () {
    expect(createUserTool([
        'username' => 'no.firstname.mcp',
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when username already exists', function () {
    User::factory()->create(['username' => 'duplicate.mcp']);

    $response = createUserTool([
        'first_name' => 'Dupe',
        'username' => 'duplicate.mcp',
    ]);

    expect($response->responses()->first()->isError())->toBeTrue();
});

test('activates user by default', function () {
    createUserTool([
        'first_name' => 'Active',
        'username' => 'default.active.mcp',
    ]);

    $this->assertDatabaseHas('users', ['username' => 'default.active.mcp', 'activated' => 1]);
});

test('can create deactivated user', function () {
    createUserTool([
        'first_name' => 'Inactive',
        'username' => 'inactive.user.mcp',
        'activated' => false,
    ]);

    $this->assertDatabaseHas('users', ['username' => 'inactive.user.mcp', 'activated' => 0]);
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    expect(createUserTool([
        'first_name' => 'Blocked',
        'username' => 'blocked.user.mcp',
    ])->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseMissing('users', ['username' => 'blocked.user.mcp']);
});

test('superadmin can assign group ids', function () {
    $this->actingAs(User::factory()->superuser()->create());
    $group = Group::factory()->create();

    $content = createUserTool([
        'first_name' => 'GroupedUser',
        'username' => 'grouped.user.mcp',
        'group_ids' => [$group->id],
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $user = User::where('username', 'grouped.user.mcp')->first();
    expect($user)->not->toBeNull();
    expect($user->groups->contains($group->id))->toBeTrue();
});

test('non superadmin cannot assign group ids', function () {
    $group = Group::factory()->create();

    $response = createUserTool([
        'first_name' => 'Denied',
        'username' => 'denied.groups.mcp',
        'group_ids' => [$group->id],
    ]);

    expect($response->responses()->first()->isError())->toBeTrue();
});
