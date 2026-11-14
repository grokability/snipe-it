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

    // --- Name-to-ID resolution (reference tests for ResolvesForeignKeyByName) ---

    public function test_resolves_model_and_status_by_name()
    {
        // Use test-unique names so the lookup does not collide with
        // seeder-created rows (e.g. the "Ready to Deploy" status label
        // the base seeder provides).
        $model = AssetModel::factory()->create(['name' => 'MCP Test Model Alpha']);
        $status = Statuslabel::factory()->create(['name' => 'MCP Test Status Alpha']);

        $content = $this->handle([
            'model_name' => 'MCP Test Model Alpha',
            'status_name' => 'MCP Test Status Alpha',
            'asset_tag' => 'TAG-NAME-001',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('assets', [
            'model_id' => $model->id,
            'status_id' => $status->id,
            'asset_tag' => 'TAG-NAME-001',
        ]);
    }

    public function test_name_resolution_is_case_insensitive()
    {
        $model = AssetModel::factory()->create(['name' => 'MCP Test Model Beta']);
        $status = Statuslabel::factory()->create(['name' => 'MCP Test Status Beta']);

        $content = $this->handle([
            'model_name' => 'mcp test model beta',
            'status_name' => 'MCP TEST STATUS BETA',
            'asset_tag' => 'TAG-CASE-001',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('assets', [
            'model_id' => $model->id,
            'status_id' => $status->id,
            'asset_tag' => 'TAG-CASE-001',
        ]);
    }

    public function test_id_wins_when_both_id_and_name_are_passed()
    {
        $modelFromId = AssetModel::factory()->create(['name' => 'From ID']);
        AssetModel::factory()->create(['name' => 'From Name']);
        $status = Statuslabel::factory()->create();

        $content = $this->handle([
            'model_id' => $modelFromId->id,
            'model_name' => 'From Name',
            'status_id' => $status->id,
            'asset_tag' => 'TAG-BOTH-001',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('assets', ['model_id' => $modelFromId->id, 'asset_tag' => 'TAG-BOTH-001']);
    }

    public function test_returns_error_when_name_matches_multiple_rows()
    {
        AssetModel::factory()->count(2)->create(['name' => 'MacBook Pro']);
        $status = Statuslabel::factory()->create();

        $response = $this->handle([
            'model_name' => 'MacBook Pro',
            'status_id' => $status->id,
            'asset_tag' => 'TAG-AMBIG-001',
        ]);

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertDatabaseMissing('assets', ['asset_tag' => 'TAG-AMBIG-001']);
    }

    public function test_returns_error_when_name_does_not_match_any_row()
    {
        $status = Statuslabel::factory()->create();

        $response = $this->handle([
            'model_name' => 'Nonexistent Model Name',
            'status_id' => $status->id,
            'asset_tag' => 'TAG-NOMATCH-001',
        ]);

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertDatabaseMissing('assets', ['asset_tag' => 'TAG-NOMATCH-001']);
    }
}
