<?php

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

test('viewing asset model index requires authentication', function () {
    $this->getJson(route('api.models.index'))->assertRedirect();
});

test('viewing asset model index requires permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.models.index'))
        ->assertForbidden();
});

test('asset model index returns expected asset models', function () {
    AssetModel::factory()->count(3)->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(
            route('api.models.index', [
                'sort' => 'name',
                'order' => 'asc',
                'offset' => '0',
                'limit' => '20',
            ]))
        ->assertOk()
        ->assertJsonStructure([
            'total',
            'rows',
        ])
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
});

test('asset model index search returns expected asset models', function () {
    AssetModel::factory()->count(3)->create();
    AssetModel::factory()->count(1)->create(['name' => 'Test Model']);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(
            route('api.models.index', [
                'search' => 'Test Model',
                'sort' => 'id',
                'order' => 'asc',
                'offset' => '0',
                'limit' => '20',
            ]))
        ->assertOk()
        ->assertJsonStructure([
            'total',
            'rows',
        ])
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('asset model index filter can search computed count aliases', function () {
    $this->markIncompleteIfSqlite('This test is not compatible with SQLite');
    $targetModel = AssetModel::factory()->create(['name' => 'Two Assets Model']);
    $otherModel = AssetModel::factory()->create(['name' => 'One Asset Model']);

    Asset::factory()->count(2)->create(['model_id' => $targetModel->id]);
    Asset::factory()->create(['model_id' => $otherModel->id]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.models.index', [
            'filter' => '{"assets_count":"2"}',
            'sort' => 'id',
            'order' => 'asc',
            'offset' => '0',
            'limit' => '20',
        ]))
        ->assertOk()
        ->assertJsonStructure([
            'total',
            'rows',
        ])
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc())
        ->assertJsonFragment(['name' => 'Two Assets Model']);
});
