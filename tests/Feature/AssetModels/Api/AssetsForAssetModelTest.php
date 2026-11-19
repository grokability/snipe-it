<?php

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

test('viewing assets for asset model requires authentication', function () {
    $model = AssetModel::factory()->create();

    $this->getJson(route('api.models.assets', ['id' => $model->id]))
        ->assertUnauthorized();
});

test('viewing assets for asset model requires permission', function () {
    $model = AssetModel::factory()->create();

    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.models.assets', ['id' => $model->id]))
        ->assertForbidden();
});

test('models view without assets view cannot read the assets for a model', function () {
    // Regression test: pre-fix, the endpoint only authorized
    // view on AssetModel. A role holding models.view but not
    // assets.view therefore read full asset rows (serials, notes,
    // assignment, purchase, audit) through AssetsTransformer. The
    // canonical GET /api/v1/hardware correctly 403s the same role.
    $model = AssetModel::factory()->create();
    Asset::factory()->create([
        'model_id' => $model->id,
        'serial' => 'LEAK-SERIAL-ABC123',
        'notes' => 'LEAK-NOTES-sensitive',
    ]);

    $modelsViewer = User::factory()->viewAssetModels()->create();

    $this->actingAsForApi($modelsViewer)
        ->getJson(route('api.models.assets', ['id' => $model->id]))
        ->assertForbidden();
});

test('models view combined with assets view still reaches the endpoint', function () {
    // Happy-path companion: fixing the authorization gap must not
    // over-restrict the roles that legitimately combine both
    // permissions.
    $model = AssetModel::factory()->create();
    Asset::factory()->count(2)->create(['model_id' => $model->id]);

    $viewer = User::factory()->viewAssetModels()->viewAssets()->create();

    $this->actingAsForApi($viewer)
        ->getJson(route('api.models.assets', ['id' => $model->id]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('total', 2)
            ->has('rows', 2)
            ->etc());
});

test('error returned if asset model does not exist', function () {
    // Snipe-IT's exception handler returns 200 + status "error" for
    // ModelNotFoundException on API requests, matching what the
    // sibling AssetModel endpoints already do (see 'error returned if asset model does not exist'
    // in RestoreAssetModelTest).
    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.models.assets', ['id' => 999999]))
        ->assertOk()
        ->assertStatusMessageIs('error');
});

test('returns only assets belonging to the named model', function () {
    $targetModel = AssetModel::factory()->create();
    $otherModel = AssetModel::factory()->create();

    $targetAssets = Asset::factory()->count(3)->create(['model_id' => $targetModel->id]);
    Asset::factory()->count(2)->create(['model_id' => $otherModel->id]);

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.models.assets', ['id' => $targetModel->id]))
        ->assertOk()
        ->assertJsonStructure(['total', 'rows'])
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('total', 3)
            ->has('rows', 3)
            ->etc());

    $returnedIds = collect($response->json('rows'))->pluck('id')->sort()->values()->all();
    $expectedIds = $targetAssets->pluck('id')->sort()->values()->all();
    expect($returnedIds)->toBe($expectedIds);
});

test('returns empty list when model has no assets', function () {
    $model = AssetModel::factory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.models.assets', ['id' => $model->id]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('total', 0)
            ->has('rows', 0)
            ->etc());
});

test('limit query parameter bounds the returned rows', function () {
    $model = AssetModel::factory()->create();
    Asset::factory()->count(5)->create(['model_id' => $model->id]);

    // total should reflect the full matching set. rows should
    // reflect the requested page size. Pagination is what makes
    // this endpoint safe against models with thousands of assets.
    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.models.assets', ['id' => $model->id, 'limit' => 2]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('total', 5)
            ->has('rows', 2)
            ->etc());
});

test('offset query parameter skips earlier rows', function () {
    $model = AssetModel::factory()->create();
    Asset::factory()->count(5)->create(['model_id' => $model->id]);

    $firstPage = $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.models.assets', ['id' => $model->id, 'limit' => 2, 'offset' => 0]))
        ->assertOk()
        ->json('rows');

    $secondPage = $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.models.assets', ['id' => $model->id, 'limit' => 2, 'offset' => 2]))
        ->assertOk()
        ->json('rows');

    expect($firstPage)->toHaveCount(2);
    expect($secondPage)->toHaveCount(2);
    $firstPageIds = collect($firstPage)->pluck('id')->all();
    $secondPageIds = collect($secondPage)->pluck('id')->all();
    expect(array_intersect($firstPageIds, $secondPageIds))->toBeEmpty('Offset pagination must not overlap between pages.');
});
