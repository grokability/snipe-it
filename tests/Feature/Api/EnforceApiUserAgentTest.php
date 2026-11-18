<?php

use App\Http\Middleware\EnforceApiUserAgent;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Passport;

test('master off bails out entirely so nothing is blocked', function () {
    // Master off — even a curl UA AND a blank UA pass through, regardless of
    // any other setting or per-route parameter.
    $this->settings->set([
        'block_api_user_agents' => '0',
        'blocked_api_user_agents' => "curl/\nPostmanRuntime/",
        'block_blank_api_user_agents' => '1',
    ]);

    Passport::actingAs(User::factory()->superuser()->create());

    $this->withHeader('User-Agent', 'curl/8.5.0')
        ->getJson(route('api.users.selectlist'))
        ->assertOk();

    $this->withHeader('User-Agent', '')
        ->getJson(route('api.users.selectlist'))
        ->assertOk();
});

test('master on blocks matching pattern and echoes user agent', function () {
    $this->settings->set([
        'block_api_user_agents' => '1',
        'blocked_api_user_agents' => "curl/\nPostmanRuntime/",
        'block_blank_api_user_agents' => '0',
    ]);

    Passport::actingAs(User::factory()->superuser()->create());

    $this->withHeader('User-Agent', 'curl/8.5.0')
        ->getJson(route('api.users.selectlist'))
        ->assertForbidden()
        ->assertJson([
            'status' => 'error',
            'payload' => ['user_agent' => 'curl/8.5.0'],
        ]);
});

test('master on pattern match is case insensitive', function () {
    $this->settings->set([
        'block_api_user_agents' => '1',
        'blocked_api_user_agents' => 'curl/',
        'block_blank_api_user_agents' => '0',
    ]);

    Passport::actingAs(User::factory()->superuser()->create());

    $this->withHeader('User-Agent', 'CURL/8.5.0')
        ->getJson(route('api.users.selectlist'))
        ->assertForbidden();
});

test('master on unmatched user agent passes', function () {
    $this->settings->set([
        'block_api_user_agents' => '1',
        'blocked_api_user_agents' => "curl/\nPostmanRuntime/",
        'block_blank_api_user_agents' => '0',
    ]);

    Passport::actingAs(User::factory()->superuser()->create());

    $this->withHeader('User-Agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/120.0.0.0')
        ->getJson(route('api.users.selectlist'))
        ->assertOk();
});

test('master on blank blocking off lets blank user agent through on api', function () {
    // Pattern blocking on but blank blocking off: an admin who has webhooks/other
    // integrations that send blanks can keep them working.
    $this->settings->set([
        'block_api_user_agents' => '1',
        'blocked_api_user_agents' => 'curl/',
        'block_blank_api_user_agents' => '0',
    ]);

    Passport::actingAs(User::factory()->superuser()->create());

    $this->withHeader('User-Agent', '')
        ->getJson(route('api.users.selectlist'))
        ->assertOk();
});

test('master on blank blocking on rejects blank user agent on api', function () {
    $this->settings->set([
        'block_api_user_agents' => '1',
        'blocked_api_user_agents' => null,
        'block_blank_api_user_agents' => '1',
    ]);

    Passport::actingAs(User::factory()->superuser()->create());

    $this->withHeader('User-Agent', '')
        ->getJson(route('api.users.selectlist'))
        ->assertForbidden()
        ->assertJson([
            'payload' => ['user_agent' => ''],
        ]);
});

test('pattern is matched only at start of user agent', function () {
    $this->settings->set([
        'block_api_user_agents' => '1',
        'blocked_api_user_agents' => 'curl/',
        'block_blank_api_user_agents' => '0',
    ]);

    Passport::actingAs(User::factory()->superuser()->create());

    $this->withHeader('User-Agent', 'MyWrapper/1.0 (uses curl/8.5.0 internally)')
        ->getJson(route('api.users.selectlist'))
        ->assertOk();

    $this->withHeader('User-Agent', 'curl/8.5.0')
        ->getJson(route('api.users.selectlist'))
        ->assertForbidden();
});

test('master on empty pattern lines do not match every request', function () {
    // Without filtering, a blank line would be a zero-length substring that matches every UA.
    $this->settings->set([
        'block_api_user_agents' => '1',
        'blocked_api_user_agents' => "curl/\n\n   \n",
        'block_blank_api_user_agents' => '0',
    ]);

    Passport::actingAs(User::factory()->superuser()->create());

    $this->withHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0) Chrome/120.0.0.0')
        ->getJson(route('api.users.selectlist'))
        ->assertOk();
});

test('scim routes block pattern matches', function () {
    $this->settings->set([
        'block_api_user_agents' => '1',
        'blocked_api_user_agents' => 'curl/',
        'block_blank_api_user_agents' => '0',
    ]);

    Passport::actingAs(User::factory()->superuser()->create());

    $this->withHeader('User-Agent', 'curl/8.5.0')
        ->getJson('/scim/v2/Users')
        ->assertForbidden()
        ->assertJson([
            'payload' => ['user_agent' => 'curl/8.5.0'],
        ]);
});

test('scim routes allow blank user agent even when admin blocks blanks', function () {
    // Hard override: even with the admin toggle set to block blanks, SCIM routes
    // pass through because Entra ID SCIM provisioning sends a blank User-Agent.
    $this->settings->set([
        'block_api_user_agents' => '1',
        'blocked_api_user_agents' => null,
        'block_blank_api_user_agents' => '1',
    ]);

    Passport::actingAs(User::factory()->superuser()->create());

    $response = $this->withHeader('User-Agent', '')
        ->getJson('/scim/v2/Users');

    $this->assertNotEquals(403, $response->status());
});

test('curl pattern blocks real curl but not third party app that mentions curl', function () {
    // Regression for the "BradyCoolIntegration / RipCurl" review thread: the curl/
    // pattern must block a real curl client at the start of the UA, but must not
    // touch an unrelated UA that just happens to contain the letters "curl" later
    // in its identifier.
    $this->settings->set([
        'block_api_user_agents' => '1',
        'blocked_api_user_agents' => 'curl/',
        'block_blank_api_user_agents' => '0',
    ]);

    Passport::actingAs(User::factory()->superuser()->create());

    $this->withHeader('User-Agent', 'Curl/8.7.1')
        ->getJson(route('api.users.selectlist'))
        ->assertForbidden()
        ->assertJson(['payload' => ['user_agent' => 'Curl/8.7.1']]);

    $this->withHeader('User-Agent', 'BradyCoolIntegration/1.0.0 (RipCurl something something ARM blah')
        ->getJson(route('api.users.selectlist'))
        ->assertOk();
});

test('bare curl pattern without trailing slash still only matches at start', function () {
    // If an admin shortens the pattern to just "curl" (no slash), the prefix-only
    // semantics from === 0 still protect a UA that contains the substring "curl"
    // later in its identifier.
    $this->settings->set([
        'block_api_user_agents' => '1',
        'blocked_api_user_agents' => 'curl',
        'block_blank_api_user_agents' => '0',
    ]);

    Passport::actingAs(User::factory()->superuser()->create());

    // Real curl client — blocked.
    $this->withHeader('User-Agent', 'Curl/8.7.1')
        ->getJson(route('api.users.selectlist'))
        ->assertForbidden();

    // Third-party app whose name contains "Curl" mid-string — not blocked.
    $this->withHeader('User-Agent', 'BradyCoolIntegration/1.0.0 (RipCurl something something ARM blah')
        ->getJson(route('api.users.selectlist'))
        ->assertOk();
});

test('missing user agent header is treated the same as empty string', function () {
    // Symfony's test client always injects a default User-Agent, so we exercise the
    // middleware directly with a Request that has no User-Agent header at all to
    // confirm null (header absent) collapses to empty-string behavior.
    $this->settings->set([
        'block_api_user_agents' => '1',
        'blocked_api_user_agents' => null,
        'block_blank_api_user_agents' => '1',
    ]);

    Auth::loginUsingId(User::factory()->superuser()->create()->id);

    $request = Request::create('/api/v1/users/selectlist');
    $request->headers->remove('User-Agent');
    expect($request->header('User-Agent'))->toBeNull();

    $response = (new EnforceApiUserAgent)->handle($request, fn () => response('passed'));

    expect($response->getStatusCode())->toBe(403);
    $payload = json_decode($response->getContent(), true);
    expect($payload['payload']['user_agent'])->toBeNull();
});

test('default patterns constant is non empty', function () {
    expect(Setting::DEFAULT_BLOCKED_API_USER_AGENTS)->not->toBeEmpty();
    expect(Setting::DEFAULT_BLOCKED_API_USER_AGENTS)->toContain('curl/');
});
