<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Passport\Token;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stops OAuth tokens whose only scopes are mcp:* from being used as
 * full-power REST API credentials. Companion fix for the MCP consent
 * flow issue where a user approving "Authorize Claude" for mcp:use
 * would end up with a token that could also call every /api/v1/*
 * endpoint with their full permissions.
 *
 * Runs on the 'api' middleware group so any route Snipe-IT publishes
 * under /api is covered, not just /api/v1. Short-circuits for:
 *   - Unauthenticated requests (auth middleware already handles them)
 *   - OIDC-token requests (not Passport, no scope concept)
 *   - Personal access tokens (no scopes = full-power by design)
 *   - Tokens that carry any non-mcp:* scope (hybrid tokens stay usable
 *     against the scopes they legitimately hold)
 */
class RejectMcpOnlyTokensOnRestApi
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null) {
            return $next($request);
        }

        $token = $user->token();
        if (! $token instanceof Token) {
            // Not a Passport-issued token (session cookie, OIDC bearer,
            // etc.). Nothing to scope-check here.
            return $next($request);
        }

        $scopes = $token->scopes ?? [];
        if ($scopes === []) {
            // Empty scopes = full-power token (personal access tokens
            // and legacy pre-scope tokens). Allow.
            return $next($request);
        }

        $onlyMcp = true;
        foreach ($scopes as $scope) {
            if (! is_string($scope) || ! str_starts_with($scope, 'mcp:')) {
                $onlyMcp = false;
                break;
            }
        }

        if (! $onlyMcp) {
            return $next($request);
        }

        return response()->json([
            'status' => 'error',
            'messages' => trans('admin/settings/mcp.rest_api_rejects_mcp_only_token'),
            'payload' => null,
        ], 403);
    }
}
