<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ShowManufacturerTool;
use App\Models\Manufacturer;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ShowManufacturerToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewManufacturers()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new ShowManufacturerTool)->handle(new Request($args));
    }

    public function test_returns_manufacturer_by_id()
    {
        $manufacturer = Manufacturer::factory()->create(['name' => 'Show By ID Manufacturer']);

        $content = $this->handle(['id' => $manufacturer->id])->getStructuredContent();

        $this->assertEquals($manufacturer->id, $content['id']);
        $this->assertEquals('Show By ID Manufacturer', $content['name']);
    }

    public function test_returns_manufacturer_by_name()
    {
        Manufacturer::factory()->create(['name' => 'Show By Name Manufacturer']);

        $content = $this->handle(['name' => 'Show By Name Manufacturer'])->getStructuredContent();

        $this->assertEquals('Show By Name Manufacturer', $content['name']);
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
        $manufacturer = Manufacturer::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle(['id' => $manufacturer->id])->responses()->first()->isError());
    }
}
