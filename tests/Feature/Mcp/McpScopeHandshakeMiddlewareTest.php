<?php

namespace Tests\Feature\Mcp;

use App\Enums\McpScope;
use App\Http\Middleware\EnforceMcpScopeHandshake;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/**
 * Covers the route-level session gate that fires HTTP 401 +
 * WWW-Authenticate when a Passport token carries zero mcp:* scopes.
 *
 * The 401 + WWW-Authenticate header is the signal OAuth-compliant
 * clients look for to re-initiate the authorization flow. Pre-scope
 * tokens (and any new token issued without an mcp:* scope) hit this
 * branch, which is what tells a client like Claude Desktop or
 * ChatGPT to prompt the user to re-authorize with the scopes they
 * need.
 */
class McpScopeHandshakeMiddlewareTest extends TestCase
{
    private function runMiddleware(Request $request): Response
    {
        return (new EnforceMcpScopeHandshake)->handle($request, function () {
            return response('through', 200);
        });
    }

    public function test_request_with_no_token_passes_through(): void
    {
        $request = Request::create('/mcp/snipe-it', 'POST');

        $response = $this->runMiddleware($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('through', $response->getContent());
    }

    public function test_token_with_mcp_scope_passes_through(): void
    {
        Passport::actingAs(User::factory()->create(), [McpScope::Read->value]);

        $request = Request::create('/mcp/snipe-it', 'POST');
        $request->setUserResolver(fn () => auth()->user());

        $response = $this->runMiddleware($request);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_token_with_wildcard_scope_passes_through(): void
    {
        Passport::actingAs(User::factory()->create(), ['*']);

        $request = Request::create('/mcp/snipe-it', 'POST');
        $request->setUserResolver(fn () => auth()->user());

        $response = $this->runMiddleware($request);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_session_scope_alone_passes_handshake(): void
    {
        // McpScope::Session (`mcp:use`) is the connectivity gate.
        // A token with only Session passes the handshake so the
        // client can call tools/list and discover what capability
        // scopes it needs to elevate to. Per-tool enforcement in
        // HasMcpScope still blocks capability calls on a session-
        // only token.
        Passport::actingAs(User::factory()->create(), [McpScope::Session->value]);

        $request = Request::create('/mcp/snipe-it', 'POST');
        $request->setUserResolver(fn () => auth()->user());

        $response = $this->runMiddleware($request);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_token_with_no_mcp_scopes_is_refused_with_401_and_www_authenticate(): void
    {
        Passport::actingAs(User::factory()->create(), []);

        $request = Request::create('/mcp/snipe-it', 'POST');
        $request->setUserResolver(fn () => auth()->user());

        $response = $this->runMiddleware($request);

        $this->assertSame(401, $response->getStatusCode());

        $www = $response->headers->get('WWW-Authenticate');
        $this->assertNotNull($www, 'WWW-Authenticate header is required for OAuth clients to re-initiate authorization');
        $this->assertStringContainsString('Bearer', $www);
        $this->assertStringContainsString('error="insufficient_scope"', $www);

        // The scope attribute advertises every available mcp:* scope
        // so the client can request them on re-authorization.
        foreach (McpScope::values() as $scope) {
            $this->assertStringContainsString($scope, $www);
        }
    }

    public function test_token_with_only_non_mcp_scopes_is_refused(): void
    {
        Passport::actingAs(User::factory()->create(), ['some:other:scope', 'another:one']);

        $request = Request::create('/mcp/snipe-it', 'POST');
        $request->setUserResolver(fn () => auth()->user());

        $response = $this->runMiddleware($request);

        $this->assertSame(401, $response->getStatusCode());
        $this->assertStringContainsString('error="insufficient_scope"', $response->headers->get('WWW-Authenticate'));
    }

    public function test_401_body_is_structured_json_with_scope_advertisement(): void
    {
        Passport::actingAs(User::factory()->create(), []);

        $request = Request::create('/mcp/snipe-it', 'POST');
        $request->setUserResolver(fn () => auth()->user());

        $response = $this->runMiddleware($request);

        $body = json_decode($response->getContent(), true);

        $this->assertSame('insufficient_scope', $body['error']);
        $this->assertArrayHasKey('error_description', $body);
        $this->assertArrayHasKey('scope', $body);

        // Space-separated scope advertisement per OAuth 2.0 standard.
        $advertised = explode(' ', $body['scope']);
        $this->assertSame(McpScope::values(), $advertised);
    }
}
