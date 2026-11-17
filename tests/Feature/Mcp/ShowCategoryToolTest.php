<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ShowCategoryTool;
use App\Models\Category;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ShowCategoryToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewCategories()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new ShowCategoryTool)->handle(new Request($args));
    }

    public function test_returns_category_by_id()
    {
        $category = Category::factory()->create(['name' => 'Lookup By ID Cat']);

        $content = $this->handle(['id' => $category->id])->getStructuredContent();

        $this->assertEquals($category->id, $content['id']);
        $this->assertEquals('Lookup By ID Cat', $content['name']);
    }

    public function test_returns_category_by_name()
    {
        $category = Category::factory()->create(['name' => 'Lookup By Name Cat']);

        $content = $this->handle(['name' => 'Lookup By Name Cat'])->getStructuredContent();

        $this->assertEquals($category->id, $content['id']);
        $this->assertEquals('Lookup By Name Cat', $content['name']);
    }

    public function test_returns_error_when_no_identifier_provided()
    {
        $this->assertTrue($this->handle()->responses()->first()->isError());
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
    }
}
