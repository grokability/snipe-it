<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\UpdateDepartmentTool;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class UpdateDepartmentToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->editDepartments()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new UpdateDepartmentTool)->handle(new Request($args));
    }

    public function test_updates_department_by_id()
    {
        $department = Department::factory()->create(['name' => 'Original Name']);

        $content = $this->handle([
            'id' => $department->id,
            'new_name' => 'Updated Name',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('departments', ['id' => $department->id, 'name' => 'Updated Name']);
    }

    public function test_updates_department_by_name()
    {
        $department = Department::factory()->create(['name' => 'Find By Name Dept']);

        $content = $this->handle([
            'name' => 'Find By Name Dept',
            'new_name' => 'Renamed Dept',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('departments', ['id' => $department->id, 'name' => 'Renamed Dept']);
    }

    public function test_response_includes_id_and_name()
    {
        $department = Department::factory()->create();

        $content = $this->handle([
            'id' => $department->id,
            'new_name' => 'Response Check Dept',
        ])->getStructuredContent();

        $this->assertArrayHasKey('id', $content);
        $this->assertEquals('Response Check Dept', $content['name']);
    }

    public function test_updates_multiple_fields()
    {
        $department = Department::factory()->create();
        $location = Location::factory()->create();
        $manager = User::factory()->create();

        $this->handle([
            'id' => $department->id,
            'location_id' => $location->id,
            'manager_id' => $manager->id,
            'phone' => '555-9999',
            'fax' => '555-8888',
            'notes' => 'Updated notes',
        ]);

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'location_id' => $location->id,
            'manager_id' => $manager->id,
            'phone' => '555-9999',
        ]);
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999, 'new_name' => 'Ghost'])->responses()->first()->isError());
    }

    public function test_returns_error_when_no_identifier_provided()
    {
        $this->assertTrue($this->handle(['new_name' => 'No ID'])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $department = Department::factory()->create(['name' => 'Unchanged Dept']);
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle([
            'id' => $department->id,
            'new_name' => 'Should Not Change',
        ])->responses()->first()->isError());

        $this->assertDatabaseHas('departments', ['id' => $department->id, 'name' => 'Unchanged Dept']);
    }
}
