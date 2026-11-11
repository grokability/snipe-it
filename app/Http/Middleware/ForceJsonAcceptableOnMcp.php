<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Forces the request to advertise Accept: application/json before the
 * auth middleware runs.
 *
 * MCP clients (ChatGPT's connector in particular) POST to /mcp/* with
 * JSON bodies but often omit the Accept header or set it to
 * text/event-stream for SSE. Without Accept: application/json,
 * Laravel's default AuthenticationException handler sees the request
 * as a browser request and redirects to /login with a 302, which
 * OAuth clients cannot parse. The redirect appears to them as either
 * a successful request or a cryptic failure, and the OAuth reauth
 * flow never triggers.
 *
 * Setting Accept: application/json up front flips
 * Request::expectsJson() to true for the rest of the stack, which
 * makes the AuthenticationException handler return the JSON 401 +
 * WWW-Authenticate response compliant OAuth clients expect.
 *
 * Only applied on the MCP route. The regular REST API callers
 * already set Accept correctly.
 */
class ForceJsonAcceptableOnMcp
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
