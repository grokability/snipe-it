<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ShowLocationTool;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ShowLocationToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewLocations()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new ShowLocationTool)->handle(new Request($args));
    }

    public function test_returns_location_by_id()
    {
        $location = Location::factory()->create();

        $content = $this->handle(['id' => $location->id])->getStructuredContent();

        $this->assertEquals($location->id, $content['id']);
        $this->assertEquals($location->name, $content['name']);
    }

    public function test_returns_location_by_name()
    {
        $location = Location::factory()->create();

        $content = $this->handle(['name' => $location->name])->getStructuredContent();

        $this->assertEquals($location->id, $content['id']);
        $this->assertEquals($location->name, $content['name']);
    }

    public function test_returns_error_when_no_identifier_provided()
    {
        $this->assertTrue($this->handle()->responses()->first()->isError());
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 99999])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $location = Location::factory()->create();

        $this->assertTrue($this->handle(['id' => $location->id])->responses()->first()->isError());
    }
}
