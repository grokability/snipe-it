<?php

use App\Models\Department;
use App\Models\Group;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->editUsers()->create());
});

function handle(array $args): ResponseFactory
{
    return handle(new Request($args));
}

test('updates user by id', function () {
    $user = User::factory()->create(['first_name' => 'Old']);

    $content = handle([
        'id' => $user->id,
        'first_name' => 'New',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('users', ['id' => $user->id, 'first_name' => 'New']);
});

test('updates user by username', function () {
    $user = User::factory()->create(['username' => 'lookup.by.username', 'jobtitle' => 'Old Title']);

    $content = handle([
        'username' => 'lookup.by.username',
        'jobtitle' => 'New Title',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('users', ['id' => $user->id, 'jobtitle' => 'New Title']);
});

test('updates user by email', function () {
    $user = User::factory()->create(['email' => 'update.by@example.com', 'jobtitle' => 'Old Title']);

    $content = handle([
        'email' => 'update.by@example.com',
        'jobtitle' => 'Senior Engineer',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('users', ['id' => $user->id, 'jobtitle' => 'Senior Engineer']);
});

test('renames username via new username', function () {
    $user = User::factory()->create(['username' => 'old.username.mcp']);

    $content = handle([
        'username' => 'old.username.mcp',
        'new_username' => 'new.username.mcp',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($content['username'])->toEqual('new.username.mcp');
    $this->assertDatabaseHas('users', ['id' => $user->id, 'username' => 'new.username.mcp']);
});

test('updates email via new email', function () {
    $user = User::factory()->create(['email' => 'before@example.com']);

    handle([
        'id' => $user->id,
        'new_email' => 'after@example.com',
    ]);

    $this->assertDatabaseHas('users', ['id' => $user->id, 'email' => 'after@example.com']);
});

test('updates password', function () {
    $user = User::factory()->create();

    handle([
        'id' => $user->id,
        'password' => 'newpassword99',
    ]);

    expect(Hash::check('newpassword99', $user->fresh()->password))->toBeTrue();
});

test('updates multiple fields at once', function () {
    $user = User::factory()->create();
    $department = Department::factory()->create();
    $location = Location::factory()->create();

    handle([
        'id' => $user->id,
        'jobtitle' => 'Team Lead',
        'phone' => '555-9999',
        'department_id' => $department->id,
        'location_id' => $location->id,
    ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'jobtitle' => 'Team Lead',
        'phone' => '555-9999',
        'department_id' => $department->id,
        'location_id' => $location->id,
    ]);
});

test('returns error when user not found', function () {
    expect(handle([
        'id' => 999999,
        'first_name' => 'Ghost',
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when no identifier provided', function () {
    expect(handle([
        'first_name' => 'No Identifier',
    ])->responses()->first()->isError())->toBeTrue();
});

test('response includes id and username', function () {
    $user = User::factory()->create();

    $content = handle([
        'id' => $user->id,
        'first_name' => 'Updated',
    ])->getStructuredContent();

    expect($content['id'])->toEqual($user->id);
    expect($content['username'])->toEqual($user->username);
});

test('non admin cannot change auth fields of admin', function () {
    // Acting user is a plain user (set in setUp); target is an admin
    $admin = User::factory()->admin()->create();

    $response = handle([
        'id' => $admin->id,
        'password' => 'shouldnotwork1',
    ]);

    expect($response->responses()->first()->isError())->toBeTrue();
    expect(Hash::check('shouldnotwork1', $admin->fresh()->password))->toBeFalse();
});

test('non admin cannot change auth fields of superuser', function () {
    $superuser = User::factory()->superuser()->create();

    $response = handle([
        'id' => $superuser->id,
        'new_email' => 'hacked@example.com',
    ]);

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseMissing('users', ['id' => $superuser->id, 'email' => 'hacked@example.com']);
});

test('admin cannot change auth fields of superuser', function () {
    $this->actingAs(User::factory()->admin()->create());
    $superuser = User::factory()->superuser()->create();

    $response = handle([
        'id' => $superuser->id,
        'new_username' => 'hijacked',
    ]);

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseMissing('users', ['id' => $superuser->id, 'username' => 'hijacked']);
});

test('superuser can change auth fields of any user', function () {
    $this->actingAs(User::factory()->superuser()->create());
    $target = User::factory()->admin()->create();

    $content = handle([
        'id' => $target->id,
        'new_email' => 'superchanged@example.com',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('users', ['id' => $target->id, 'email' => 'superchanged@example.com']);
});

test('non auth fields are still updated even without can edit auth fields', function () {
    // A non-admin updating an admin's non-sensitive fields (jobtitle etc.) should still work
    $admin = User::factory()->admin()->create(['jobtitle' => 'Old Title']);

    $content = handle([
        'id' => $admin->id,
        'jobtitle' => 'New Title',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('users', ['id' => $admin->id, 'jobtitle' => 'New Title']);
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());
    $user = User::factory()->create();

    expect(handle([
        'id' => $user->id,
        'first_name' => 'Should Not Update',
    ])->responses()->first()->isError())->toBeTrue();
});

test('superadmin can assign group ids', function () {
    $this->actingAs(User::factory()->superuser()->create());
    $user = User::factory()->create();
    $group = Group::factory()->create();

    $content = handle([
        'id' => $user->id,
        'group_ids' => [$group->id],
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($content['group_ids'])->toContain($group->id);
    expect($user->fresh()->groups->contains($group->id))->toBeTrue();
});

test('non superadmin cannot assign group ids', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();

    $response = handle([
        'id' => $user->id,
        'group_ids' => [$group->id],
    ]);

    expect($response->responses()->first()->isError())->toBeTrue();
    expect($user->fresh()->groups->contains($group->id))->toBeFalse();
});

test('group ids replaces existing groups', function () {
    $this->actingAs(User::factory()->superuser()->create());
    $user = User::factory()->create();
    $oldGroup = Group::factory()->create();
    $newGroup = Group::factory()->create();
    $user->groups()->sync([$oldGroup->id]);

    handle([
        'id' => $user->id,
        'group_ids' => [$newGroup->id],
    ]);

    $freshGroups = $user->fresh()->groups->pluck('id');
    expect($freshGroups)->toContain($newGroup->id);
    expect($freshGroups)->not->toContain($oldGroup->id);
});
