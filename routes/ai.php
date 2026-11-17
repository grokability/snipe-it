<?php

use App\Enums\McpScope;
use App\Http\Middleware\CheckUserIsActivated;
use App\Http\Middleware\EnforceApiTwoFactorEnrollment;
use App\Http\Middleware\ForceJsonAcceptableOnMcp;
use App\Mcp\Servers\SnipeMCPServer;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;

// OAuth discovery endpoints. Laravel MCP's defaults (registered by
// Mcp::oauthRoutes() below) advertise only `mcp:use` as the supported
// scope, which means OAuth clients (ChatGPT, Claude Desktop, etc.)
// request only `mcp:use` and the resulting token cannot satisfy any
// per-tool scope check in HasMcpScope.
//
// Four discovery endpoints to cover:
//   /.well-known/oauth-authorization-server
//   /.well-known/oauth-authorization-server/{path}
//   /.well-known/oauth-protected-resource
//   /.well-known/oauth-protected-resource/{path}
//
// The nested `{path}` variants matter because clients discovering a
// specific MCP endpoint (e.g. /mcp/snipe-it) fetch
// /.well-known/oauth-protected-resource/mcp/snipe-it to get metadata
// scoped to that endpoint, including a resource identifier that
// matches the endpoint URL. All four need to advertise our scopes.
//
// Registration order matters: Laravel's router does
// last-registered-wins for duplicate URI patterns, so these overrides
// go AFTER Mcp::oauthRoutes() below. The non-nested root routes have
// an exists-check inside the MCP registrar (so order wouldn't strictly
// matter there), but the nested `{path}` variants are registered
// unconditionally, so our overrides have to come last to win.
$authServerMetadata = function (): array {
    $metadata = [
        'issuer' => config('mcp.authorization_server') ?? url('/'),
        'authorization_endpoint' => route('passport.authorizations.authorize'),
        'token_endpoint' => route('passport.token'),
        'response_types_supported' => ['code'],
        'code_challenge_methods_supported' => ['S256'],
        'scopes_supported' => McpScope::values(),
        'grant_types_supported' => ['authorization_code', 'refresh_token'],
        'token_endpoint_auth_methods_supported' => ['none', 'client_secret_basic', 'client_secret_post'],
    ];

    // Dynamic client registration is registered by Mcp::oauthRoutes()
    // with the `registration` flag. If that flag is ever disabled, the
    // route won't exist and we should omit the field rather than throw.
    if (Route::has('mcp.oauth.register')) {
        $metadata['registration_endpoint'] = route('mcp.oauth.register');
    }

    return $metadata;
};

$protectedResourceMetadata = function (string $path = ''): array {
    return [
        'resource' => url('/'.ltrim($path, '/')),
        'authorization_servers' => [config('mcp.authorization_server') ?? url('/')],
        'scopes_supported' => McpScope::values(),
    ];
};

// Let Laravel MCP register its OAuth routes first (including the
// unconditional nested variants), then overwrite the four discovery
// endpoints with ours. Laravel's router matches on last-registered-
// wins for the same URI pattern, so this order guarantees ours win
// even for the nested routes that Laravel MCP would otherwise own.
Mcp::oauthRoutes();

Route::get('/.well-known/oauth-authorization-server', fn () => response()->json($authServerMetadata()))
    ->name('mcp.oauth.authorization-server');

Route::get('/.well-known/oauth-authorization-server/{path}', fn () => response()->json($authServerMetadata()))
    ->where('path', '.*')
    ->name('mcp.oauth.authorization-server.nested');

Route::get('/.well-known/oauth-protected-resource', fn () => response()->json($protectedResourceMetadata()))
    ->name('mcp.oauth.protected-resource');

Route::get('/.well-known/oauth-protected-resource/{path}', fn (string $path) => response()->json($protectedResourceMetadata($path)))
    ->where('path', '.*')
    ->name('mcp.oauth.protected-resource.nested');

// Middleware chain mirrors the protections on the regular api group,
// with one MCP-specific header shim up front:
//   ForceJsonAcceptableOnMcp       Forces Accept: application/json so
//                                  auth failures return JSON 401 +
//                                  WWW-Authenticate instead of
//                                  redirecting to /login. MCP clients
//                                  often omit the Accept header or set
//                                  it to text/event-stream, which
//                                  would otherwise trip the default
//                                  302-to-login path.
//   auth:api                       Passport TokenGuard. Validates the
//                                  Bearer token, attaches the user.
//   CheckUserIsActivated           Rejects deactivated users whose
//                                  tokens would otherwise keep working.
//   EnforceApiTwoFactorEnrollment  Rejects users who have not enrolled
//                                  when the install forces 2FA.
//   mcp.scope.handshake            Rejects tokens with no mcp:* scopes,
//                                  returning HTTP 401 + WWW-Authenticate
//                                  so compliant OAuth clients can
//                                  re-initiate authorization with the
//                                  needed scopes.
//   api-throttle:api               Sets JSON response headers + rate limits.
Mcp::web('/mcp/snipe-it', SnipeMCPServer::class)->middleware([
    ForceJsonAcceptableOnMcp::class,
    'auth:api',
    CheckUserIsActivated::class,
    EnforceApiTwoFactorEnrollment::class,
    'mcp.scope.handshake',
    'api-throttle:api',
]);
