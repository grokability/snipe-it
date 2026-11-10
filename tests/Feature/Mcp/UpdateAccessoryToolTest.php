<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\UpdateAccessoryTool;
use App\Models\Accessory;
use App\Models\Category;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class UpdateAccessoryToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new UpdateAccessoryTool)->handle(new Request($args));
    }

    public function test_updates_accessory_by_id()
    {
        $accessory = Accessory::factory()->create(['qty' => 5]);

        $content = $this->handle([
            'id' => $accessory->id,
            'qty' => 10,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('accessories', ['id' => $accessory->id, 'qty' => 10]);
    }

    public function test_updates_accessory_by_name()
    {
        $accessory = Accessory::factory()->create(['name' => 'Lookup By Name', 'qty' => 3]);

        $content = $this->handle([
            'name' => 'Lookup By Name',
            'qty' => 8,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('accessories', ['id' => $accessory->id, 'qty' => 8]);
    }

    public function test_renames_accessory_via_new_name()
    {
        $accessory = Accessory::factory()->create(['name' => 'Old Accessory Name']);

        $content = $this->handle([
            'id' => $accessory->id,
            'new_name' => 'New Accessory Name',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertEquals('New Accessory Name', $content['name']);
        $this->assertDatabaseHas('accessories', ['id' => $accessory->id, 'name' => 'New Accessory Name']);
    }

    public function test_updates_multiple_fields_at_once()
    {
        $accessory = Accessory::factory()->create();
        $location = Location::factory()->create();
        $category = Category::factory()->forAccessories()->create();

        $this->handle([
            'id' => $accessory->id,
            'qty' => 20,
            'location_id' => $location->id,
            'category_id' => $category->id,
            'order_number' => 'ORD-999',
            'notes' => 'Updated note',
        ]);

        $this->assertDatabaseHas('accessories', [
            'id' => $accessory->id,
            'qty' => 20,
            'location_id' => $location->id,
            'category_id' => $category->id,
            'order_number' => 'ORD-999',
        ]);
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle([
            'id' => 999999,
            'qty' => 5,
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_no_identifier_provided()
    {
        $this->assertTrue($this->handle([
            'qty' => 5,
        ])->responses()->first()->isError());
    }

    public function test_response_includes_id_and_name()
    {
        $accessory = Accessory::factory()->create(['name' => 'Response Accessory']);

        $content = $this->handle([
            'id' => $accessory->id,
            'qty' => 7,
        ])->getStructuredContent();

        $this->assertEquals($accessory->id, $content['id']);
        $this->assertEquals('Response Accessory', $content['name']);
    }
}
