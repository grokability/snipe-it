<?php

use App\Models\Category;
use App\Models\User;

test('viewing category index requires permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.categories.index'))
        ->assertForbidden();
});

test('category index returns expected search results', function () {
    Category::factory()->count(10)->create();
    Category::factory()->count(1)->forAssets()->create(['name' => 'My Test Category']);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(
            route('api.categories.index', [
                'search' => 'My Test Category',
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
        ->assertJson([
            'total' => 1,
        ]);
});

test('category index returns expected categories', function () {
    $this->markTestIncomplete('Not sure why the category factory is generating one more than expected here.');
    Category::factory()->count(3)->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(
            route('api.categories.index', [
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
        ->assertJson([
            'total' => 3,
        ]);
});

test('category index can sort by has eula ascending', function () {
    $searchPrefix = 'has-eula-asc-'.uniqid();

    $withoutEula = Category::factory()->forAssets()->create([
        'name' => $searchPrefix.'-without',
        'eula_text' => '',
    ]);

    $withEula = Category::factory()->forAssets()->create([
        'name' => $searchPrefix.'-with',
        'eula_text' => 'This category has a EULA.',
    ]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(
            route('api.categories.index', [
                'search' => $searchPrefix,
                'sort' => 'has_eula',
                'order' => 'asc',
                'offset' => '0',
                'limit' => '20',
            ]))
        ->assertOk()
        ->assertJson([
            'total' => 2,
        ])
        ->assertJsonPath('rows.0.id', $withoutEula->id)
        ->assertJsonPath('rows.1.id', $withEula->id);
});

test('category index can sort by has eula descending', function () {
    $searchPrefix = 'has-eula-desc-'.uniqid();

    $withoutEula = Category::factory()->forAssets()->create([
        'name' => $searchPrefix.'-without',
        'eula_text' => '',
    ]);

    $withEula = Category::factory()->forAssets()->create([
        'name' => $searchPrefix.'-with',
        'eula_text' => 'This category has a EULA.',
    ]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(
            route('api.categories.index', [
                'search' => $searchPrefix,
                'sort' => 'has_eula',
                'order' => 'desc',
                'offset' => '0',
                'limit' => '20',
            ]))
        ->assertOk()
        ->assertJson([
            'total' => 2,
        ])
        ->assertJsonPath('rows.0.id', $withEula->id)
        ->assertJsonPath('rows.1.id', $withoutEula->id);
});
