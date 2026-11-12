<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CreateSupplierTool;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CreateSupplierToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->createSuppliers()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new CreateSupplierTool)->handle(new Request($args));
    }

    public function test_creates_supplier_with_required_fields()
    {
        $content = $this->handle(['name' => 'Test Supplier'])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('suppliers', ['name' => 'Test Supplier']);
    }

    public function test_response_includes_id_and_name()
    {
        $content = $this->handle(['name' => 'Response Supplier'])->getStructuredContent();

        $this->assertArrayHasKey('id', $content);
        $this->assertEquals('Response Supplier', $content['name']);
    }

    public function test_returns_error_when_name_missing()
    {
        $this->assertTrue($this->handle([])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle(['name' => 'Blocked Supplier'])->responses()->first()->isError());
        $this->assertDatabaseMissing('suppliers', ['name' => 'Blocked Supplier']);
    }
}
