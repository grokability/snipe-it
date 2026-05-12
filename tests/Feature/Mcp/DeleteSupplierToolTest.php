<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\DeleteSupplierTool;
use App\Models\Supplier;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class DeleteSupplierToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->deleteSuppliers()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new DeleteSupplierTool)->handle(new Request($args));
    }

    public function test_deletes_supplier_by_id()
    {
        $supplier = Supplier::factory()->create();

        $content = $this->handle(['id' => $supplier->id])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
    }

    public function test_deletes_supplier_by_name()
    {
        $supplier = Supplier::factory()->create(['name' => 'Delete By Name Supplier']);

        $content = $this->handle(['name' => 'Delete By Name Supplier'])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $supplier = Supplier::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle(['id' => $supplier->id])->responses()->first()->isError());
        $this->assertNotSoftDeleted('suppliers', ['id' => $supplier->id]);
    }
}
