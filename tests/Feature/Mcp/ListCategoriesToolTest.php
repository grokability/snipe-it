<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ListCategoriesTool;
use App\Models\Category;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ListCategoriesToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewCategories()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new ListCategoriesTool)->handle(new Request($args));
    }

    public function test_returns_categories_with_pagination_metadata()
    {
        Category::factory()->count(3)->create();

        $content = $this->handle()->getStructuredContent();

        $this->assertArrayHasKey('total', $content);
        $this->assertArrayHasKey('categories', $content);
        $this->assertArrayHasKey('limit', $content);
        $this->assertArrayHasKey('offset', $content);
        $this->assertGreaterThanOrEqual(3, $content['total']);
    }

    public function test_each_category_includes_key_fields()
    {
        Category::factory()->create();

        $content = $this->handle()->getStructuredContent();

        $this->assertNotEmpty($content['categories']);
        $category = $content['categories'][0];
        $this->assertArrayHasKey('id', $category);
        $this->assertArrayHasKey('name', $category);
        $this->assertArrayHasKey('category_type', $category);
    }

    public function test_filters_by_category_type()
    {
        $assetCategory = Category::factory()->create(['category_type' => 'asset', 'name' => 'Asset Type Cat '.uniqid()]);
        $licenseCategory = Category::factory()->create(['category_type' => 'license', 'name' => 'License Type Cat '.uniqid()]);

        $content = $this->handle(['category_type' => 'asset'])->getStructuredContent();

        $ids = array_column($content['categories'], 'id');
        $this->assertContains($assetCategory->id, $ids);
        $this->assertNotContains($licenseCategory->id, $ids);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle()->responses()->first()->isError());
    }
}
