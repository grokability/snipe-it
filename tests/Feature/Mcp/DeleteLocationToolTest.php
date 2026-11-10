<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\DeleteLocationTool;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class DeleteLocationToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->deleteLocations()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new DeleteLocationTool)->handle(new Request($args));
    }

    public function test_deletes_location_by_id()
    {
        $location = Location::factory()->create();

        $content = $this->handle(['id' => $location->id])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('locations', ['id' => $location->id]);
    }

    public function test_deletes_location_by_name()
    {
        $location = Location::factory()->create();

        $content = $this->handle(['name' => $location->name])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('locations', ['id' => $location->id]);
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 99999])->responses()->first()->isError());
    }

    public function test_returns_error_when_location_has_users()
    {
        $location = Location::factory()->create();
        User::factory()->create(['location_id' => $location->id]);

        $response = $this->handle(['id' => $location->id]);

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertNotSoftDeleted('locations', ['id' => $location->id]);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $location = Location::factory()->create();

        $this->assertTrue($this->handle(['id' => $location->id])->responses()->first()->isError());
        $this->assertNotSoftDeleted('locations', ['id' => $location->id]);
    }
}
