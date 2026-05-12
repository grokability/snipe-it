<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CreateManufacturerTool;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CreateManufacturerToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->createManufacturers()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new CreateManufacturerTool)->handle(new Request($args));
    }

    public function test_creates_manufacturer_with_required_fields()
    {
        $content = $this->handle(['name' => 'Test Manufacturer'])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('manufacturers', ['name' => 'Test Manufacturer']);
    }

    public function test_response_includes_id_and_name()
    {
        $content = $this->handle(['name' => 'Response Manufacturer'])->getStructuredContent();

        $this->assertArrayHasKey('id', $content);
        $this->assertEquals('Response Manufacturer', $content['name']);
    }

    public function test_returns_error_when_name_missing()
    {
        $this->assertTrue($this->handle([])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle(['name' => 'Blocked Manufacturer'])->responses()->first()->isError());
        $this->assertDatabaseMissing('manufacturers', ['name' => 'Blocked Manufacturer']);
    }
}
