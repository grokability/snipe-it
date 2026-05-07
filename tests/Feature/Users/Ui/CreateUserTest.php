<?php

namespace Tests\Feature\Users\Ui;

use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CreateUserTest extends TestCase
{
    public function test_permission_required_to_create_user()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('users.create'))
            ->assertForbidden();
    }

    public function test_page_renders()
    {
        $this->actingAs(User::factory()->createUsers()->create())
            ->get(route('users.create'))
            ->assertOk();

    }

    public function test_can_create_user()
    {
        Notification::fake();

        $response = $this->actingAs(User::factory()->createUsers()->viewUsers()->create())
            ->from(route('users.index'))
            ->post(route('users.store'), [
                'first_name' => 'Test First Name',
                'last_name' => 'Test Last Name',
                'username' => 'testuser',
                'password' => 'testpassword1235!!',
                'password_confirmation' => 'testpassword1235!!',
                'activated' => '1',
                'email' => 'foo@example.org',
                'notes' => 'Test Note',
            ])
            ->assertSessionHasNoErrors()
            ->assertStatus(302)
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'first_name' => 'Test First Name',
            'last_name' => 'Test Last Name',
            'username' => 'testuser',
            'activated' => '1',
            'email' => 'foo@example.org',
            'notes' => 'Test Note',

        ]);
        Notification::assertNothingSent();
        $this->followRedirects($response)->assertSee('Success');

    }

    public function test_can_create_and_notify_user()
    {

        Notification::fake();

        $response = $this->actingAs(User::factory()->createUsers()->viewUsers()->create())
            ->from(route('users.index'))
            ->post(route('users.store'), [
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
            ->assertSessionHasNoErrors()
            ->assertStatus(302)
            ->assertRedirect(route('users.index'));

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
        $this->followRedirects($response)->assertSee('Success');

    }

    public function test_non_admin_cannot_grant_admin_or_superuser_permissions_when_creating_user_via_ui()
    {
        $response = $this->actingAs(User::factory()->createUsers()->viewUsers()->create())
            ->from(route('users.index'))
            ->post(route('users.store'), [
                'first_name' => 'Taylor',
                'last_name' => 'Tester',
                'username' => 'taylor-create-ui',
                'password' => 'testpassword1235!!',
                'password_confirmation' => 'testpassword1235!!',
                'permission' => [
                    'admin' => '1',
                    'superuser' => '1',
                    'users.view' => '1',
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertStatus(302)
            ->assertRedirect(route('users.index'));

        $createdUser = User::where('username', 'taylor-create-ui')->firstOrFail();
        $decoded = (array) $createdUser->decodePermissions();

        $this->assertArrayNotHasKey('admin', $decoded, 'Non-admin user should not be able to grant admin during create');
        $this->assertArrayNotHasKey('superuser', $decoded, 'Non-admin user should not be able to grant superuser during create');
        $this->assertEquals(1, $decoded['users.view'] ?? null, 'Non-privileged permissions should still be createable');
        $this->followRedirects($response)->assertSee('Success');
    }

    public function test_admin_cannot_grant_superuser_permission_when_creating_user_via_ui()
    {
        $response = $this->actingAs(User::factory()->admin()->createUsers()->viewUsers()->create())
            ->from(route('users.index'))
            ->post(route('users.store'), [
                'first_name' => 'Alex',
                'last_name' => 'Admin',
                'username' => 'alex-create-ui',
                'password' => 'testpassword1235!!',
                'password_confirmation' => 'testpassword1235!!',
                'permission' => [
                    'admin' => '1',
                    'superuser' => '1',
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertStatus(302)
            ->assertRedirect(route('users.index'));

        $createdUser = User::where('username', 'alex-create-ui')->firstOrFail();
        $decoded = (array) $createdUser->decodePermissions();

        $this->assertSame('1', (string) ($decoded['admin'] ?? null), 'Admin should be able to grant admin during create');
        $this->assertArrayNotHasKey('superuser', $decoded, 'Admin should not be able to grant superuser during create');
        $this->followRedirects($response)->assertSee('Success');
    }
}
