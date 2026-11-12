<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\DeleteUserTool;
use App\Models\Asset;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class DeleteUserToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->deleteUsers()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new DeleteUserTool)->handle(new Request($args));
    }

    public function test_deletes_user_by_id()
    {
        $user = User::factory()->create();

        $content = $this->handle(['id' => $user->id])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_deletes_user_by_username()
    {
        $user = User::factory()->create(['username' => 'delete.by.username']);

        $content = $this->handle(['username' => 'delete.by.username'])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_deletes_user_by_email()
    {
        $user = User::factory()->create(['email' => 'delete.by@example.com']);

        $content = $this->handle(['email' => 'delete.by@example.com'])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_response_includes_username()
    {
        $user = User::factory()->create(['username' => 'response.check.mcp']);

        $content = $this->handle(['id' => $user->id])->getStructuredContent();

        $this->assertEquals('response.check.mcp', $content['username']);
    }

    public function test_returns_error_when_user_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999])->responses()->first()->isError());
    }

    public function test_returns_error_when_deleting_own_account()
    {
        $authUser = auth()->user();

        $response = $this->handle(['id' => $authUser->id]);

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertNotSoftDeleted('users', ['id' => $authUser->id]);
    }

    public function test_returns_error_when_user_has_assigned_assets()
    {
        $user = User::factory()->create();
        Asset::factory()->assignedToUser($user)->create();

        $response = $this->handle(['id' => $user->id]);

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertNotSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $user = User::factory()->create();

        $this->assertTrue($this->handle(['id' => $user->id])->responses()->first()->isError());
        $this->assertNotSoftDeleted('users', ['id' => $user->id]);
    }
}
