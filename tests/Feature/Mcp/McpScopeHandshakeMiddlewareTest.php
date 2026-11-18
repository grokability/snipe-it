<?php

use App\Enums\McpScope;
use App\Http\Middleware\EnforceMcpScopeHandshake;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;
use Symfony\Component\HttpFoundation\Response;

function runMiddleware(Request $request): Response
{
    return (new EnforceMcpScopeHandshake)->handle($request, function () {
        return response('through', 200);
    });
}

test('request with no token passes through', function () {
    $request = Request::create('/mcp/snipe-it', 'POST');

    $response = runMiddleware($request);

    expect($response->getStatusCode())->toBe(200);
    expect($response->getContent())->toBe('through');
});

test('token with mcp scope passes through', function () {
    Passport::actingAs(User::factory()->create(), [McpScope::Read->value]);

    $request = Request::create('/mcp/snipe-it', 'POST');
    $request->setUserResolver(fn () => auth()->user());

    $response = runMiddleware($request);

    expect($response->getStatusCode())->toBe(200);
});

test('token with wildcard scope passes through', function () {
    Passport::actingAs(User::factory()->create(), ['*']);

    $request = Request::create('/mcp/snipe-it', 'POST');
    $request->setUserResolver(fn () => auth()->user());

    $response = runMiddleware($request);

    expect($response->getStatusCode())->toBe(200);
});

test('session scope alone passes handshake', function () {
    // McpScope::Session (`mcp:use`) is the connectivity gate.
    // A token with only Session passes the handshake so the
    // client can call tools/list and discover what capability
    // scopes it needs to elevate to. Per-tool enforcement in
    // SnipeMcpTool still blocks capability calls on a session-
    // only token.
    Passport::actingAs(User::factory()->create(), [McpScope::Session->value]);

    $request = Request::create('/mcp/snipe-it', 'POST');
    $request->setUserResolver(fn () => auth()->user());

    $response = runMiddleware($request);

    expect($response->getStatusCode())->toBe(200);
});

test('token with no mcp scopes is refused with 401 and www authenticate', function () {
    Passport::actingAs(User::factory()->create(), []);

    $request = Request::create('/mcp/snipe-it', 'POST');
    $request->setUserResolver(fn () => auth()->user());

    $response = runMiddleware($request);

    expect($response->getStatusCode())->toBe(401);

    $www = $response->headers->get('WWW-Authenticate');
    expect($www)->not->toBeNull('WWW-Authenticate header is required for OAuth clients to re-initiate authorization');
    $this->assertStringContainsString('Bearer', $www);
    $this->assertStringContainsString('error="insufficient_scope"', $www);

    // The scope attribute advertises every available mcp:* scope
    // so the client can request them on re-authorization.
    foreach (McpScope::values() as $scope) {
        $this->assertStringContainsString($scope, $www);
    }
});

test('token with only non mcp scopes is refused', function () {
    Passport::actingAs(User::factory()->create(), ['some:other:scope', 'another:one']);

    $request = Request::create('/mcp/snipe-it', 'POST');
    $request->setUserResolver(fn () => auth()->user());

    $response = runMiddleware($request);

    expect($response->getStatusCode())->toBe(401);
    $this->assertStringContainsString('error="insufficient_scope"', $response->headers->get('WWW-Authenticate'));
});

test('401 body is structured json with scope advertisement', function () {
    Passport::actingAs(User::factory()->create(), []);

    $request = Request::create('/mcp/snipe-it', 'POST');
    $request->setUserResolver(fn () => auth()->user());

    $response = runMiddleware($request);

    $body = json_decode($response->getContent(), true);

    expect($body['error'])->toBe('insufficient_scope');
    expect($body)->toHaveKey('error_description');
    expect($body)->toHaveKey('scope');

    // Space-separated scope advertisement per OAuth 2.0 standard.
    $advertised = explode(' ', $body['scope']);
    expect($advertised)->toBe(McpScope::values());
});
