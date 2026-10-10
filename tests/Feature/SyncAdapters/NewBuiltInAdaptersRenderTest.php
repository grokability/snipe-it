<?php

namespace Tests\Feature\SyncAdapters;

use App\Models\User;
use Tests\TestCase;

/**
 * Smoke coverage for the three newest built-in adapters (Mosyle, Meraki
 * Systems Manager, Omnissa Workspace ONE). Each is a schema-only
 * declaration on top of SyncAdapter. the storage / save /
 * encryption cycle is already covered by FleetSettingsPageTest,
 * AddigySettingsPageTest, and IntuneSettingsPageTest for the three
 * distinct schema shapes (single-secret, key-pair, mixed). This test
 * just verifies each new adapter is registered with the SyncAdapter::allTypes
 * and its schema-declared labels appear on the settings page.
 */
class NewBuiltInAdaptersRenderTest extends TestCase
{
    public function test_mosyle_manager_renders_with_its_schema_label()
    {
        $html = $this->actingAs(User::factory()->superuser()->create())
            ->get(route('settings.adapters.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Mosyle Manager', $html);
        // Schema field names from MosyleAdapter::settingsSchema() post-#19790.
        // Mosyle Manager v2 requires accessToken + email + password on login.
        $this->assertMatchesRegularExpression('/name="mosyle_access_token"/', $html);
        $this->assertMatchesRegularExpression('/name="mosyle_email"/', $html);
        $this->assertMatchesRegularExpression('/name="mosyle_password"/', $html);
    }

    public function test_mosyle_business_renders_with_its_schema_label()
    {
        // Mosyle Business is a separate adapter from Mosyle Manager
        // because the Business v1 API has a different auth model,
        // different endpoint shape, and a different OS enum. The
        // catalog auto-discovers it via SyncAdapter::allTypes()'s
        // glob scan of app/SyncAdapters/*/Adapter.php, so this test
        // guards against a slug-collision regression that would hide
        // either adapter from the picker.
        $html = $this->actingAs(User::factory()->superuser()->create())
            ->get(route('settings.adapters.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Mosyle Business', $html);
        $this->assertMatchesRegularExpression('/name="mosyle_business_access_token"/', $html);
        $this->assertMatchesRegularExpression('/name="mosyle_business_email"/', $html);
        $this->assertMatchesRegularExpression('/name="mosyle_business_password"/', $html);
    }

    public function test_meraki_systems_manager_renders_with_both_schema_fields()
    {
        $html = $this->actingAs(User::factory()->superuser()->create())
            ->get(route('settings.adapters.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Meraki Systems Manager', $html);
        // Secret schema field
        $this->assertMatchesRegularExpression('/name="meraki_sm_api_key"/', $html);
        // Non-secret schema field
        $this->assertMatchesRegularExpression('/name="meraki_sm_organization_id"/', $html);
    }

    public function test_workspace_one_renders_with_all_three_schema_fields()
    {
        $html = $this->actingAs(User::factory()->superuser()->create())
            ->get(route('settings.adapters.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Omnissa Workspace ONE', $html);
        $this->assertMatchesRegularExpression('/name="workspace_one_tenant_code"/', $html);
        $this->assertMatchesRegularExpression('/name="workspace_one_client_id"/', $html);
        $this->assertMatchesRegularExpression('/name="workspace_one_client_secret"/', $html);
    }
}
