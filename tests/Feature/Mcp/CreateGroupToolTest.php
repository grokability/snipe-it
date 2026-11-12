<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CreateGroupTool;
use App\Models\Group;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CreateGroupToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new CreateGroupTool)->handle(new Request($args));
    }

    public function test_creates_group()
    {
        $name = 'Test MCP Group '.uniqid();

        $this->handle(['name' => $name]);

        $this->assertDatabaseHas('permission_groups', ['name' => $name]);
    }

    public function test_response_includes_id_and_name()
    {
        $name = 'Test MCP Group '.uniqid();

        $content = $this->handle(['name' => $name])->getStructuredContent();

        $this->assertArrayHasKey('id', $content);
        $this->assertArrayHasKey('name', $content);
        $this->assertEquals($name, $content['name']);
        $this->assertTrue($content['success']);
    }

    public function test_returns_error_when_name_missing()
    {
        $this->assertTrue($this->handle()->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $name = 'Unauthorized Group '.uniqid();

        $this->handle(['name' => $name]);

        $this->assertDatabaseMissing('permission_groups', ['name' => $name]);
    }

    public function test_creates_group_with_valid_permissions()
    {
        $name = 'Permissions Group '.uniqid();

        $content = $this->handle([
            'name' => $name,
            'permissions' => json_encode(['assets.view' => 1, 'assets.create' => -1]),
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertArrayHasKey('permissions', $content);
        $group = Group::where('name', $name)->first();
        $this->assertNotNull($group);
        $decoded = json_decode($group->permissions, true);
        $this->assertEquals(1, $decoded['assets.view']);
        $this->assertEquals(-1, $decoded['assets.create']);
    }

    public function test_returns_error_for_invalid_permission_key()
    {
        $response = $this->handle([
            'name' => 'Bad Perms Group '.uniqid(),
            'permissions' => json_encode(['not.a.real.key' => 1]),
        ]);

        $this->assertTrue($response->responses()->first()->isError());
    }

    public function test_returns_error_for_invalid_permission_value()
    {
        $response = $this->handle([
            'name' => 'Bad Value Group '.uniqid(),
            'permissions' => json_encode(['assets.view' => 0]),
        ]);

        $this->assertTrue($response->responses()->first()->isError());
    }

    public function test_returns_error_for_malformed_permissions_json()
    {
        $response = $this->handle([
            'name' => 'Malformed Group '.uniqid(),
            'permissions' => 'not valid json',
        ]);

        $this->assertTrue($response->responses()->first()->isError());
    }
}
