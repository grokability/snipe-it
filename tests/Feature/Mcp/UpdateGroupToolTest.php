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
}
