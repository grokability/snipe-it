<?php

use App\Exceptions\Handler;
use App\Models\User;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    // ThrottlesLogins shares a RateLimiter cache across tests
    // in-process, so zero it. Otherwise prior failed-login attempts
    // can push this test past the lockout threshold before asserts.
    Cache::flush();
});

test('token mismatch exception redirect uses sameorigin guard', function () {
    $handler = app(Handler::class);

    $request = bindRequestWithReferer('/login', 'https://evil.example.net/phish');

    $response = $handler->render($request, new TokenMismatchException);

    expect($response->getStatusCode())->toBe(302);
    expect(rtrim($response->headers->get('Location'), '/'))->toBe(config('app.url'));
});

test('token mismatch exception redirect honors same origin referer', function () {
    $handler = app(Handler::class);
    $safeReferer = config('app.url').'/some/path';

    $request = bindRequestWithReferer('/login', $safeReferer);

    $response = $handler->render($request, new TokenMismatchException);

    expect($response->getStatusCode())->toBe(302);
    expect($response->headers->get('Location'))->toBe($safeReferer);
});

test('invalid date exception redirect uses sameorigin guard', function () {
    $handler = app(Handler::class);

    $request = bindRequestWithReferer('/anything', 'https://evil.example.net/phish');

    $response = $handler->render($request, new InvalidFormatException('bogus date'));

    expect($response->getStatusCode())->toBe(302);
    expect(rtrim($response->headers->get('Location'), '/'))->toBe(config('app.url'));
});

/**
 * Construct a POST request with the given Referer and bind it as
 * the container's active request so url()->previous() reads from it.
 * Handler::render() reaches for url()->previous() internally via
 * the global helper, which resolves against the container-bound
 * request rather than any ad-hoc instance.
 */
function bindRequestWithReferer(string $path, string $referer): Request
{
    $request = Request::create(config('app.url').$path, 'POST');
    $request->headers->set('referer', $referer);
    app()->instance('request', $request);

    return $request;
}

test('reset password invalid token does not redirect to offsite referer', function () {
    User::factory()->create(['email' => 'reset-me@example.test', 'username' => 'reset-me']);

    // Flipping the password-reset path to the bad-token branch at
    // ResetPasswordController:131 requires a username + email that
    // match a real user, plus a token that doesn't verify.
    $response = $this->from('https://evil.example.net/phish')
        ->post('/password/reset', [
            'username' => 'reset-me',
            'email' => 'reset-me@example.test',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
            'token' => 'this-token-is-invalid',
        ]);

    $this->assertNotSame('https://evil.example.net/phish', $response->headers->get('Location'));
});
