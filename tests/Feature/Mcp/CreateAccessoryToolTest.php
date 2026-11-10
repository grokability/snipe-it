<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CreateAccessoryTool;
use App\Models\Category;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CreateAccessoryToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new CreateAccessoryTool)->handle(new Request($args));
    }

    public function test_creates_accessory_with_required_fields()
    {
        $category = Category::factory()->forAccessories()->create();

        $content = $this->handle([
            'name' => 'Test Keyboard',
            'category_id' => $category->id,
            'qty' => 5,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('accessories', ['name' => 'Test Keyboard', 'qty' => 5]);
    }

    public function test_response_includes_id_name_and_qty()
    {
        $category = Category::factory()->forAccessories()->create();

        $content = $this->handle([
            'name' => 'Response Check Accessory',
            'category_id' => $category->id,
            'qty' => 3,
        ])->getStructuredContent();

        $this->assertArrayHasKey('id', $content);
        $this->assertEquals('Response Check Accessory', $content['name']);
        $this->assertEquals(3, $content['qty']);
    }

    public function test_creates_accessory_with_optional_fields()
    {
        $category = Category::factory()->forAccessories()->create();
        $location = Location::factory()->create();

        $this->handle([
            'name' => 'Full Accessory',
            'category_id' => $category->id,
            'qty' => 10,
            'model_number' => 'MN-1234',
            'location_id' => $location->id,
            'order_number' => 'ORD-001',
            'purchase_cost' => 29.99,
            'purchase_date' => '2024-01-15',
            'min_amt' => 2,
            'notes' => 'Test notes',
        ]);

        $this->assertDatabaseHas('accessories', [
            'name' => 'Full Accessory',
            'model_number' => 'MN-1234',
            'location_id' => $location->id,
            'order_number' => 'ORD-001',
            'min_amt' => 2,
        ]);
    }

    public function test_returns_error_when_name_missing()
    {
        $category = Category::factory()->forAccessories()->create();

        $this->assertTrue($this->handle([
            'category_id' => $category->id,
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_category_missing()
    {
        $this->assertTrue($this->handle([
            'name' => 'No Category',
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_category_does_not_exist()
    {
        $this->assertTrue($this->handle([
            'name' => 'Bad Category',
            'category_id' => 999999,
        ])->responses()->first()->isError());
    }
}
