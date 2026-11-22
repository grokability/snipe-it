<?php

namespace App\Http\Middleware;

use App\Enums\ActionSource;
use App\Models\Actionlog;
use Closure;
use Illuminate\Http\Request;

/**
 * Sets every `action_logs` row written during an MCP tool call with
 * `action_source = mcp` and `user_agent = Snipe-IT MCP` so admins
 * reading the activity report can tell MCP-driven mutations apart
 * from the HTTP client.
 */
class SetMcpActionContext
{
    public function handle(Request $request, Closure $next)
    {
        return Actionlog::withActionSource(ActionSource::Mcp, fn() => $next($request));
    }
}
