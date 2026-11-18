<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    // ThrottlesLogins reads/writes RateLimiter cache counters. The
    // array cache driver persists across tests in-process, so a prior
    // test could push this one past the lockout threshold before
    // its first assertion. Zero the counters.
    Cache::flush();
});

test('validation failure does not redirect to offsite referer', function () {
    User::factory()->create();

    $this->from('https://evil.example.net/phish')
        ->post('/login', [
            'username' => '',
            'password' => '',
        ])
        ->assertRedirect(route('login'));
});

test('authentication failure does not redirect to offsite referer', function () {
    User::factory()->create(['username' => 'legit-user']);

    $this->from('https://evil.example.net/phish')
        ->post('/login', [
            'username' => 'legit-user',
            'password' => 'wrong-password',
        ])
        ->assertRedirect(route('login'));
});

test('validation failure honors same origin referer', function () {
    User::factory()->create();

    $safeReferer = config('app.url').'/login';

    $this->from($safeReferer)
        ->post('/login', [
            'username' => '',
            'password' => '',
        ])
        ->assertRedirect($safeReferer);
});
