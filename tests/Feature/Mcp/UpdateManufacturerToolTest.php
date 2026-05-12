<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\UpdateManufacturerTool;
use App\Models\Manufacturer;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class UpdateManufacturerToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->editManufacturers()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new UpdateManufacturerTool)->handle(new Request($args));
    }

    public function test_updates_manufacturer_by_id()
    {
        $manufacturer = Manufacturer::factory()->create(['name' => 'Original Manufacturer']);

        $content = $this->handle([
            'id' => $manufacturer->id,
            'new_name' => 'Updated Manufacturer',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('manufacturers', ['id' => $manufacturer->id, 'name' => 'Updated Manufacturer']);
    }

    public function test_renames_via_new_name()
    {
        $manufacturer = Manufacturer::factory()->create(['name' => 'Old Manufacturer Name']);

        $content = $this->handle([
            'name' => 'Old Manufacturer Name',
            'new_name' => 'New Manufacturer Name',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertEquals('New Manufacturer Name', $content['name']);
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999, 'new_name' => 'Ghost'])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $manufacturer = Manufacturer::factory()->create(['name' => 'Unchanged Manufacturer']);
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle([
            'id' => $manufacturer->id,
            'new_name' => 'Should Not Change',
        ])->responses()->first()->isError());

        $this->assertDatabaseHas('manufacturers', ['id' => $manufacturer->id, 'name' => 'Unchanged Manufacturer']);
    }
}
