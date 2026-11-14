<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ShowAssetModelTool;
use App\Models\AssetModel;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ShowAssetModelToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewAssetModels()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new ShowAssetModelTool)->handle(new Request($args));
    }

    public function test_returns_model_by_id()
    {
        $model = AssetModel::factory()->create();

        $content = $this->handle(['id' => $model->id])->getStructuredContent();

        $this->assertEquals($model->id, $content['id']);
        $this->assertEquals($model->name, $content['name']);
    }

    public function test_returns_model_by_name()
    {
        $model = AssetModel::factory()->create();

        $content = $this->handle(['name' => $model->name])->getStructuredContent();

        $this->assertEquals($model->id, $content['id']);
        $this->assertEquals($model->name, $content['name']);
    }

    public function test_returns_error_when_no_identifier_provided()
    {
        $this->assertTrue($this->handle()->responses()->first()->isError());
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 99999])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $model = AssetModel::factory()->create();

        $this->assertTrue($this->handle(['id' => $model->id])->responses()->first()->isError());
    }
}
