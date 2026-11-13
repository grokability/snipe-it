<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ListSuppliersTool;
use App\Models\Supplier;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ListSuppliersToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewSuppliers()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new ListSuppliersTool)->handle(new Request($args));
    }

    public function test_returns_suppliers_with_pagination()
    {
        Supplier::factory()->count(3)->create();

        $content = $this->handle()->getStructuredContent();

        $this->assertArrayHasKey('suppliers', $content);
        $this->assertGreaterThanOrEqual(3, $content['total']);
    }

    public function test_each_supplier_has_key_fields()
    {
        Supplier::factory()->create(['name' => 'Key Fields Supplier']);

        $content = $this->handle(['search' => 'Key Fields Supplier'])->getStructuredContent();

        $this->assertNotEmpty($content['suppliers']);
        $supplier = $content['suppliers'][0];
        $this->assertArrayHasKey('id', $supplier);
        $this->assertArrayHasKey('name', $supplier);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle()->responses()->first()->isError());
    }
}
