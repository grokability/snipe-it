<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\DeleteAssetModelTool;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class DeleteAssetModelToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->deleteAssetModels()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new DeleteAssetModelTool)->handle(new Request($args));
    }

    public function test_deletes_model_by_id()
    {
        $model = AssetModel::factory()->create();

        $content = $this->handle(['id' => $model->id])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('models', ['id' => $model->id]);
    }

    public function test_deletes_model_by_name()
    {
        $model = AssetModel::factory()->create();

        $content = $this->handle(['name' => $model->name])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('models', ['id' => $model->id]);
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 99999])->responses()->first()->isError());
    }

    public function test_returns_error_when_model_has_assets()
    {
        $model = AssetModel::factory()->create();
        Asset::factory()->create(['model_id' => $model->id]);

        $response = $this->handle(['id' => $model->id]);

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertNotSoftDeleted('models', ['id' => $model->id]);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $model = AssetModel::factory()->create();

        $this->assertTrue($this->handle(['id' => $model->id])->responses()->first()->isError());
        $this->assertNotSoftDeleted('models', ['id' => $model->id]);
    }
}
