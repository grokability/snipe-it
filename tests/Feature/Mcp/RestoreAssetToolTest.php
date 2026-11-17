<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\RestoreAssetTool;
use App\Models\Asset;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class RestoreAssetToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new RestoreAssetTool)->handle(new Request($args));
    }

    public function test_restores_soft_deleted_asset()
    {
        $asset = Asset::factory()->create();
        $asset->delete();

        $this->assertSoftDeleted('assets', ['id' => $asset->id]);

        $content = $this->handle(['id' => $asset->id])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertNotSoftDeleted('assets', ['id' => $asset->id]);
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999])->responses()->first()->isError());
    }

    public function test_returns_error_when_asset_not_deleted()
    {
        $asset = Asset::factory()->create();

        $this->assertTrue($this->handle(['id' => $asset->id])->responses()->first()->isError());
        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'deleted_at' => null]);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $asset = Asset::factory()->create();
        $asset->delete();

        $this->assertTrue($this->handle(['id' => $asset->id])->responses()->first()->isError());
        $this->assertSoftDeleted('assets', ['id' => $asset->id]);
    }
}
