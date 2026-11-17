<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\UpdateProfileTool;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class UpdateProfileToolTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    private function handle(array $args): ResponseFactory
    {
        return (new UpdateProfileTool)->handle(new Request($args));
    }

    public function test_updates_profile_fields()
    {
        $content = $this->handle([
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'phone' => '555-1234',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'phone' => '555-1234',
        ]);
    }

    public function test_updates_locale()
    {
        $content = $this->handle(['locale' => 'fr'])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('users', ['id' => $this->user->id, 'locale' => 'fr']);
    }

    public function test_updates_location_with_permission()
    {
        $this->actingAs(User::factory()->canEditOwnLocation()->create());
        $location = Location::factory()->create();

        $content = $this->handle(['location_id' => $location->id])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertEquals($location->id, $content['location_id']);
    }

    public function test_does_not_update_location_without_permission()
    {
        $location = Location::factory()->create();

        $originalLocation = $this->user->location_id;

        $this->handle(['location_id' => $location->id]);

        $this->assertDatabaseHas('users', ['id' => $this->user->id, 'location_id' => $originalLocation]);
    }

    public function test_only_updates_provided_fields()
    {
        $originalLastName = $this->user->last_name;

        $this->handle(['first_name' => 'Changed']);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'first_name' => 'Changed',
            'last_name' => $originalLastName,
        ]);
    }
}
