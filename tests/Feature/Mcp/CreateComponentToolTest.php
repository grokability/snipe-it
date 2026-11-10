<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CreateComponentTool;
use App\Models\Category;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CreateComponentToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->createComponents()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new CreateComponentTool)->handle(new Request($args));
    }

    public function test_creates_component_with_required_fields()
    {
        $category = Category::factory()->forComponents()->create();

        $content = $this->handle([
            'name' => 'Test RAM',
            'category_id' => $category->id,
            'qty' => 10,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('components', ['name' => 'Test RAM', 'qty' => 10]);
    }

    public function test_response_includes_id_name_qty_and_category()
    {
        $category = Category::factory()->forComponents()->create();

        $content = $this->handle([
            'name' => 'Response Check Component',
            'category_id' => $category->id,
            'qty' => 5,
        ])->getStructuredContent();

        $this->assertArrayHasKey('id', $content);
        $this->assertEquals('Response Check Component', $content['name']);
        $this->assertEquals(5, $content['qty']);
        $this->assertEquals($category->id, $content['category_id']);
    }

    public function test_creates_component_with_optional_fields()
    {
        $category = Category::factory()->forComponents()->create();
        $location = Location::factory()->create();

        $this->handle([
            'name' => 'Full Component',
            'category_id' => $category->id,
            'qty' => 20,
            'serial' => 'SN-COMP-001',
            'model_number' => 'MN-5678',
            'location_id' => $location->id,
            'order_number' => 'ORD-COMP-001',
            'purchase_cost' => 49.99,
            'purchase_date' => '2024-03-01',
            'min_amt' => 3,
            'notes' => 'Component notes',
        ]);

        $this->assertDatabaseHas('components', [
            'name' => 'Full Component',
            'serial' => 'SN-COMP-001',
            'model_number' => 'MN-5678',
            'location_id' => $location->id,
            'order_number' => 'ORD-COMP-001',
            'min_amt' => 3,
        ]);
    }

    public function test_returns_error_when_name_missing()
    {
        $category = Category::factory()->forComponents()->create();

        $this->assertTrue($this->handle([
            'category_id' => $category->id,
            'qty' => 5,
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_category_missing()
    {
        $this->assertTrue($this->handle([
            'name' => 'No Category',
            'qty' => 5,
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_qty_missing()
    {
        $category = Category::factory()->forComponents()->create();

        $this->assertTrue($this->handle([
            'name' => 'No Qty',
            'category_id' => $category->id,
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_category_does_not_exist()
    {
        $this->assertTrue($this->handle([
            'name' => 'Bad Category',
            'category_id' => 999999,
            'qty' => 5,
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $category = Category::factory()->create(['category_type' => 'component']);

        $this->assertTrue($this->handle([
            'name' => 'Blocked Component',
            'category_id' => $category->id,
            'qty' => 1,
        ])->responses()->first()->isError());
    }
}
