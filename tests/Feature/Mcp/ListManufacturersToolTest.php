<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ListManufacturersTool;
use App\Models\Manufacturer;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ListManufacturersToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewManufacturers()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new ListManufacturersTool)->handle(new Request($args));
    }

    public function test_returns_manufacturers_with_pagination()
    {
        Manufacturer::factory()->count(3)->create();

        $content = $this->handle()->getStructuredContent();

        $this->assertArrayHasKey('manufacturers', $content);
        $this->assertGreaterThanOrEqual(3, $content['total']);
    }

    public function test_each_manufacturer_has_key_fields()
    {
        Manufacturer::factory()->create(['name' => 'Key Fields Manufacturer']);

        $content = $this->handle(['search' => 'Key Fields Manufacturer'])->getStructuredContent();

        $this->assertNotEmpty($content['manufacturers']);
        $manufacturer = $content['manufacturers'][0];
        $this->assertArrayHasKey('id', $manufacturer);
        $this->assertArrayHasKey('name', $manufacturer);
    }

    public function test_limit_controls_results()
    {
        Manufacturer::factory()->count(5)->create();

        $content = $this->handle(['limit' => 2])->getStructuredContent();

        $this->assertCount(2, $content['manufacturers']);
        $this->assertEquals(2, $content['limit']);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle()->responses()->first()->isError());
    }
}
