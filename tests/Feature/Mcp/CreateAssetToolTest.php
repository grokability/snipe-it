<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CreateAssetTool;
use App\Models\AssetModel;
use App\Models\Statuslabel;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CreateAssetToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->createAssets()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new CreateAssetTool)->handle(new Request($args));
    }

    public function test_creates_asset_with_required_fields()
    {
        $model = AssetModel::factory()->create();
        $status = Statuslabel::factory()->create();

        $content = $this->handle([
            'model_id' => $model->id,
            'status_id' => $status->id,
            'asset_tag' => 'TEST-001',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('assets', [
            'model_id' => $model->id,
            'status_id' => $status->id,
            'asset_tag' => 'TEST-001',
        ]);
    }

    public function test_response_includes_id_and_asset_tag()
    {
        $model = AssetModel::factory()->create();
        $status = Statuslabel::factory()->create();

        $content = $this->handle([
            'model_id' => $model->id,
            'status_id' => $status->id,
            'asset_tag' => 'TAG-RESP-001',
        ])->getStructuredContent();

        $this->assertArrayHasKey('id', $content);
        $this->assertEquals('TAG-RESP-001', $content['asset_tag']);
    }

    public function test_returns_error_when_model_id_missing()
    {
        $status = Statuslabel::factory()->create();

        $this->assertTrue($this->handle([
            'status_id' => $status->id,
            'asset_tag' => 'TAG-NO-MODEL',
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_status_id_missing()
    {
        $model = AssetModel::factory()->create();

        $this->assertTrue($this->handle([
            'model_id' => $model->id,
            'asset_tag' => 'TAG-NO-STATUS',
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_asset_tag_missing()
    {
        $model = AssetModel::factory()->create();
        $status = Statuslabel::factory()->create();

        $this->assertTrue($this->handle([
            'model_id' => $model->id,
            'status_id' => $status->id,
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $model = AssetModel::factory()->create();
        $status = Statuslabel::factory()->create();

        $this->assertTrue($this->handle([
            'model_id' => $model->id,
            'status_id' => $status->id,
            'asset_tag' => 'TAG-BLOCKED',
        ])->responses()->first()->isError());

        $this->assertDatabaseMissing('assets', ['asset_tag' => 'TAG-BLOCKED']);
    }
}
