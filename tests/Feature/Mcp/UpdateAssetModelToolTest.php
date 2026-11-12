<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\UpdateAssetModelTool;
use App\Models\AssetModel;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class UpdateAssetModelToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->editAssetModels()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new UpdateAssetModelTool)->handle(new Request($args));
    }

    public function test_updates_model_by_id()
    {
        $model = AssetModel::factory()->create();

        $content = $this->handle([
            'id' => $model->id,
            'model_number' => 'MN-001',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('models', [
            'id' => $model->id,
            'model_number' => 'MN-001',
        ]);
    }

    public function test_renames_via_new_name()
    {
        $model = AssetModel::factory()->create();

        $content = $this->handle([
            'id' => $model->id,
            'new_name' => 'Renamed Model',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertEquals('Renamed Model', $content['name']);
        $this->assertDatabaseHas('models', [
            'id' => $model->id,
            'name' => 'Renamed Model',
        ]);
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 99999])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $model = AssetModel::factory()->create();

        $this->assertTrue($this->handle(['id' => $model->id, 'model_number' => 'HACKED'])->responses()->first()->isError());
    }
}
