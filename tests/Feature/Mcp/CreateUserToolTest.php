<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CreateUserTool;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CreateUserToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->createUsers()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new CreateUserTool)->handle(new Request($args));
    }

    public function test_creates_user_with_required_fields()
    {
        $content = $this->handle([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'username' => 'jane.doe.mcp',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('users', ['username' => 'jane.doe.mcp', 'first_name' => 'Jane']);
    }

    public function test_response_includes_new_user_id_and_username()
    {
        $content = $this->handle([
            'first_name' => 'Test',
            'username' => 'new.user.mcp',
        ])->getStructuredContent();

        $this->assertArrayHasKey('id', $content);
        $this->assertArrayHasKey('username', $content);
        $this->assertEquals('new.user.mcp', $content['username']);
        $this->assertIsInt($content['id']);
    }

    public function test_hashes_password_when_provided()
    {
        $this->handle([
            'first_name' => 'Secure',
            'username' => 'secure.user.mcp',
            'password' => 'secret1234',
        ]);

        $user = User::where('username', 'secure.user.mcp')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('secret1234', $user->password));
    }

    public function test_creates_user_with_no_password_when_omitted()
    {
        $this->handle([
            'first_name' => 'Nopass',
            'username' => 'nopass.user.mcp',
        ]);

        $user = User::where('username', 'nopass.user.mcp')->first();
        $this->assertNotNull($user);
        $this->assertFalse(Hash::check('', $user->password));
    }

    public function test_creates_user_with_email_and_contact_fields()
    {
        $this->handle([
            'first_name' => 'Contact',
            'username' => 'contact.user.mcp',
            'email' => 'contact@example.com',
            'phone' => '555-1234',
            'jobtitle' => 'Engineer',
        ]);

        $this->assertDatabaseHas('users', [
            'username' => 'contact.user.mcp',
            'email' => 'contact@example.com',
            'phone' => '555-1234',
            'jobtitle' => 'Engineer',
        ]);
    }

    public function test_creates_user_with_department_and_location()
    {
        $department = Department::factory()->create();
        $location = Location::factory()->create();

        $this->handle([
            'first_name' => 'Located',
            'username' => 'located.user.mcp',
            'department_id' => $department->id,
            'location_id' => $location->id,
        ]);

        $this->assertDatabaseHas('users', [
            'username' => 'located.user.mcp',
            'department_id' => $department->id,
            'location_id' => $location->id,
        ]);
    }

    public function test_returns_error_when_username_missing()
    {
        $this->assertTrue($this->handle([
            'first_name' => 'No Username',
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_first_name_missing()
    {
        $this->assertTrue($this->handle([
            'username' => 'no.firstname.mcp',
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_username_already_exists()
    {
        User::factory()->create(['username' => 'duplicate.mcp']);

        $response = $this->handle([
            'first_name' => 'Dupe',
            'username' => 'duplicate.mcp',
        ]);

        $this->assertTrue($response->responses()->first()->isError());
    }

    public function test_activates_user_by_default()
    {
        $this->handle([
            'first_name' => 'Active',
            'username' => 'default.active.mcp',
        ]);

        $this->assertDatabaseHas('users', ['username' => 'default.active.mcp', 'activated' => 1]);
    }

    public function test_can_create_deactivated_user()
    {
        $this->handle([
            'first_name' => 'Inactive',
            'username' => 'inactive.user.mcp',
            'activated' => false,
        ]);

        $this->assertDatabaseHas('users', ['username' => 'inactive.user.mcp', 'activated' => 0]);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle([
            'first_name' => 'Blocked',
            'username' => 'blocked.user.mcp',
        ])->responses()->first()->isError());
        $this->assertDatabaseMissing('users', ['username' => 'blocked.user.mcp']);
    }
}
