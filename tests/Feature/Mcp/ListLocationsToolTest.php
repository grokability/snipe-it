<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ListLocationsTool;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ListLocationsToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewLocations()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new ListLocationsTool)->handle(new Request($args));
    }

    public function test_returns_locations_with_pagination()
    {
        Location::factory()->count(3)->create();

        $content = $this->handle()->getStructuredContent();

        $this->assertArrayHasKey('total', $content);
        $this->assertArrayHasKey('locations', $content);
        $this->assertGreaterThanOrEqual(3, $content['total']);
    }

    public function test_each_location_has_key_fields()
    {
        Location::factory()->create();

        $content = $this->handle()->getStructuredContent();

        $this->assertNotEmpty($content['locations']);
        $first = $content['locations'][0];
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('name', $first);
    }

    public function test_filters_by_parent_id()
    {
        $parent = Location::factory()->create();
        $child = Location::factory()->create(['parent_id' => $parent->id]);
        Location::factory()->create(); // another location without parent

        $content = $this->handle(['parent_id' => $parent->id])->getStructuredContent();

        $ids = array_column($content['locations'], 'id');
        $this->assertContains($child->id, $ids);
        $this->assertNotContains($parent->id, $ids);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle()->responses()->first()->isError());
    }
}
