<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\UpdateSupplierTool;
use App\Models\Supplier;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class UpdateSupplierToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->editSuppliers()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new UpdateSupplierTool)->handle(new Request($args));
    }

    public function test_updates_supplier_by_id()
    {
        $supplier = Supplier::factory()->create(['name' => 'Original Supplier']);

        $content = $this->handle([
            'id' => $supplier->id,
            'new_name' => 'Updated Supplier',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'name' => 'Updated Supplier']);
    }

    public function test_renames_via_new_name()
    {
        $supplier = Supplier::factory()->create(['name' => 'Old Supplier Name']);

        $content = $this->handle([
            'name' => 'Old Supplier Name',
            'new_name' => 'New Supplier Name',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertEquals('New Supplier Name', $content['name']);
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999, 'new_name' => 'Ghost'])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $supplier = Supplier::factory()->create(['name' => 'Unchanged Supplier']);
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle([
            'id' => $supplier->id,
            'new_name' => 'Should Not Change',
        ])->responses()->first()->isError());

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'name' => 'Unchanged Supplier']);
    }
}
