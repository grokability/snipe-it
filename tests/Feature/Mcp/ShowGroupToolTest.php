<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ShowGroupTool;
use App\Models\Group;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ShowGroupToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new ShowGroupTool)->handle(new Request($args));
    }

    public function test_returns_group_by_id()
    {
        $group = Group::factory()->create();

        $content = $this->handle(['id' => $group->id])->getStructuredContent();

        $this->assertEquals($group->id, $content['id']);
        $this->assertEquals($group->name, $content['name']);
    }

    public function test_returns_group_by_name()
    {
        $group = Group::factory()->create();

        $content = $this->handle(['name' => $group->name])->getStructuredContent();

        $this->assertEquals($group->id, $content['id']);
        $this->assertEquals($group->name, $content['name']);
    }

    public function test_returns_error_when_no_identifier_provided()
    {
        $this->assertTrue($this->handle()->responses()->first()->isError());
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
