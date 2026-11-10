<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\UpdateUserTool;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class UpdateUserToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new UpdateUserTool)->handle(new Request($args));
    }

    public function test_updates_user_by_id()
    {
        $user = User::factory()->create(['first_name' => 'Old']);

        $content = $this->handle([
            'id' => $user->id,
            'first_name' => 'New',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'first_name' => 'New']);
    }

    public function test_updates_user_by_username()
    {
        $user = User::factory()->create(['username' => 'lookup.by.username', 'jobtitle' => 'Old Title']);

        $content = $this->handle([
            'username' => 'lookup.by.username',
            'jobtitle' => 'New Title',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'jobtitle' => 'New Title']);
    }

    public function test_updates_user_by_email()
    {
        $user = User::factory()->create(['email' => 'update.by@example.com', 'jobtitle' => 'Old Title']);

        $content = $this->handle([
            'email' => 'update.by@example.com',
            'jobtitle' => 'Senior Engineer',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'jobtitle' => 'Senior Engineer']);
    }

    public function test_renames_username_via_new_username()
    {
        $user = User::factory()->create(['username' => 'old.username.mcp']);

        $content = $this->handle([
            'username' => 'old.username.mcp',
            'new_username' => 'new.username.mcp',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertEquals('new.username.mcp', $content['username']);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'username' => 'new.username.mcp']);
    }

    public function test_updates_email_via_new_email()
    {
        $user = User::factory()->create(['email' => 'before@example.com']);

        $this->handle([
            'id' => $user->id,
            'new_email' => 'after@example.com',
        ]);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'email' => 'after@example.com']);
    }

    public function test_updates_password()
    {
        $user = User::factory()->create();

        $this->handle([
            'id' => $user->id,
            'password' => 'newpassword99',
        ]);

        $this->assertTrue(Hash::check('newpassword99', $user->fresh()->password));
    }

    public function test_updates_multiple_fields_at_once()
    {
        $user = User::factory()->create();
        $department = Department::factory()->create();
        $location = Location::factory()->create();

        $this->handle([
            'id' => $user->id,
            'jobtitle' => 'Team Lead',
            'phone' => '555-9999',
            'department_id' => $department->id,
            'location_id' => $location->id,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'jobtitle' => 'Team Lead',
            'phone' => '555-9999',
            'department_id' => $department->id,
            'location_id' => $location->id,
        ]);
    }

    public function test_returns_error_when_user_not_found()
    {
        $this->assertTrue($this->handle([
            'id' => 999999,
            'first_name' => 'Ghost',
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_no_identifier_provided()
    {
        $this->assertTrue($this->handle([
            'first_name' => 'No Identifier',
        ])->responses()->first()->isError());
    }

    public function test_response_includes_id_and_username()
    {
        $user = User::factory()->create();

        $content = $this->handle([
            'id' => $user->id,
            'first_name' => 'Updated',
        ])->getStructuredContent();

        $this->assertEquals($user->id, $content['id']);
        $this->assertEquals($user->username, $content['username']);
    }

    // --- canEditAuthFields permission gate ---

    public function test_non_admin_cannot_change_auth_fields_of_admin()
    {
        // Acting user is a plain user (set in setUp); target is an admin
        $admin = User::factory()->admin()->create();

        $response = $this->handle([
            'id' => $admin->id,
            'password' => 'shouldnotwork1',
        ]);

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertFalse(Hash::check('shouldnotwork1', $admin->fresh()->password));
    }

    public function test_non_admin_cannot_change_auth_fields_of_superuser()
    {
        $superuser = User::factory()->superuser()->create();

        $response = $this->handle([
            'id' => $superuser->id,
            'new_email' => 'hacked@example.com',
        ]);

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertDatabaseMissing('users', ['id' => $superuser->id, 'email' => 'hacked@example.com']);
    }

    public function test_admin_cannot_change_auth_fields_of_superuser()
    {
        $this->actingAs(User::factory()->admin()->create());
        $superuser = User::factory()->superuser()->create();

        $response = $this->handle([
            'id' => $superuser->id,
            'new_username' => 'hijacked',
        ]);

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertDatabaseMissing('users', ['id' => $superuser->id, 'username' => 'hijacked']);
    }

    public function test_superuser_can_change_auth_fields_of_any_user()
    {
        $this->actingAs(User::factory()->superuser()->create());
        $target = User::factory()->admin()->create();

        $content = $this->handle([
            'id' => $target->id,
            'new_email' => 'superchanged@example.com',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('users', ['id' => $target->id, 'email' => 'superchanged@example.com']);
    }

    public function test_non_auth_fields_are_still_updated_even_without_can_edit_auth_fields()
    {
        // A non-admin updating an admin's non-sensitive fields (jobtitle etc.) should still work
        $admin = User::factory()->admin()->create(['jobtitle' => 'Old Title']);

        $content = $this->handle([
            'id' => $admin->id,
            'jobtitle' => 'New Title',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'jobtitle' => 'New Title']);
    }
}
