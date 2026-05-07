<?php

namespace Tests\Feature\Categories\Api;

use App\Models\Category;
use App\Models\User;
use Tests\TestCase;

class IndexCategoriesTest extends TestCase
{
    public function test_viewing_category_index_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.categories.index'))
            ->assertForbidden();
    }

    public function test_category_index_returns_expected_search_results()
    {
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

    }

    public function test_category_index_returns_expected_categories()
    {
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

    }

    public function test_category_index_can_sort_by_has_eula_ascending()
    {
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
    }

    public function test_category_index_can_sort_by_has_eula_descending()
    {
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
    }
}
