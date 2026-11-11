<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ListUsersTool;
use App\Models\Company;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ListUsersToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewUsers()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new ListUsersTool)->handle(new Request($args));
    }

    public function test_returns_list_of_users()
    {
        User::factory()->count(3)->create();

        $content = $this->handle()->getStructuredContent();

        $this->assertArrayHasKey('users', $content);
        $this->assertArrayHasKey('total', $content);
        $this->assertGreaterThanOrEqual(3, $content['total']);
    }

    public function test_user_records_contain_expected_fields()
    {
        User::factory()->create();

        $content = $this->handle(['limit' => 1])->getStructuredContent();

        $user = $content['users'][0];
        $this->assertArrayHasKey('id', $user);
        $this->assertArrayHasKey('username', $user);
        $this->assertArrayHasKey('first_name', $user);
        $this->assertArrayHasKey('last_name', $user);
        $this->assertArrayHasKey('email', $user);
        $this->assertArrayHasKey('activated', $user);
        $this->assertArrayHasKey('assets_count', $user);
    }

    public function test_filters_by_company_id()
    {
        $company = Company::factory()->create();
        $user = $company->users()->save(User::factory()->make());

        $content = $this->handle(['company_id' => $company->id])->getStructuredContent();

        $ids = array_column($content['users'], 'id');
        $this->assertContains($user->id, $ids);
        foreach ($content['users'] as $u) {
            $this->assertEquals($company->name, $u['company']);
        }
    }

    public function test_filters_by_department_id()
    {
        $department = Department::factory()->create();
        $user = User::factory()->create(['department_id' => $department->id]);

        $content = $this->handle(['department_id' => $department->id])->getStructuredContent();

        $ids = array_column($content['users'], 'id');
        $this->assertContains($user->id, $ids);
    }

    public function test_filters_by_location_id()
    {
        $location = Location::factory()->create();
        $user = User::factory()->create(['location_id' => $location->id]);

        $content = $this->handle(['location_id' => $location->id])->getStructuredContent();

        $ids = array_column($content['users'], 'id');
        $this->assertContains($user->id, $ids);
    }

    public function test_filters_by_activated_status()
    {
        $active = User::factory()->create(['activated' => true]);
        $inactive = User::factory()->create(['activated' => false]);

        $content = $this->handle(['activated' => false])->getStructuredContent();

        $ids = array_column($content['users'], 'id');
        $this->assertContains($inactive->id, $ids);
        $this->assertNotContains($active->id, $ids);
    }

    public function test_search_filters_by_name()
    {
        $target = User::factory()->create(['first_name' => 'Uniquefirstname', 'last_name' => 'McTest']);

        $content = $this->handle(['search' => 'Uniquefirstname'])->getStructuredContent();

        $ids = array_column($content['users'], 'id');
        $this->assertContains($target->id, $ids);
    }

    public function test_pagination_limit_and_offset()
    {
        User::factory()->count(10)->create();

        $page1 = $this->handle(['limit' => 3, 'offset' => 0])->getStructuredContent();
        $page2 = $this->handle(['limit' => 3, 'offset' => 3])->getStructuredContent();

        $this->assertCount(3, $page1['users']);
        $this->assertCount(3, $page2['users']);
        $this->assertNotEquals($page1['users'][0]['id'], $page2['users'][0]['id']);
    }

    public function test_response_contains_pagination_meta()
    {
        $content = $this->handle(['limit' => 5, 'offset' => 0])->getStructuredContent();

        $this->assertEquals(5, $content['limit']);
        $this->assertEquals(0, $content['offset']);
        $this->assertArrayHasKey('total', $content);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle()->responses()->first()->isError());
    }

    public function test_excludes_soft_deleted_users_by_default()
    {
        User::factory()->create(['username' => 'active-one']);
        $deleted = User::factory()->create(['username' => 'deleted-one']);
        $deleted->delete();

        $content = $this->handle(['limit' => 500])->getStructuredContent();
        $usernames = array_column($content['users'], 'username');

        $this->assertContains('active-one', $usernames);
        $this->assertNotContains('deleted-one', $usernames);
    }

    public function test_include_deleted_returns_both_active_and_soft_deleted()
    {
        User::factory()->create(['username' => 'active-one']);
        $deleted = User::factory()->create(['username' => 'deleted-one']);
        $deleted->delete();

        $content = $this->handle(['include_deleted' => true, 'limit' => 500])->getStructuredContent();
        $usernames = array_column($content['users'], 'username');

        $this->assertContains('active-one', $usernames);
        $this->assertContains('deleted-one', $usernames);
    }

    public function test_only_deleted_returns_soft_deleted_only()
    {
        User::factory()->create(['username' => 'active-one']);
        $deleted = User::factory()->create(['username' => 'deleted-one']);
        $deleted->delete();

        $content = $this->handle(['only_deleted' => true, 'limit' => 500])->getStructuredContent();
        $usernames = array_column($content['users'], 'username');

        $this->assertContains('deleted-one', $usernames);
        $this->assertNotContains('active-one', $usernames);
    }

    public function test_response_includes_deleted_at_and_child_counts()
    {
        User::factory()->create(['username' => 'marker-user']);

        $content = $this->handle(['limit' => 500])->getStructuredContent();
        $row = collect($content['users'])->firstWhere('username', 'marker-user');

        $this->assertNotNull($row);
        $this->assertArrayHasKey('deleted_at', $row);
        $this->assertArrayHasKey('last_login', $row);
        $this->assertArrayHasKey('assets_count', $row);
        $this->assertArrayHasKey('licenses_count', $row);
        $this->assertArrayHasKey('accessories_count', $row);
        $this->assertArrayHasKey('consumables_count', $row);
        $this->assertArrayHasKey('notes', $row);
    }
}
