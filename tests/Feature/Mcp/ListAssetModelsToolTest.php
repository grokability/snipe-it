<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ListAssetModelsTool;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ListAssetModelsToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewAssetModels()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new ListAssetModelsTool)->handle(new Request($args));
    }

    public function test_returns_models_with_pagination()
    {
        AssetModel::factory()->count(3)->create();

        $content = $this->handle()->getStructuredContent();

        $this->assertArrayHasKey('total', $content);
        $this->assertArrayHasKey('models', $content);
        $this->assertGreaterThanOrEqual(3, $content['total']);
    }

    public function test_each_model_has_key_fields()
    {
        AssetModel::factory()->create();

        $content = $this->handle()->getStructuredContent();

        $this->assertNotEmpty($content['models']);
        $first = $content['models'][0];
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('name', $first);
        $this->assertArrayHasKey('category', $first);
    }

    public function test_filters_by_category_id()
    {
        $category = Category::factory()->create(['category_type' => 'asset']);
        $model = AssetModel::factory()->create(['category_id' => $category->id]);
        AssetModel::factory()->create(); // another model with different category

        $content = $this->handle(['category_id' => $category->id])->getStructuredContent();

        $ids = array_column($content['models'], 'id');
        $this->assertContains($model->id, $ids);
        foreach ($ids as $id) {
            $this->assertEquals($category->id, collect($content['models'])->firstWhere('id', $id)['category_id']);
        }
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle()->responses()->first()->isError());
    }
}
