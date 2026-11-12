<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CreateDepartmentTool;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CreateDepartmentToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->createDepartments()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new CreateDepartmentTool)->handle(new Request($args));
    }

    public function test_creates_department_with_required_fields()
    {
        $content = $this->handle(['name' => 'Test Department'])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('departments', ['name' => 'Test Department']);
    }

    public function test_response_includes_id_and_name()
    {
        $content = $this->handle(['name' => 'Response Department'])->getStructuredContent();

        $this->assertArrayHasKey('id', $content);
        $this->assertEquals('Response Department', $content['name']);
    }

    public function test_creates_department_with_optional_fields()
    {
        $location = Location::factory()->create();
        $manager = User::factory()->create();

        $this->handle([
            'name' => 'Full Department',
            'location_id' => $location->id,
            'manager_id' => $manager->id,
            'phone' => '555-1234',
            'fax' => '555-5678',
            'notes' => 'Department notes',
        ]);

        $this->assertDatabaseHas('departments', [
            'name' => 'Full Department',
            'location_id' => $location->id,
            'manager_id' => $manager->id,
            'phone' => '555-1234',
            'fax' => '555-5678',
        ]);
    }

    public function test_returns_error_when_name_missing()
    {
        $this->assertTrue($this->handle([])->responses()->first()->isError());
    }

    public function test_returns_error_when_location_does_not_exist()
    {
        $this->assertTrue($this->handle([
            'name' => 'Bad Location Dept',
            'location_id' => 999999,
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle(['name' => 'Blocked Department'])->responses()->first()->isError());
        $this->assertDatabaseMissing('departments', ['name' => 'Blocked Department']);
    }
}
