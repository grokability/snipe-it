<?php

use App\Helpers\Helper;
use App\Models\Group;
use App\Models\User;

test('storing group requires super admin permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->postJson(route('api.groups.store'))
        ->assertForbidden();
});

test('can store group with permissions passed', function () {
    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.groups.store'), [
            'name' => 'My Awesome Group',
            'notes' => 'My Awesome Note',
            'permissions' => [
                'admin' => '1',
                'import' => '1',
                'reports.view' => '0',
            ],
        ])
        ->assertOk();

    $group = Group::where('name', 'My Awesome Group')->where('notes', 'My Awesome Note')->first();

    expect($group)->not->toBeNull();
    expect($group->decodePermissions()['admin'])->toEqual('1');
    expect($group->decodePermissions()['import'])->toEqual('1');
    expect($group->decodePermissions()['reports.view'])->toEqual('0');
});

test('storing group without permission passed', function () {
    $superuser = User::factory()->superuser()->create();
    $this->actingAsForApi($superuser)
        ->postJson(route('api.groups.store'), [
            'name' => 'My Awesome Group',
        ])
        ->assertOk();

    $group = Group::where('name', 'My Awesome Group')->first();

    expect($group)->not->toBeNull();

    expect($group->decodePermissions())->toEqual(Helper::selectedPermissionsArray(config('permissions'), config('permissions')), 'Default group permissions were not set as expected');

    $this->actingAsForApi($superuser)
        ->getJson(route('api.groups.show', ['group' => $group]))
        ->assertOk();
});

test('storing group with invalid permission drops bad permission', function () {
    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.groups.store'), [
            'name' => 'My Awesome Group',
            'permissions' => [
                'admin' => '1',
                'snipe_is_awesome' => '1',
            ],
        ])
        ->assertOk();

    $group = Group::where('name', 'My Awesome Group')->first();
    expect($group)->not->toBeNull();
    expect($group->decodePermissions()['admin'])->toEqual('1');
    expect($group->decodePermissions())->not->toContain('snipe_is_awesome');
});
