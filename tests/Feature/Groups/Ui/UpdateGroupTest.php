<?php

use App\Models\Group;
use App\Models\User;

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('groups.edit', Group::factory()->create()->id))
        ->assertOk();
});

test('user can edit groups', function () {
    $group = Group::factory()->create(['name' => 'Test Group']);
    expect(Group::where('name', 'Test Group')->exists())->toBeTrue();

    $response = $this->actingAs(User::factory()->superuser()->create())
        ->put(route('groups.update', ['group' => $group]), [
            'name' => 'Test Group Edited',
            'notes' => 'Test Note Edited',
        ])
        ->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('groups.index'));

    $this->followRedirects($response)->assertSee('Success');
    expect(Group::where('name', 'Test Group Edited')->where('notes', 'Test Note Edited')->exists())->toBeTrue();
});

test('user can edit group permissions and invalid permissions are dropped', function () {
    $group = Group::factory()->create(['name' => 'Permission Test Group']);

    $this->actingAs(User::factory()->superuser()->create())
        ->put(route('groups.update', ['group' => $group]), [
            'name' => 'Permission Test Group Edited',
            'notes' => 'Permission test note',
            'permission' => [
                'admin' => '1',
                'reports.view' => '0',
                'invalid.permission' => '1',
            ],
        ])
        ->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('groups.index'));

    $decoded = (array) $group->refresh()->decodePermissions();

    expect($decoded)->toHaveKey('admin');
    $this->assertArrayHasKey('reports.view', $decoded);
    $this->assertArrayNotHasKey('invalid.permission', $decoded);
});

test('permissions are preserved if permission payload not sent', function () {
    $group = Group::factory()->create([
        'permissions' => json_encode(['admin' => '1']),
    ]);

    $this->actingAs(User::factory()->superuser()->create())
        ->put(route('groups.update', ['group' => $group]), [
            'name' => 'Group Name Only',
            'notes' => 'Updated notes',
        ])
        ->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('groups.index'));

    $decoded = (array) $group->refresh()->decodePermissions();

    expect($group->name)->toBe('Group Name Only');
    expect((string) ($decoded['admin'] ?? null))->toBe('1');
});
