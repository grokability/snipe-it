<?php

namespace Tests\Feature\Users\Api;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CreateUserTest extends TestCase
{
    public function test_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.users.store'), [
                'first_name' => 'Joe',
                'username' => 'joe',
                'password' => 'joe_password',
                'password_confirmation' => 'joe_password',
            ])
            ->assertForbidden();
    }

    public function test_company_id_needs_to_be_integer()
    {
        $company = Company::factory()->create();

        $this->actingAsForApi(User::factory()->createUsers()->create())
            ->postJson(route('api.users.store'), [
                'company_id' => [$company->id],
                'first_name' => 'Joe',
                'username' => 'joe',
                'password' => 'joe_password',
                'password_confirmation' => 'joe_password',
            ])
            ->assertStatusMessageIs('error')
            ->assertJson(function (AssertableJson $json) {
                $json->has('messages.company_id')->etc();
            });
    }

    public function test_department_id_needs_to_be_integer()
    {
        $department = Department::factory()->create();

        $this->actingAsForApi(User::factory()->createUsers()->create())
            ->postJson(route('api.users.store'), [
                'department_id' => [$department->id],
                'first_name' => 'Joe',
                'username' => 'joe',
                'password' => 'joe_password',
                'password_confirmation' => 'joe_password',
            ])
            ->assertStatusMessageIs('error')
            ->assertJson(function (AssertableJson $json) {
                $json->has('messages.department_id')->etc();
            });
    }

    public function test_can_create_user()
    {
        Notification::fake();

        $response = $this->actingAsForApi(User::factory()->createUsers()->create())
            ->postJson(route('api.users.store'), [
                'first_name' => 'Test First Name',
                'last_name' => 'Test Last Name',
                'username' => 'testuser',
                'password' => 'testpassword1235!!',
                'password_confirmation' => 'testpassword1235!!',
                'activated' => '1',
                'email' => 'foo@example.org',
                'notes' => 'Test Note',
                'avatar' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAZAAAAEsAQMAAADXeXeBAAAABlBMVEX+AAD///+KQee0AAAACXBIWXMAAAsSAAALEgHS3X78AAAAB3RJTUUH5QQbCAoNcoiTQAAAACZJREFUaN7twTEBAAAAwqD1T20JT6AAAAAAAAAAAAAAAAAAAICnATvEAAEnf54JAAAAAElFTkSuQmCC',
            ])
            ->assertStatusMessageIs('success')
            ->assertOk();

        $this->assertDatabaseHas('users', [
            'first_name' => 'Test First Name',
            'last_name' => 'Test Last Name',
            'username' => 'testuser',
            'activated' => '1',
            'email' => 'foo@example.org',
            'notes' => 'Test Note',
        ]);

        Notification::assertNothingSent();

        $user = User::findOrFail($response['payload']['id']);

        // assert against resized hash
        $this->assertEquals(
            'db2e13ba04318c99058ca429d67777322f48566b',
            sha1(Storage::disk('public')->get(app('users_upload_path').$user->avatar))
        );
    }

    public function test_can_create_and_notify_user()
    {
        Notification::fake();

        $this->actingAsForApi(User::factory()->createUsers()->create())
            ->postJson(route('api.users.store'), [
                'first_name' => 'Test First Name',
                'last_name' => 'Test Last Name',
                'username' => 'testuser',
                'password' => 'testpassword1235!!',
                'password_confirmation' => 'testpassword1235!!',
                'send_welcome' => '1',
                'activated' => '1',
                'email' => 'foo@example.org',
                'notes' => 'Test Note',
            ])
            ->assertStatusMessageIs('success')
            ->assertOk();

        $this->assertDatabaseHas('users', [
            'first_name' => 'Test First Name',
            'last_name' => 'Test Last Name',
            'username' => 'testuser',
            'activated' => '1',
            'email' => 'foo@example.org',
            'notes' => 'Test Note',
        ]);

        $user = User::where('username', 'testuser')->first();
        Notification::assertSentTo($user, WelcomeNotification::class);
    }

    public function test_non_admin_cannot_grant_admin_or_superuser_permissions_when_creating_user_via_api()
    {
        $this->actingAsForApi(User::factory()->createUsers()->create())
            ->postJson(route('api.users.store'), [
                'first_name' => 'Taylor',
                'last_name' => 'Tester',
                'username' => 'taylor-create-api',
                'password' => 'testpassword1235!!',
                'password_confirmation' => 'testpassword1235!!',
                'permissions' => '{"admin":"1","superuser":"1","users.view":"1"}',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $createdUser = User::where('username', 'taylor-create-api')->firstOrFail();
        $decoded = (array) $createdUser->decodePermissions();

        $this->assertArrayNotHasKey('admin', $decoded, 'Non-admin user should not be able to grant admin during create');
        $this->assertArrayNotHasKey('superuser', $decoded, 'Non-admin user should not be able to grant superuser during create');
        $this->assertEquals(1, $decoded['users.view'] ?? null, 'Non-privileged permissions should still be createable');
    }

    public function test_admin_cannot_grant_superuser_permission_when_creating_user_via_api()
    {
        $this->actingAsForApi(User::factory()->admin()->createUsers()->create())
            ->postJson(route('api.users.store'), [
                'first_name' => 'Alex',
                'last_name' => 'Admin',
                'username' => 'alex-create-api',
                'password' => 'testpassword1235!!',
                'password_confirmation' => 'testpassword1235!!',
                'permissions' => [
                    'admin' => '1',
                    'superuser' => '1',
                ],
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $createdUser = User::where('username', 'alex-create-api')->firstOrFail();
        $decoded = (array) $createdUser->decodePermissions();

        $this->assertSame('1', (string) ($decoded['admin'] ?? null), 'Admin should be able to grant admin during create');
        $this->assertArrayNotHasKey('superuser', $decoded, 'Admin should not be able to grant superuser during create');
    }
}
