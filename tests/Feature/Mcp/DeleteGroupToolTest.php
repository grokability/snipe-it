<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\DeleteGroupTool;
use App\Models\Group;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class DeleteGroupToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new DeleteGroupTool)->handle(new Request($args));
    }

    public function test_deletes_group_by_id()
    {
        $group = Group::factory()->create();

        $this->handle(['id' => $group->id]);

        $this->assertDatabaseMissing('permission_groups', ['id' => $group->id]);
    }

    public function test_deletes_group_by_name()
    {
        $group = Group::factory()->create();

        $this->handle(['name' => $group->name]);

        $this->assertDatabaseMissing('permission_groups', ['id' => $group->id]);
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $group = Group::factory()->create();

        $this->assertTrue($this->handle(['id' => $group->id])->responses()->first()->isError());
    }
}
