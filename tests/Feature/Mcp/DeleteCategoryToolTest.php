<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\DeleteCategoryTool;
use App\Models\Category;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class DeleteCategoryToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->deleteCategories()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new DeleteCategoryTool)->handle(new Request($args));
    }

    public function test_deletes_category_by_id()
    {
        $category = Category::factory()->create();

        $content = $this->handle(['id' => $category->id])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_deletes_category_by_name()
    {
        $category = Category::factory()->create(['name' => 'Delete By Name Cat']);

        $content = $this->handle(['name' => 'Delete By Name Cat'])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $category = Category::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle(['id' => $category->id])->responses()->first()->isError());
        $this->assertNotSoftDeleted('categories', ['id' => $category->id]);
    }
}
