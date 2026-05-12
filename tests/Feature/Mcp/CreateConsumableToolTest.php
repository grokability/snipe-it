<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CreateConsumableTool;
use App\Models\Category;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CreateConsumableToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->createConsumables()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new CreateConsumableTool)->handle(new Request($args));
    }

    public function test_creates_consumable_with_required_fields()
    {
        $category = Category::factory()->create(['category_type' => 'consumable']);

        $content = $this->handle([
            'name' => 'Test Toner Cartridge',
            'qty' => 10,
            'category_id' => $category->id,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('consumables', [
            'name' => 'Test Toner Cartridge',
            'qty' => 10,
            'category_id' => $category->id,
        ]);
    }

    public function test_response_includes_id_name_and_qty()
    {
        $category = Category::factory()->create(['category_type' => 'consumable']);

        $content = $this->handle([
            'name' => 'Response Check Consumable',
            'qty' => 5,
            'category_id' => $category->id,
        ])->getStructuredContent();

        $this->assertArrayHasKey('id', $content);
        $this->assertEquals('Response Check Consumable', $content['name']);
        $this->assertEquals(5, $content['qty']);
    }

    public function test_creates_with_optional_fields()
    {
        $category = Category::factory()->create(['category_type' => 'consumable']);
        $location = Location::factory()->create();

        $this->handle([
            'name' => 'Full Consumable',
            'qty' => 20,
            'category_id' => $category->id,
            'location_id' => $location->id,
            'order_number' => 'ORD-CONS-001',
            'purchase_cost' => 9.99,
            'purchase_date' => '2024-06-01',
            'min_amt' => 5,
            'notes' => 'Test consumable notes',
        ]);

        $this->assertDatabaseHas('consumables', [
            'name' => 'Full Consumable',
            'qty' => 20,
            'location_id' => $location->id,
            'order_number' => 'ORD-CONS-001',
            'min_amt' => 5,
        ]);
    }

    public function test_returns_error_when_name_missing()
    {
        $category = Category::factory()->create(['category_type' => 'consumable']);

        $this->assertTrue($this->handle([
            'qty' => 5,
            'category_id' => $category->id,
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_qty_missing()
    {
        $category = Category::factory()->create(['category_type' => 'consumable']);

        $this->assertTrue($this->handle([
            'name' => 'No Qty Consumable',
            'category_id' => $category->id,
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_category_missing()
    {
        $this->assertTrue($this->handle([
            'name' => 'No Category Consumable',
            'qty' => 5,
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $category = Category::factory()->create(['category_type' => 'consumable']);

        $this->assertTrue($this->handle([
            'name' => 'Blocked Consumable',
            'qty' => 1,
            'category_id' => $category->id,
        ])->responses()->first()->isError());

        $this->assertDatabaseMissing('consumables', ['name' => 'Blocked Consumable']);
    }
}
