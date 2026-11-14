<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\GetActivityLogTool;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use Laravel\Mcp\Request;
use Tests\TestCase;

/**
 * MCP regression coverage for GHSA-m955-p5pg-78mv.
 *
 * GetActivityLogTool reads action_logs through the Actionlog model, so
 * the fix to Company::scopeCompanyables (delegated to Actionlog::
 * applyCrossCompanyScope) applies to MCP callers the same way it
 * applies to HTTP API callers. This test pins that assumption by
 * driving the tool directly and asserting a cross-tenant NULL-company
 * Asset log does not come back in the MCP response.
 *
 * ListHistoryTool is not covered here. It runs a per-item history
 * Gate before the actionlog query, so cross-tenant access is blocked
 * at the policy layer before scope ever runs. GetActivityLogTool has
 * no per-item gate and is the right surface to pin the scope on.
 */
class GetActivityLogToolFmcsScopeTest extends TestCase
{
    public function test_cross_tenant_null_company_asset_log_is_hidden_from_mcp_activity_feed(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $viewer = $companyB->users()->save(
            User::factory()->make([
                'permissions' => json_encode(['activity.view' => '1']),
            ])
        );

        $asset = Asset::factory()->create(['company_id' => $companyA->id]);
        $crossTenantLog = Actionlog::factory()->create([
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'action_type' => 'update',
            'company_id' => null,
        ]);

        $this->actingAs($viewer);

        $response = (new GetActivityLogTool)->handle(new Request([
            'item_type' => Asset::class,
            'item_id' => $asset->id,
        ]));

        $content = $response->getStructuredContent();

        $returnedIds = array_column($content['activity'], 'id');

        $this->assertNotContains(
            $crossTenantLog->id,
            $returnedIds,
            'GetActivityLogTool returned a cross-tenant NULL-company Asset action_log. The Company::scopeCompanyables fix is not applying through the MCP path.'
        );
    }
}
