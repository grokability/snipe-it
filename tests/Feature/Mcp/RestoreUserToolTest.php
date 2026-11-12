<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\RestoreUserTool;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class RestoreUserToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new RestoreUserTool)->handle(new Request($args));
    }

    public function test_restores_soft_deleted_user()
    {
        $user = User::factory()->create();
        $user->delete();

        $this->assertSoftDeleted('users', ['id' => $user->id]);

        $content = $this->handle(['id' => $user->id])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertNotSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_not_deleted()
    {
        $user = User::factory()->create();

        $this->assertTrue($this->handle(['id' => $user->id])->responses()->first()->isError());
        $this->assertDatabaseHas('users', ['id' => $user->id, 'deleted_at' => null]);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $user = User::factory()->create();
        $user->delete();

        $this->assertTrue($this->handle(['id' => $user->id])->responses()->first()->isError());
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }
}
