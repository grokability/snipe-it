<?php

namespace Tests\Feature\Mcp;

use App\Enums\McpScope;
use App\Mcp\Tools\DeleteAssetTool;
use App\Mcp\Tools\ShowAssetTool;
use App\Models\Asset;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * Pins the per-tool Passport scope enforcement wired via HasMcpScope.
 *
 * The suite covers three behaviors:
 *   1. A token that carries the required scope passes the check and
 *      the tool's existing policy gates run as before.
 *   2. A token that carries a different scope (even multiple) is
 *      refused with a scope_denied error. Exact-match semantics.
 *   3. Test contexts that use the plain `actingAs()` helper, which
 *      does not attach a Passport token, continue to work. This lets
 *      the existing MCP test suite call tools without a token-issuance
 *      step while real production requests still get the enforcement.
 */
class McpScopeEnforcementTest extends TestCase
{
    private function callShow(array $args): ResponseFactory
    {
        return (new ShowAssetTool)->handle(new Request($args));
    }

    private function callDelete(array $args): ResponseFactory
    {
        return (new DeleteAssetTool)->handle(new Request($args));
    }

    public function test_read_scope_is_sufficient_for_show_asset(): void
    {
        $asset = Asset::factory()->create();
        Passport::actingAs(User::factory()->viewAssets()->create(), [McpScope::Read->value]);

        $response = $this->callShow(['id' => $asset->id]);

        $this->assertFalse($response->responses()->first()->isError());
    }

    public function test_write_scope_cannot_call_a_read_tool(): void
    {
        $asset = Asset::factory()->create();
        Passport::actingAs(User::factory()->viewAssets()->create(), [McpScope::Write->value]);

        $response = $this->callShow(['id' => $asset->id]);

        $this->assertTrue($response->responses()->first()->isError());
    }

    public function test_delete_scope_cannot_call_a_read_tool_even_with_full_permissions(): void
    {
        $asset = Asset::factory()->create();
        Passport::actingAs(User::factory()->superuser()->create(), [McpScope::Delete->value]);

        $response = $this->callShow(['id' => $asset->id]);

        $this->assertTrue($response->responses()->first()->isError());
    }

    public function test_read_scope_cannot_call_a_delete_tool(): void
    {
        $asset = Asset::factory()->create();
        Passport::actingAs(User::factory()->superuser()->create(), [McpScope::Read->value]);

        $response = $this->callDelete(['id' => $asset->id]);

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'deleted_at' => null]);
    }

    public function test_delete_scope_plus_delete_permission_can_delete(): void
    {
        $asset = Asset::factory()->create();
        Passport::actingAs(User::factory()->deleteAssets()->create(), [McpScope::Delete->value]);

        $response = $this->callDelete(['id' => $asset->id]);

        $this->assertFalse($response->responses()->first()->isError());
        $this->assertSoftDeleted('assets', ['id' => $asset->id]);
    }

    public function test_multiple_scopes_on_token_allow_matching_tools(): void
    {
        $asset = Asset::factory()->create();
        Passport::actingAs(
            User::factory()->deleteAssets()->viewAssets()->create(),
            [McpScope::Read->value, McpScope::Delete->value],
        );

        $this->assertFalse($this->callShow(['id' => $asset->id])->responses()->first()->isError());
        $this->assertFalse($this->callDelete(['id' => $asset->id])->responses()->first()->isError());
    }

    /**
     * Regular actingAs() path. No Passport token attached. The MCP
     * suite as a whole relies on this short-circuit so individual
     * tool tests do not have to set up a token for every case.
     */
    public function test_acting_as_without_passport_skips_scope_check(): void
    {
        $asset = Asset::factory()->create();
        $this->actingAs(User::factory()->viewAssets()->create());

        $response = $this->callShow(['id' => $asset->id]);

        $this->assertFalse($response->responses()->first()->isError());
    }

    public function test_token_with_no_mcp_scopes_is_refused(): void
    {
        $asset = Asset::factory()->create();
        Passport::actingAs(User::factory()->superuser()->create(), []);

        $response = $this->callShow(['id' => $asset->id]);

        $this->assertTrue($response->responses()->first()->isError());
    }

    public function test_session_scope_alone_cannot_call_a_capability_tool(): void
    {
        // McpScope::Session (`mcp:use`) is the connect-and-discover
        // scope. It passes the handshake middleware so tools/list
        // works, but per-tool exact-match still blocks capability
        // calls. This is the layered design: a session-only client
        // can enumerate but cannot execute.
        $asset = Asset::factory()->create();
        Passport::actingAs(User::factory()->viewAssets()->create(), [McpScope::Session->value]);

        $response = $this->callShow(['id' => $asset->id]);

        $this->assertTrue($response->responses()->first()->isError());
    }
}
