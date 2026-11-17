<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CreateAssetModelTool;
use App\Models\Category;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CreateAssetModelToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->createAssetModels()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new CreateAssetModelTool)->handle(new Request($args));
    }

    public function test_creates_model_with_required_fields()
    {
        $category = Category::factory()->create(['category_type' => 'asset']);

        $content = $this->handle([
            'name' => 'Test Model',
            'category_id' => $category->id,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('models', [
            'name' => 'Test Model',
            'category_id' => $category->id,
        ]);
    }

    public function test_response_includes_id_name_and_category()
    {
        $category = Category::factory()->create(['category_type' => 'asset']);

        $content = $this->handle([
            'name' => 'Response Model',
            'category_id' => $category->id,
        ])->getStructuredContent();

        $this->assertArrayHasKey('id', $content);
        $this->assertEquals('Response Model', $content['name']);
        $this->assertEquals($category->id, $content['category_id']);
    }

    public function test_returns_error_when_name_missing()
    {
        $category = Category::factory()->create(['category_type' => 'asset']);

        $this->assertTrue($this->handle(['category_id' => $category->id])->responses()->first()->isError());
    }

    public function test_returns_error_when_category_missing()
    {
        $this->assertTrue($this->handle(['name' => 'No Category Model'])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $category = Category::factory()->create(['category_type' => 'asset']);

        $this->assertTrue($this->handle([
            'name' => 'Blocked Model',
            'category_id' => $category->id,
        ])->responses()->first()->isError());

        $this->assertDatabaseMissing('models', ['name' => 'Blocked Model']);
    }
}
