<?php

namespace App\Http\Middleware;

use App\Enums\McpScope;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * MCP session-level scope gate.
 *
 * Runs on /mcp/* right after auth:api. Confirms the acting Passport
 * token carries at least one scope defined in McpScope. The framework-
 * standard `mcp:use` (McpScope::Session) satisfies this check, so a
 * minimally-scoped token can at least connect and call tools/list
 * to discover what capability scopes each tool requires. Capability
 * enforcement still happens per-tool.
 *
 * If a token carries zero mcp-scopes at all, returns HTTP 401 with a
 * WWW-Authenticate header advertising the full scope set. Compliant
 * OAuth clients see that header and re-initiate the authorization
 * flow to request the needed scopes.
 *
 * The two gates are deliberately layered:
 *
 *   This middleware (session level)
 *     Returns 401 + WWW-Authenticate when the token carries no
 *     McpScope at all. Triggers client-side reauthorization.
 *     Required because tool-level JSON-RPC errors are wrapped in
 *     HTTP 200 responses and give OAuth clients no signal to re-auth.
 *
 *   HasMcpScope::scopeDenied() (per-tool level)
 *     Exact-match check per tool call. A token with McpScope::Session
 *     only (or with a capability scope that does not match the called
 *     tool) gets a scope_denied tool error with a structured
 *     `_meta["mcp/www_authenticate"]` hint pointing at the specific
 *     scope needed for that tool. Clients can then re-authorize for
 *     the elevated scope.
 *
 * actingAs() in tests sets a user on the guard without attaching a
 * Passport token, so currentAccessToken() returns null. Skip the
 * check in that case. Production requests always have a token via
 * auth:api's TokenGuard, so enforcement is live for every real call.
 */
class EnforceMcpScopeHandshake
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! method_exists($user, 'currentAccessToken')) {
            return $next($request);
        }

        $token = $user->currentAccessToken();
        if ($token === null) {
            return $next($request);
        }

        // $user->tokenCan() resolves against the acting token's
        // ->can($scope) method, which returns true when the scope is
        // in the token's scopes OR when the token carries the '*'
        // wildcard. Iterating McpScope::values() and shortcutting on
        // the first match is the most portable way to check "has any
        // mcp:* scope" across both Passport Token models (DB-backed)
        // and AccessToken value objects (PSR-7-attribute-backed, used
        // by Passport::actingAs in tests). Both implement ->can().
        foreach (McpScope::values() as $scope) {
            if ($user->tokenCan($scope)) {
                return $next($request);
            }
        }

        $availableScopes = implode(' ', McpScope::values());

        return response()->json(
            [
                'error' => 'insufficient_scope',
                'error_description' => trans('mcp.scope_handshake_required'),
                'scope' => $availableScopes,
            ],
            Response::HTTP_UNAUTHORIZED,
            [
                'WWW-Authenticate' => sprintf(
                    'Bearer realm="mcp", error="insufficient_scope", scope="%s"',
                    $availableScopes,
                ),
            ],
        );
    }
}
