<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\UpdateCategoryTool;
use App\Models\Category;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class UpdateCategoryToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->editCategories()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new UpdateCategoryTool)->handle(new Request($args));
    }

    public function test_updates_category_by_id()
    {
        $category = Category::factory()->create(['name' => 'Original Cat Name']);

        $content = $this->handle([
            'id' => $category->id,
            'new_name' => 'Updated Cat Name',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Updated Cat Name']);
    }

    public function test_renames_via_new_name()
    {
        $category = Category::factory()->create(['name' => 'Before Cat Rename']);

        $content = $this->handle([
            'id' => $category->id,
            'new_name' => 'After Cat Rename',
        ])->getStructuredContent();

        $this->assertEquals('After Cat Rename', $content['name']);
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'After Cat Rename']);
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999, 'new_name' => 'Ghost Cat'])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $category = Category::factory()->create(['name' => 'Unchanged Cat']);
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle([
            'id' => $category->id,
            'new_name' => 'Should Not Change',
        ])->responses()->first()->isError());

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Unchanged Cat']);
    }
}
