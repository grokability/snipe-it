<?php

use App\Mcp\Tools\GetActivityLogTool;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use Laravel\Mcp\Request;

test('cross tenant null company asset log is hidden from mcp activity feed', function () {
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

    expect($returnedIds)->not->toContain($crossTenantLog->id, 'GetActivityLogTool returned a cross-tenant NULL-company Asset action_log. The Company::scopeCompanyables fix is not applying through the MCP path.');
});
