<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    #[Test]
    public function successful_google_callback_updates_the_last_login_timestamp(): void
    {

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

        $this->assertNotNull($user->fresh()->last_login, 'last_login must be stamped after a successful Google callback.');
        $this->assertTrue(Auth::check(), 'User must be authenticated after a successful Google callback.');
    }

    #[Test]
    public function long_google_avatar_url_is_stored_intact(): void
    {
        // Regression test for issue #19620. Google Workspace avatar URLs
        // like `https://lh3.googleusercontent.com/a-/ALV-Uj...` routinely
        // run 260+ characters. The users.avatar column was previously
        // varchar(191), so MySQL in non-strict mode silently truncated
        // the stored URL, leaving a malformed token that 400s on fetch
        // and renders as a broken image.
        $user = User::factory()->create(['username' => 'me@example.org']);

        $longUrl = 'https://lh3.googleusercontent.com/a-/'.str_repeat('ALV-Uj0123456789', 15).'=s96-c';
        $this->assertGreaterThan(191, strlen($longUrl));

        $socialUser = Mockery::mock(SocialiteUser::class);
        $socialUser->shouldReceive('getEmail')->andReturn('me@example.org');
        $socialUser->avatar = $longUrl;

        $driver = Mockery::mock();
        $driver->shouldReceive('user')->andReturn($socialUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($driver);

        $this->get(route('google.callback', ['code' => 'google-oauth-code']));

        $this->assertSame($longUrl, $user->fresh()->avatar);
    }

    #[Test]
    public function callback_without_code_redirects_to_login_without_hitting_google(): void
    {
        // Satisfies Setting::setupCompleted() so CheckForSetup lets the request through.
        User::factory()->create();
        Socialite::shouldReceive('driver')->never();

        $this->get(route('google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('username');

        $this->assertFalse(Auth::check());
    }

    #[Test]
    public function callback_with_error_param_redirects_to_login_without_hitting_google(): void
    {
        User::factory()->create();
        Socialite::shouldReceive('driver')->never();

        $this->get(route('google.callback', ['error' => 'access_denied']))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('username');

        $this->assertFalse(Auth::check());
    }

    #[Test]
    public function callback_client_exception_from_token_endpoint_redirects_to_login(): void
    {
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

        $this->assertFalse(Auth::check());
    }
}
