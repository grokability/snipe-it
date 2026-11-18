<?php

use App\Mcp\Servers\SnipeMCPServer;
use App\Mcp\Tools\CreateAssetTool;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->createAssets()->create());
});

function handle(array $args): ResponseFactory
{
    return handle(new Request($args));
}

test('creates asset with required fields', function () {
    $model = AssetModel::factory()->create();
    $status = Statuslabel::factory()->create();

    $content = handle([
        'model_id' => $model->id,
        'status_id' => $status->id,
        'asset_tag' => 'TEST-001',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('assets', [
        'model_id' => $model->id,
        'status_id' => $status->id,
        'asset_tag' => 'TEST-001',
    ]);
});

test('response includes id and asset tag', function () {
    $model = AssetModel::factory()->create();
    $status = Statuslabel::factory()->create();

    $content = handle([
        'model_id' => $model->id,
        'status_id' => $status->id,
        'asset_tag' => 'TAG-RESP-001',
    ])->getStructuredContent();

    expect($content)->toHaveKey('id');
    expect($content['asset_tag'])->toEqual('TAG-RESP-001');
});

test('returns error when model id missing', function () {
    $status = Statuslabel::factory()->create();

    expect(handle([
        'status_id' => $status->id,
        'asset_tag' => 'TAG-NO-MODEL',
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when status id missing', function () {
    $model = AssetModel::factory()->create();

    expect(handle([
        'model_id' => $model->id,
        'asset_tag' => 'TAG-NO-STATUS',
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when asset tag missing', function () {
    $model = AssetModel::factory()->create();
    $status = Statuslabel::factory()->create();

    expect(handle([
        'model_id' => $model->id,
        'status_id' => $status->id,
    ])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    $model = AssetModel::factory()->create();
    $status = Statuslabel::factory()->create();

    expect(handle([
        'model_id' => $model->id,
        'status_id' => $status->id,
        'asset_tag' => 'TAG-BLOCKED',
    ])->responses()->first()->isError())->toBeTrue();

    $this->assertDatabaseMissing('assets', ['asset_tag' => 'TAG-BLOCKED']);
});

test('resolves model and status by name', function () {
    // Use test-unique names so the lookup does not collide with
    // seeder-created rows (e.g. the "Ready to Deploy" status label
    // the base seeder provides).
    $model = AssetModel::factory()->create(['name' => 'MCP Test Model Alpha']);
    $status = Statuslabel::factory()->create(['name' => 'MCP Test Status Alpha']);

    $content = handle([
        'model_name' => 'MCP Test Model Alpha',
        'status_name' => 'MCP Test Status Alpha',
        'asset_tag' => 'TAG-NAME-001',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('assets', [
        'model_id' => $model->id,
        'status_id' => $status->id,
        'asset_tag' => 'TAG-NAME-001',
    ]);
});

test('name resolution is case insensitive', function () {
    $model = AssetModel::factory()->create(['name' => 'MCP Test Model Beta']);
    $status = Statuslabel::factory()->create(['name' => 'MCP Test Status Beta']);

    $content = handle([
        'model_name' => 'mcp test model beta',
        'status_name' => 'MCP TEST STATUS BETA',
        'asset_tag' => 'TAG-CASE-001',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('assets', [
        'model_id' => $model->id,
        'status_id' => $status->id,
        'asset_tag' => 'TAG-CASE-001',
    ]);
});

test('id wins when both id and name are passed', function () {
    $modelFromId = AssetModel::factory()->create(['name' => 'From ID']);
    AssetModel::factory()->create(['name' => 'From Name']);
    $status = Statuslabel::factory()->create();

    $content = handle([
        'model_id' => $modelFromId->id,
        'model_name' => 'From Name',
        'status_id' => $status->id,
        'asset_tag' => 'TAG-BOTH-001',
    ])->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('assets', ['model_id' => $modelFromId->id, 'asset_tag' => 'TAG-BOTH-001']);
});

test('returns error when name matches multiple rows', function () {
    config(['app.debug' => false]);
    AssetModel::factory()->count(2)->create(['name' => 'MacBook Pro']);
    $status = Statuslabel::factory()->create();

    $response = SnipeMCPServer::tool(CreateAssetTool::class, [
        'model_name' => 'MacBook Pro',
        'status_id' => $status->id,
        'asset_tag' => 'TAG-AMBIG-001',
    ]);

    $response->assertHasErrors([
        trans('mcp.fk_ambiguous', [
            'field' => 'model_name',
            'value' => 'MacBook Pro',
            'id_field' => 'model_id',
        ]),
    ]);
    $this->assertDatabaseMissing('assets', ['asset_tag' => 'TAG-AMBIG-001']);
});

test('returns error when name does not match any row', function () {
    config(['app.debug' => false]);
    $status = Statuslabel::factory()->create();

    $response = SnipeMCPServer::tool(CreateAssetTool::class, [
        'model_name' => 'Nonexistent Model Name',
        'status_id' => $status->id,
        'asset_tag' => 'TAG-NOMATCH-001',
    ]);

    $response->assertHasErrors([
        trans('mcp.fk_not_found', [
            'field' => 'model_name',
            'value' => 'Nonexistent Model Name',
        ]),
    ]);
    $this->assertDatabaseMissing('assets', ['asset_tag' => 'TAG-NOMATCH-001']);
});

test('returns not found for a location name in another company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $location = Location::factory()->create([
        'name' => 'Other Company Office',
        'company_id' => $otherCompany->id,
    ]);
    $model = AssetModel::factory()->create();
    $status = Statuslabel::factory()->create();
    $this->actingAs(User::factory()->forCompany($company)->createAssets()->create());
    $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

    $response = SnipeMCPServer::tool(CreateAssetTool::class, [
        'model_id' => $model->id,
        'status_id' => $status->id,
        'location_name' => $location->name,
        'asset_tag' => 'TAG-CROSS-COMPANY',
    ]);

    $response->assertHasErrors([
        trans('mcp.fk_not_found', [
            'field' => 'location_name',
            'value' => $location->name,
        ]),
    ]);
    $this->assertDatabaseMissing('assets', ['asset_tag' => 'TAG-CROSS-COMPANY']);
});
