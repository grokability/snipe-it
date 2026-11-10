<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\UpdateLocationTool;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class UpdateLocationToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->editLocations()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new UpdateLocationTool)->handle(new Request($args));
    }

    public function test_updates_location_by_id()
    {
        $location = Location::factory()->create();

        $content = $this->handle([
            'id' => $location->id,
            'city' => 'New City',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'city' => 'New City',
        ]);
    }

    public function test_renames_via_new_name()
    {
        $location = Location::factory()->create();

        $content = $this->handle([
            'id' => $location->id,
            'new_name' => 'Renamed Location',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertEquals('Renamed Location', $content['name']);
        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'name' => 'Renamed Location',
        ]);
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 99999])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $location = Location::factory()->create();

        $this->assertTrue($this->handle(['id' => $location->id, 'city' => 'Hacked'])->responses()->first()->isError());
    }
}
