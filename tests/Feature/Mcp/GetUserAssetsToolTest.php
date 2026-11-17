<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\GetUserAssetsTool;
use App\Models\Asset;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class GetUserAssetsToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewUsers()->viewAssets()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new GetUserAssetsTool)->handle(new Request($args));
    }

    public function test_returns_assets_for_user()
    {
        $user = User::factory()->create();
        Asset::factory()->assignedToUser($user)->create();

        $content = $this->handle(['id' => $user->id])->getStructuredContent();

        $this->assertEquals($user->id, $content['user_id']);
        $this->assertEquals(1, $content['total']);
        $this->assertCount(1, $content['assets']);
    }

    public function test_returns_empty_array_when_user_has_no_assets()
    {
        $user = User::factory()->create();

        $content = $this->handle(['id' => $user->id])->getStructuredContent();

        $this->assertEquals(0, $content['total']);
        $this->assertCount(0, $content['assets']);
    }

    public function test_returns_error_when_user_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $user = User::factory()->create();

        $this->assertTrue($this->handle(['id' => $user->id])->responses()->first()->isError());
    }
}
