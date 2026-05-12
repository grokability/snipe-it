<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CreateCategoryTool;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CreateCategoryToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->createCategories()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new CreateCategoryTool)->handle(new Request($args));
    }

    public function test_creates_asset_category()
    {
        $content = $this->handle([
            'name' => 'Test Asset Category',
            'category_type' => 'asset',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('categories', ['name' => 'Test Asset Category', 'category_type' => 'asset']);
    }

    public function test_creates_license_category()
    {
        $content = $this->handle([
            'name' => 'Test License Category',
            'category_type' => 'license',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('categories', ['name' => 'Test License Category', 'category_type' => 'license']);
    }

    public function test_response_includes_id_name_and_type()
    {
        $content = $this->handle([
            'name' => 'Response Category',
            'category_type' => 'accessory',
        ])->getStructuredContent();

        $this->assertArrayHasKey('id', $content);
        $this->assertEquals('Response Category', $content['name']);
        $this->assertEquals('accessory', $content['category_type']);
    }

    public function test_returns_error_when_name_missing()
    {
        $this->assertTrue($this->handle(['category_type' => 'asset'])->responses()->first()->isError());
    }

    public function test_returns_error_when_type_missing()
    {
        $this->assertTrue($this->handle(['name' => 'No Type Category'])->responses()->first()->isError());
    }

    public function test_returns_error_when_invalid_type()
    {
        $this->assertTrue($this->handle([
            'name' => 'Invalid Type Category',
            'category_type' => 'invalid',
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle([
            'name' => 'Blocked Category',
            'category_type' => 'asset',
        ])->responses()->first()->isError());

        $this->assertDatabaseMissing('categories', ['name' => 'Blocked Category']);
    }
}
