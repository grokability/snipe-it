<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ShowUserPermissionsTool;
use App\Models\Group;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ShowUserPermissionsToolTest extends TestCase
{
    private function handle(array $args = []): ResponseFactory
    {
        return (new ShowUserPermissionsTool)->handle(new Request($args));
    }

    public function test_returns_self_permissions_when_no_identifier_provided()
    {
        $user = User::factory()->viewAssets()->create();
        $this->actingAs($user);

        $content = $this->handle()->getStructuredContent();

        $this->assertEquals($user->id, $content['id']);
        $this->assertEquals($user->username, $content['username']);
        $this->assertContains('assets.view', $content['allowed_permissions']);
        $this->assertFalse($content['is_superuser']);
    }

    public function test_returns_target_user_permissions_by_id_when_authorized()
    {
        $viewer = User::factory()->viewUsers()->create();
        $target = User::factory()->viewAssets()->editAssets()->create();
        $this->actingAs($viewer);

        $content = $this->handle(['id' => $target->id])->getStructuredContent();

        $this->assertEquals($target->id, $content['id']);
        $this->assertContains('assets.view', $content['allowed_permissions']);
        $this->assertContains('assets.edit', $content['allowed_permissions']);
    }

    public function test_returns_target_user_permissions_by_username()
    {
        $viewer = User::factory()->viewUsers()->create();
        $target = User::factory()->viewAssets()->create(['username' => 'jdoe']);
        $this->actingAs($viewer);

        $content = $this->handle(['username' => 'jdoe'])->getStructuredContent();

        $this->assertEquals($target->id, $content['id']);
    }

    public function test_detects_superuser()
    {
        $user = User::factory()->superuser()->create();
        $this->actingAs($user);

        $content = $this->handle()->getStructuredContent();

        $this->assertTrue($content['is_superuser']);
    }

    public function test_includes_group_memberships()
    {
        $group = Group::factory()->create(['name' => 'Help Desk']);
        $user = User::factory()->create();
        $user->groups()->attach($group->id);
        $this->actingAs($user);

        $content = $this->handle()->getStructuredContent();

        $this->assertCount(1, $content['groups']);
        $this->assertEquals('Help Desk', $content['groups'][0]['name']);
    }

    public function test_reports_group_sourced_permissions()
    {
        $group = Group::factory()->create([
            'name' => 'Asset Viewers',
            'permissions' => json_encode(['assets.view' => '1']),
        ]);
        $user = User::factory()->create();
        $user->groups()->attach($group->id);
        $this->actingAs($user);

        $content = $this->handle()->getStructuredContent();

        $this->assertContains('assets.view', $content['allowed_permissions']);
    }

    public function test_returns_error_when_user_not_found()
    {
        $this->actingAs(User::factory()->viewUsers()->create());

        $this->assertTrue($this->handle(['id' => 999999])->responses()->first()->isError());
    }

    public function test_returns_error_when_viewer_lacks_permission()
    {
        $target = User::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle(['id' => $target->id])->responses()->first()->isError());
    }

    public function test_self_lookup_bypasses_view_permission_gate()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $content = $this->handle(['id' => $user->id])->getStructuredContent();

        $this->assertEquals($user->id, $content['id']);
    }
}
