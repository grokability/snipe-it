<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ShowUserTool;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ShowUserToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewUsers()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new ShowUserTool)->handle(new Request($args));
    }

    public function test_finds_user_by_id()
    {
        $user = User::factory()->create();

        $content = $this->handle(['id' => $user->id])->getStructuredContent();

        $this->assertEquals($user->id, $content['id']);
        $this->assertEquals($user->username, $content['username']);
    }

    public function test_finds_user_by_username()
    {
        $user = User::factory()->create(['username' => 'test.user.mcp']);

        $content = $this->handle(['username' => 'test.user.mcp'])->getStructuredContent();

        $this->assertEquals($user->id, $content['id']);
        $this->assertEquals('test.user.mcp', $content['username']);
    }

    public function test_finds_user_by_email()
    {
        $user = User::factory()->create(['email' => 'mcp.test@example.com']);

        $content = $this->handle(['email' => 'mcp.test@example.com'])->getStructuredContent();

        $this->assertEquals($user->id, $content['id']);
        $this->assertEquals('mcp.test@example.com', $content['email']);
    }

    public function test_returns_error_when_no_identifier_provided()
    {
        $this->assertTrue($this->handle([])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_not_found_by_id()
    {
        $this->assertTrue($this->handle(['id' => 999999])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_not_found_by_username()
    {
        $this->assertTrue($this->handle(['username' => 'no.such.user'])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_not_found_by_email()
    {
        $this->assertTrue($this->handle(['email' => 'nobody@nowhere.invalid'])->responses()->first()->isError());
    }

    public function test_response_includes_expected_fields()
    {
        $user = User::factory()->create();

        $content = $this->handle(['id' => $user->id])->getStructuredContent();

        foreach (['id', 'username', 'email', 'first_name', 'last_name', 'activated', 'assets_count', 'licenses_count'] as $field) {
            $this->assertArrayHasKey($field, $content);
        }
    }

    public function test_response_includes_asset_and_license_counts()
    {
        $user = User::factory()->create();

        $content = $this->handle(['id' => $user->id])->getStructuredContent();

        $this->assertIsInt($content['assets_count']);
        $this->assertIsInt($content['licenses_count']);
    }

    public function test_response_includes_company_and_department()
    {
        $user = User::factory()->create();

        $content = $this->handle(['id' => $user->id])->getStructuredContent();

        $this->assertArrayHasKey('company', $content);
        $this->assertArrayHasKey('department', $content);
        $this->assertArrayHasKey('location', $content);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $user = User::factory()->create();

        $this->assertTrue($this->handle(['id' => $user->id])->responses()->first()->isError());
    }
}
