<?php

use App\Models\User;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use PHPUnit\Framework\Attributes\Test;

test('successful google callback updates the last login timestamp', function () {
    $user = User::factory()->create([
        'username' => 'me@example.org',
        'last_login' => null,
    ]);

    $socialUser = Mockery::mock(SocialiteUser::class);
    $socialUser->shouldReceive('getEmail')->andReturn('me@example.org');
    $socialUser->avatar = 'https://lh3.googleusercontent.com/a/sample-avatar';

    $driver = Mockery::mock();
    $driver->shouldReceive('user')->andReturn($socialUser);
    Socialite::shouldReceive('driver')->with('google')->andReturn($driver);

    $this->get(route('google.callback', ['code' => 'google-oauth-code']))
        ->assertRedirect(route('home'));

    expect($user->fresh()->last_login)->not->toBeNull('last_login must be stamped after a successful Google callback.');
    expect(Auth::check())->toBeTrue('User must be authenticated after a successful Google callback.');
});

test('long google avatar url is stored intact', function () {
    // Regression test for issue #19620. Google Workspace avatar URLs
    // like `https://lh3.googleusercontent.com/a-/ALV-Uj...` routinely
    // run 260+ characters. The users.avatar column was previously
    // varchar(191), so MySQL in non-strict mode silently truncated
    // the stored URL, leaving a malformed token that 400s on fetch
    // and renders as a broken image.
    $user = User::factory()->create(['username' => 'me@example.org']);

    $longUrl = 'https://lh3.googleusercontent.com/a-/'.str_repeat('ALV-Uj0123456789', 15).'=s96-c';
    expect(strlen($longUrl))->toBeGreaterThan(191);

    $socialUser = Mockery::mock(SocialiteUser::class);
    $socialUser->shouldReceive('getEmail')->andReturn('me@example.org');
    $socialUser->avatar = $longUrl;

    $driver = Mockery::mock();
    $driver->shouldReceive('user')->andReturn($socialUser);
    Socialite::shouldReceive('driver')->with('google')->andReturn($driver);

    $this->get(route('google.callback', ['code' => 'google-oauth-code']));

    expect($user->fresh()->avatar)->toBe($longUrl);
});

test('callback without code redirects to login without hitting google', function () {
    // Satisfies Setting::setupCompleted() so CheckForSetup lets the request through.
    User::factory()->create();
    Socialite::shouldReceive('driver')->never();

    $this->get(route('google.callback'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('username');

    expect(Auth::check())->toBeFalse();
});

test('callback with error param redirects to login without hitting google', function () {
    User::factory()->create();
    Socialite::shouldReceive('driver')->never();

    $this->get(route('google.callback', ['error' => 'access_denied']))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('username');

    expect(Auth::check())->toBeFalse();
});

test('callback client exception from token endpoint redirects to login', function () {
    User::factory()->create();

    $clientException = new ClientException(
        'Client error: 400 Bad Request',
        new Request('POST', 'https://www.googleapis.com/oauth2/v4/token'),
        new Response(400),
    );

    $driver = Mockery::mock();
    $driver->shouldReceive('user')->andThrow($clientException);
    Socialite::shouldReceive('driver')->with('google')->andReturn($driver);

    $this->get(route('google.callback', ['code' => 'stale-or-reused']))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('username');

    expect(Auth::check())->toBeFalse();
});
