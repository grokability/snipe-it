<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\UpdateComponentTool;
use App\Models\Category;
use App\Models\Component;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class UpdateComponentToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new UpdateComponentTool)->handle(new Request($args));
    }

    public function test_updates_component_by_id()
    {
        $component = Component::factory()->create(['qty' => 5]);

        $content = $this->handle([
            'id' => $component->id,
            'qty' => 15,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('components', ['id' => $component->id, 'qty' => 15]);
    }

    public function test_updates_component_by_name()
    {
        $component = Component::factory()->create(['name' => 'Lookup By Name', 'qty' => 3]);

        $content = $this->handle([
            'name' => 'Lookup By Name',
            'qty' => 9,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('components', ['id' => $component->id, 'qty' => 9]);
    }

    public function test_renames_component_via_new_name()
    {
        $component = Component::factory()->create(['name' => 'Old Component Name']);

        $content = $this->handle([
            'id' => $component->id,
            'new_name' => 'New Component Name',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertEquals('New Component Name', $content['name']);
        $this->assertDatabaseHas('components', ['id' => $component->id, 'name' => 'New Component Name']);
    }

    public function test_updates_multiple_fields_at_once()
    {
        $component = Component::factory()->create();
        $location = Location::factory()->create();
        $category = Category::factory()->forComponents()->create();

        $this->handle([
            'id' => $component->id,
            'qty' => 25,
            'location_id' => $location->id,
            'category_id' => $category->id,
            'order_number' => 'ORD-COMP-999',
            'notes' => 'Updated component note',
        ]);

        $this->assertDatabaseHas('components', [
            'id' => $component->id,
            'qty' => 25,
            'location_id' => $location->id,
            'category_id' => $category->id,
            'order_number' => 'ORD-COMP-999',
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
        $component = Component::factory()->create(['name' => 'Response Component']);

        $content = $this->handle([
            'id' => $component->id,
            'qty' => 8,
        ])->getStructuredContent();

        $this->assertEquals($component->id, $content['id']);
        $this->assertEquals('Response Component', $content['name']);
    }
}
