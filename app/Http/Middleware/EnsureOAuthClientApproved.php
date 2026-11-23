<?php

namespace App\Http\Middleware;

use App\Models\OAuthClient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks OAuth authorize + token requests for clients that haven't
 * been approved by a superuser yet. Companion to the approval
 * workflow that marks newly-DCR-registered clients as pending when
 * their redirect URI isn't on the auto-approve list.
 *
 * Behavior per path:
 *   /oauth/authorize (GET or POST): renders an HTML page explaining
 *     the client is awaiting / was denied approval, so a phished
 *     end-user doesn't just see a blank 403.
 *   /oauth/token (POST): returns an OAuth-standard JSON
 *     error=unauthorized_client so compliant OAuth clients surface
 *     the specific failure instead of a generic 500.
 *
 * Attached to the web middleware group in bootstrap/app.php. Non-OAuth
 * routes pass through untouched (early-exit on path check).
 */
class EnsureOAuthClientApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->isOAuthFlowRoute($request)) {
            return $next($request);
        }

        $clientId = $this->resolveClientId($request);
        if ($clientId === null) {
            // No client_id present in the request shape we know how to
            // read. Let Passport's own validation surface the missing-
            // parameter error so the operator gets the OAuth-standard
            // response rather than our client-not-approved page.
            return $next($request);
        }

        $client = OAuthClient::find($clientId);
        if ($client === null || $client->isApproved()) {
            return $next($request);
        }

        return $this->denyResponse($request, $client);
    }

    private function isOAuthFlowRoute(Request $request): bool
    {
        $path = trim($request->path(), '/');

        return str_starts_with($path, 'oauth/authorize')
            || $path === 'oauth/token';
    }

    /**
     * Pull client_id from whichever surface the current OAuth step
     * carries it on. Authorize uses the query string (GET) and form
     * body (POST). Token exchange uses form body and may fall back to
     * HTTP Basic (client_id in the username field) for confidential
     * clients.
     */
    private function resolveClientId(Request $request): ?string
    {
        $clientId = $request->input('client_id');
        if (is_string($clientId) && $clientId !== '') {
            return $clientId;
        }

        $basic = $request->getUser();
        if (is_string($basic) && $basic !== '') {
            return $basic;
        }

        return null;
    }

    private function denyResponse(Request $request, OAuthClient $client): Response
    {
        $isToken = trim($request->path(), '/') === 'oauth/token';
        $message = $client->isRejected()
            ? trans('admin/settings/mcp.client_rejected_message')
            : trans('admin/settings/mcp.client_pending_message');

        if ($isToken || $request->expectsJson()) {
            return response()->json([
                'error' => 'unauthorized_client',
                'error_description' => $message,
            ], 403);
        }

        return response()->view('mcp.client-not-approved', [
            'client' => $client,
            'message' => $message,
        ], 403);
    }
}
