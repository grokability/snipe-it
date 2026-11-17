<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ListGroupsTool;
use App\Models\Group;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ListGroupsToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new ListGroupsTool)->handle(new Request($args));
    }

    public function test_returns_groups_with_pagination()
    {
        Group::factory()->count(2)->create();

        $content = $this->handle()->getStructuredContent();

        $this->assertArrayHasKey('groups', $content);
        $this->assertArrayHasKey('total', $content);
        $this->assertGreaterThanOrEqual(2, $content['total']);
    }

    public function test_each_group_has_key_fields()
    {
        Group::factory()->create();

        $content = $this->handle(['limit' => 1])->getStructuredContent();

        $this->assertNotEmpty($content['groups']);
        $group = $content['groups'][0];
        $this->assertArrayHasKey('id', $group);
        $this->assertArrayHasKey('name', $group);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle()->responses()->first()->isError());
    }
}
