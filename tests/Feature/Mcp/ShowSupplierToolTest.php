<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ShowSupplierTool;
use App\Models\Supplier;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ShowSupplierToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewSuppliers()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new ShowSupplierTool)->handle(new Request($args));
    }

    public function test_returns_supplier_by_id()
    {
        $supplier = Supplier::factory()->create(['name' => 'Show By ID Supplier']);

        $content = $this->handle(['id' => $supplier->id])->getStructuredContent();

        $this->assertEquals($supplier->id, $content['id']);
        $this->assertEquals('Show By ID Supplier', $content['name']);
    }

    public function test_returns_supplier_by_name()
    {
        Supplier::factory()->create(['name' => 'Show By Name Supplier']);

        $content = $this->handle(['name' => 'Show By Name Supplier'])->getStructuredContent();

        $this->assertEquals('Show By Name Supplier', $content['name']);
    }

    public function test_returns_error_when_no_identifier_provided()
    {
        $this->assertTrue($this->handle()->responses()->first()->isError());
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
    }
}
