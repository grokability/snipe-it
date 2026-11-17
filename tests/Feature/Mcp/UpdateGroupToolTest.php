<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\UpdateGroupTool;
use App\Models\Group;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class UpdateGroupToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new UpdateGroupTool)->handle(new Request($args));
    }

    public function test_updates_group_by_id()
    {
        $group = Group::factory()->create();
        $newNotes = 'Updated notes '.uniqid();

        $this->handle(['id' => $group->id, 'notes' => $newNotes]);

        $this->assertDatabaseHas('permission_groups', ['id' => $group->id, 'notes' => $newNotes]);
    }

    public function test_renames_via_new_name()
    {
        $group = Group::factory()->create();
        $newName = 'Renamed Group '.uniqid();

        $this->handle(['id' => $group->id, 'new_name' => $newName]);

        $this->assertDatabaseHas('permission_groups', ['id' => $group->id, 'name' => $newName]);
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999, 'notes' => 'test'])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $group = Group::factory()->create();

        $this->assertTrue($this->handle(['id' => $group->id, 'notes' => 'test'])->responses()->first()->isError());
    }

    public function test_updates_permissions()
    {
        $group = Group::factory()->create(['permissions' => json_encode(['assets.view' => 1])]);

        $content = $this->handle([
            'id' => $group->id,
            'permissions' => json_encode(['assets.create' => 1, 'assets.edit' => -1]),
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $decoded = json_decode($group->fresh()->permissions, true);
        $this->assertArrayHasKey('assets.create', $decoded);
        $this->assertArrayNotHasKey('assets.view', $decoded);
    }

    public function test_returns_error_for_invalid_permission_key_on_update()
    {
        $group = Group::factory()->create();

        $response = $this->handle([
            'id' => $group->id,
            'permissions' => json_encode(['completely.fake.key' => 1]),
        ]);

        $this->assertTrue($response->responses()->first()->isError());
    }

    public function test_permissions_unchanged_when_not_provided()
    {
        $original = json_encode(['assets.view' => 1]);
        $group = Group::factory()->create(['permissions' => $original]);

        $this->handle(['id' => $group->id, 'notes' => 'updated notes']);

        $this->assertEquals($original, $group->fresh()->permissions);
    }
}
