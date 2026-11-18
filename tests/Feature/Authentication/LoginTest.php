<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

beforeEach(function () {
    // Every test in this class exercises Laravel's ThrottlesLogins
    // trait, which reads and writes counters through the RateLimiter
    // cache. The array cache driver used in tests persists for the
    // lifetime of the process, so counters left over from a prior
    // test (either in this file or another one that also POSTs to
    // /login) can push the current test past its lockout threshold
    // before its first assertion. Flush the whole cache so every
    // test starts from a zeroed counter.
    Cache::flush();
});

test('logs failed login attempt', function () {
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
});

test('login throttle config is respected', function () {
    // Regression coverage for the Laravel 12 upgrade regression where
    // config/auth.php collapsed the nested throttle array back to a
    // scalar, causing LoginController's config reads to return null
    // and the ThrottlesLogins trait to silently no-op.
    //
    // A user has to exist for the login POST to reach the controller.
    // With an empty users table Snipe-IT's setup middleware short-
    // circuits every POST /login to /setup, so the throttle path
    // never runs.
    User::factory()->create();

    config(['auth.login_throttle.max_attempts' => 1]);
    config(['auth.login_throttle.lockout_duration' => 60]);

    // Belt-and-suspenders: explicitly clear the exact key Laravel
    // will use for this attempt (username|ip, transliterated /
    // lowercased) in case anything survived Cache::flush().
    RateLimiter::clear(Str::transliterate(Str::lower('invalid username').'|127.0.0.100'));

    // First failed login registers one attempt. max_attempts is 1,
    // so the second POST must be blocked.
    $this->from('/login')
        ->withServerVariables(['REMOTE_ADDR' => '127.0.0.100'])
        ->post('/login', [
            'username' => 'invalid username',
            'password' => 'invalid password',
        ]);

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
});

test('logs successful login', function () {
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
});
