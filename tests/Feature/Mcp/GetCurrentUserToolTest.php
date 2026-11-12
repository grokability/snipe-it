<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\GetCurrentUserTool;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class GetCurrentUserToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new GetCurrentUserTool)->handle(new Request($args));
    }

    public function test_returns_current_user_info()
    {
        $user = auth()->user();

        $content = $this->handle()->getStructuredContent();

        $this->assertEquals($user->id, $content['id']);
        $this->assertEquals($user->username, $content['username']);
    }

    public function test_response_includes_expected_fields()
    {
        $content = $this->handle()->getStructuredContent();

        foreach (['id', 'username', 'first_name', 'last_name', 'email', 'company', 'department', 'location', 'employee_num', 'title', 'phone', 'activated'] as $field) {
            $this->assertArrayHasKey($field, $content);
        }
    }
}
