<?php

namespace Tests\Feature\Groups\Api;

use App\Helpers\Helper;
use App\Models\Group;
use App\Models\User;
use Tests\TestCase;

class StoreGroupTest extends TestCase
{
    public function testStoringGroupRequiresSuperAdminPermission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.groups.store'))
            ->assertForbidden();
    }

    public function testCanStoreGroupWithPermissionsPassed()
    {
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

        $this->assertNotNull($group);
        $this->assertEquals('1', $group->decodePermissions()['admin']);
        $this->assertEquals('1', $group->decodePermissions()['import']);
        $this->assertEquals('0', $group->decodePermissions()['reports.view']);
    }

    public function testStoringGroupWithoutPermissionPassed()
    {
        $superuser = User::factory()->superuser()->create();
        $this->actingAsForApi($superuser)
            ->postJson(route('api.groups.store'), [
                'name' => 'My Awesome Group'
            ])
            ->assertOk();

        $group = Group::where('name', 'My Awesome Group')->first();

        $this->assertNotNull($group);

        $this->assertEquals(
            Helper::selectedPermissionsArray(config('permissions'), config('permissions')),
            $group->decodePermissions(),
            'Default group permissions were not set as expected',
        );

        $this->actingAsForApi($superuser)
            ->getJson(route('api.groups.show',  ['group' => $group]))
            ->assertOk();
    }

    public function testStoringGroupWithInvalidPermissionDropsBadPermission()
    {
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
        $this->assertNotNull($group);
        $this->assertEquals('1', $group->decodePermissions()['admin']);
        $this->assertNotContains('snipe_is_awesome', $group->decodePermissions());

    }
}
