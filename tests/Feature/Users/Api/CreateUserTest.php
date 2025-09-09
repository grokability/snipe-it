<?php

namespace Tests\Feature\Users\Api;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CreateUserTest extends TestCase
{
    public function testRequiresPermission()
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

    public function testCompanyIdNeedsToBeInteger()
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

    public function testDepartmentIdNeedsToBeInteger()
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

    public function testCanCreateUser()
    {
        Notification::fake();

        $this->actingAsForApi(User::factory()->createUsers()->create())
            ->postJson(route('api.users.store'), [
                'first_name' => 'Test First Name',
                'last_name' => 'Test Last Name',
                'username' => 'testuser',
                'password' => 'testpassword1235!!',
                'password_confirmation' => 'testpassword1235!!',
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

        Notification::assertNothingSent();
    }

    public function testCanCreateAndNotifyUser()
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
}
