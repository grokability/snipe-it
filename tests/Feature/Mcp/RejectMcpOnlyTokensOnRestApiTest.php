<?php

namespace Tests\Feature\Mcp;

use App\Enums\McpScope;
use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * Pins the RejectMcpOnlyTokensOnRestApi middleware against the real
 * Passport 13 bearer path, where the TokenGuard attaches an
 * AccessToken value object instead of a Token eloquent model.
 *
 * Before this fix the middleware did `instanceof Token`, which was
 * never true for bearer tokens, so every MCP-scoped token kept full
 * REST write access. These tests exercise /api/v1/users/me because
 * it's authenticated-only, needs no model setup, and a 200 response
 * proves the token slipped through.
 */
class RejectMcpOnlyTokensOnRestApiTest extends TestCase
{
    public function test_mcp_only_token_is_refused_on_rest_api(): void
    {
        $user = User::factory()->superuser()->create();
        Passport::actingAs($user, [McpScope::Session->value, McpScope::Read->value]);

        $this->getJson(route('api.users.me'))
            ->assertForbidden()
            ->assertJsonPath('status', 'error');
    }

    public function test_mcp_write_only_token_is_refused_on_rest_api(): void
    {
        $user = User::factory()->superuser()->create();
        Passport::actingAs($user, [McpScope::Session->value, McpScope::Write->value]);

        $this->getJson(route('api.users.me'))->assertForbidden();
    }

    public function test_hybrid_token_with_non_mcp_scope_is_allowed(): void
    {
        $user = User::factory()->superuser()->create();
        Passport::actingAs($user, [McpScope::Session->value, 'users.view']);

        $this->getJson(route('api.users.me'))->assertOk();
    }

    public function test_scopeless_token_is_allowed(): void
    {
        $user = User::factory()->superuser()->create();
        Passport::actingAs($user, []);

        $this->getJson(route('api.users.me'))->assertOk();
    }
}
