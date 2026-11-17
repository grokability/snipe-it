<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\DeleteManufacturerTool;
use App\Models\Manufacturer;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class DeleteManufacturerToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->deleteManufacturers()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new DeleteManufacturerTool)->handle(new Request($args));
    }

    public function test_deletes_manufacturer_by_id()
    {
        $manufacturer = Manufacturer::factory()->create();

        $content = $this->handle(['id' => $manufacturer->id])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('manufacturers', ['id' => $manufacturer->id]);
    }

    public function test_deletes_manufacturer_by_name()
    {
        $manufacturer = Manufacturer::factory()->create(['name' => 'Delete By Name Manufacturer']);

        $content = $this->handle(['name' => 'Delete By Name Manufacturer'])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('manufacturers', ['id' => $manufacturer->id]);
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
        $this->assertNotSoftDeleted('manufacturers', ['id' => $manufacturer->id]);
    }
}
