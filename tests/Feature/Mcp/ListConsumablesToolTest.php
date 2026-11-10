<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ListConsumablesTool;
use App\Models\Category;
use App\Models\Consumable;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ListConsumablesToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewConsumables()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new ListConsumablesTool)->handle(new Request($args));
    }

    public function test_returns_total_and_pagination_metadata()
    {
        Consumable::factory()->count(3)->create();

        $content = $this->handle()->getStructuredContent();

        $this->assertArrayHasKey('total', $content);
        $this->assertArrayHasKey('offset', $content);
        $this->assertArrayHasKey('limit', $content);
        $this->assertArrayHasKey('consumables', $content);
        $this->assertGreaterThanOrEqual(3, $content['total']);
    }

    public function test_each_consumable_includes_key_fields()
    {
        Consumable::factory()->create(['name' => 'Test Ink Cartridge']);

        $content = $this->handle(['limit' => 500])->getStructuredContent();

        $consumable = collect($content['consumables'])->firstWhere('name', 'Test Ink Cartridge');

        $this->assertNotNull($consumable);
        $this->assertArrayHasKey('id', $consumable);
        $this->assertArrayHasKey('name', $consumable);
        $this->assertArrayHasKey('qty', $consumable);
        $this->assertArrayHasKey('category', $consumable);
    }

    public function test_limit_controls_results()
    {
        Consumable::factory()->count(10)->create();

        $content = $this->handle(['limit' => 3])->getStructuredContent();

        $this->assertCount(3, $content['consumables']);
        $this->assertEquals(3, $content['limit']);
    }

    public function test_filters_by_category_id()
    {
        $category = Category::factory()->create(['category_type' => 'consumable']);
        Consumable::factory()->create(['name' => 'Category Match', 'category_id' => $category->id]);
        Consumable::factory()->create(['name' => 'Other Consumable']);

        $content = $this->handle(['category_id' => $category->id])->getStructuredContent();

        $this->assertEquals(1, $content['total']);
        $this->assertEquals('Category Match', $content['consumables'][0]['name']);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle()->responses()->first()->isError());
    }
}
