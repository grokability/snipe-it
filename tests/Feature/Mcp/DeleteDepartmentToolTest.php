<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\DeleteDepartmentTool;
use App\Models\Department;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class DeleteDepartmentToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->deleteDepartments()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new DeleteDepartmentTool)->handle(new Request($args));
    }

    public function test_deletes_department_by_id()
    {
        $department = Department::factory()->create();

        $content = $this->handle(['id' => $department->id])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('departments', ['id' => $department->id]);
    }

    public function test_deletes_department_by_name()
    {
        $department = Department::factory()->create(['name' => 'Delete By Name Dept']);

        $content = $this->handle(['name' => 'Delete By Name Dept'])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('departments', ['id' => $department->id]);
    }

    public function test_response_includes_name()
    {
        $department = Department::factory()->create(['name' => 'Named Department']);

        $content = $this->handle(['id' => $department->id])->getStructuredContent();

        $this->assertEquals('Named Department', $content['name']);
    }

    public function test_returns_error_when_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999])->responses()->first()->isError());
    }

    public function test_returns_error_when_department_has_users()
    {
        $department = Department::factory()->create();
        User::factory()->create(['department_id' => $department->id]);

        $response = $this->handle(['id' => $department->id]);

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertNotSoftDeleted('departments', ['id' => $department->id]);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $department = Department::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle(['id' => $department->id])->responses()->first()->isError());
        $this->assertNotSoftDeleted('departments', ['id' => $department->id]);
    }
}
