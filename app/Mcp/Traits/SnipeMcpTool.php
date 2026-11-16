<?php

namespace App\Mcp\Traits;

use App\Enums\McpScope;
use App\Mcp\Support\ToolScopeMap;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

/**
 * Project-level conventions every Snipe-IT MCP tool mixes in. Four
 * surfaces today, driven by ToolScopeMap and the MCP spec:
 *
 *   1. toArray() override: emits `_meta.securitySchemes` on each
 *      tool's `tools/list` entry so OAuth-aware MCP clients (ChatGPT,
 *      Claude Desktop) can discover which scope each tool needs and
 *      request the right scopes at authorize time.
 *
 *   2. scopeDenied(): short-circuits a tool-level call with a
 *      scope_denied tool error when the acting token lacks the right
 *      scope. Also attaches `_meta["mcp/www_authenticate"]` to the
 *      error so a capable client can prompt the user to re-authorize
 *      with the missing scope.
 *
 *   3. Test short-circuit: when there is no currentAccessToken (plain
 *      actingAs() tests, no Passport TokenGuard), the check returns
 *      null so the existing MCP test suite keeps working without
 *      every test having to mint a real token.
 *
 *   4. structuredResponse(): dual-content response builder that
 *      satisfies the MCP spec requirement that tools returning
 *      structuredContent also emit a functionally equivalent copy in
 *      the text `content` field so clients that read only the text
 *      side still receive the payload.
 */
trait SnipeMcpTool
{
    /**
     * Override Tool::toArray to attach _meta.securitySchemes for this
     * tool's required scope. Called by Laravel MCP's tools/list
     * method when the client discovers available tools.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $result = parent::toArray();

        $scope = ToolScopeMap::for(static::class);
        if ($scope === null) {
            return $result;
        }

        $meta = $result['_meta'] ?? [];
        $meta['securitySchemes'] = [
            [
                'type' => 'oauth2',
                'scopes' => [$scope->value],
            ],
        ];
        $result['_meta'] = $meta;

        return $result;
    }

    protected function scopeDenied(McpScope $required): ?ResponseFactory
    {
        $user = auth()->user();
        if ($user === null) {
            return null;
        }

        // currentAccessToken() comes from Passport's HasApiTokens trait
        // on the User model. For a real auth:api request it returns the
        // active token. For a plain actingAs() test context (no
        // TokenGuard, no attached token) it returns null, and we treat
        // that as "skip scope enforcement" so the existing MCP suite
        // keeps working without a token-issuance step per test.
        if ($user->currentAccessToken() === null) {
            return null;
        }

        if ($user->tokenCan($required->value)) {
            return null;
        }

        // WWW-Authenticate challenge inside the MCP response.
        // OpenAI documents this as `_meta["mcp/www_authenticate"]` for
        // scope-elevation prompts. The same string shape as an HTTP
        // WWW-Authenticate header so a client that already knows how
        // to parse Bearer challenges can reuse that path.
        $challenge = sprintf(
            'Bearer realm="mcp", error="insufficient_scope", scope="%s"',
            $required->value,
        );

        $response = Response::make(Response::error(trans('mcp.scope_denied', ['scope' => $required->value])));
        $response->withMeta([
            'mcp/www_authenticate' => $challenge,
            'required_scope' => $required->value,
        ]);

        return $response;
    }

    /**
     * Build a response that mirrors the structured payload into the
     * text content block alongside structuredContent. The MCP spec says
     * a tool returning structuredContent SHOULD also return a
     * functionally equivalent copy in `content`, so clients that read
     * only the text side (Claude Desktop, claude.ai at time of writing)
     * get the same data as clients that read structuredContent
     * (Claude Code, MCP Inspector).
     *
     * @param  array<string, mixed>  $structured
     */
    protected function structuredResponse(string $summary, array $structured): ResponseFactory
    {
        return Response::make([
            Response::text($summary),
            Response::text(json_encode($structured, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)),
        ])->withStructuredContent($structured);
    }
}
