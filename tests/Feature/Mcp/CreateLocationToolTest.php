<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CreateLocationTool;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CreateLocationToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->createLocations()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new CreateLocationTool)->handle(new Request($args));
    }

    public function test_creates_location_with_required_fields()
    {
        $content = $this->handle(['name' => 'Test Location'])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('locations', ['name' => 'Test Location']);
    }

    public function test_response_includes_id_and_name()
    {
        $content = $this->handle(['name' => 'Location With ID'])->getStructuredContent();

        $this->assertArrayHasKey('id', $content);
        $this->assertEquals('Location With ID', $content['name']);
    }

    public function test_creates_child_location()
    {
        $parent = Location::factory()->create();

        $content = $this->handle([
            'name' => 'Child Location',
            'parent_id' => $parent->id,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('locations', [
            'name' => 'Child Location',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_returns_error_when_name_missing()
    {
        $this->assertTrue($this->handle([])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle(['name' => 'Blocked Location'])->responses()->first()->isError());
        $this->assertDatabaseMissing('locations', ['name' => 'Blocked Location']);
    }
}
