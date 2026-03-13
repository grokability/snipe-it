<?php

namespace Tests\Feature\Authentication;

use App\Models\User;
use Tests\TestCase;

class LoginTest extends TestCase
{
    public function testLogsFailedLoginAttempt()
    {

        User::factory()->create(['username' => 'username_here']);

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.100'])
            ->post('/login', [
                'username' => 'username_here',
                'password' => 'not a real password',
            ], [
                'User-Agent' => 'Some Custom User Agent',
            ]);

        $this->assertDatabaseHas('login_attempts', [
            'username' => 'username_here',
            'remote_ip' => '127.0.0.100',
            'user_agent' => 'Some Custom User Agent',
            'successful' => 0,
        ]);
    }


    public function testLoginThrottleConfigIsRespected()
    {

       $this->markTestIncomplete("This test is flaky and needs to be fixed. Passes and fails seemingly at random.");
       User::factory()->create(['username' => 'username_here']);

       config(['auth.passwords.users.throttle.max_attempts' => 1]);
       config(['auth.passwords.users.throttle.lockout_duration' => 1]);

        for ($i = 0; $i < 2; ++$i) {
            $this->from('/login')
                ->withServerVariables(['REMOTE_ADDR' => '127.0.0.100'])
                ->post('/login', [
                    'username' => 'invalid username',
                    'password' => 'invalid password',
                ]);
        }


        $response = $this->from('/login')
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.100'])
            ->post('/login', [
                'username' => 'invalid username',
                'password' => 'invalid password',
            ])
            ->assertSessionHasErrors(['username'])
            ->assertStatus(302)
            ->assertRedirect('/login');

        $this->followRedirects($response)->assertSee(trans('auth.throttle', ['minutes' => 1]));
    }

    public function testLogsSuccessfulLogin()
    {
        $this->markTestIncomplete("This test is flaky and needs to be fixed. Passes and fails seemingly at random.");
        User::factory()->create(['username' => 'username_here']);

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.100'])
            ->post('/login', [
                'username' => 'username_here',
                'password' => 'password',
            ], [
                'User-Agent' => 'Some Custom User Agent',
            ]);

        $this->assertDatabaseHas('login_attempts', [
            'username' => 'username_here',
            'remote_ip' => '127.0.0.100',
            'user_agent' => 'Some Custom User Agent',
            'successful' => 1,
        ]);
    }
}
