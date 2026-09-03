<?php

namespace Tests\Feature\SyncAdapters;

use App\Models\User;
use Tests\TestCase;

/**
 * Confirms every interactive control on the shared sync-adapter form
 * frame is disabled when the app is in demo mode. Prevents a demo user
 * from wiring their real MDM into the public demo instance by typing
 * credentials, flipping the active toggle, or picking mappings.
 *
 * Belongs at the SyncAdapters top level (not under any per-adapter
 * directory) because the shell is shared: locking it locks every
 * adapter's settings tab at once.
 */
class DemoModeLocksFormTest extends TestCase
{
    public function test_demo_mode_disables_every_interactive_control_on_the_adapter_form()
    {
        config()->set('app.lock_passwords', true);

        $html = $this->actingAs(User::factory()->superuser()->create())
            ->get(route('settings.adapters.index'))
            ->assertOk()
            ->getContent();

        // Credential + URL inputs render as XXXX-masked disabled text
        // inputs when locked (so the placeholder ceremony is visible).
        // Active and heartbeat checkboxes forward :disabled through
        // <x-form.checkbox-row>. Mapping selects forward :disabled
        // through <x-input.select> via attribute merge.
        $this->assertStringContainsString('name="fleet_url"', $html);
        $this->assertMatchesRegularExpression('/name="fleet_url"[^>]*disabled/', $html);
        $this->assertMatchesRegularExpression('/name="fleet_token"[^>]*disabled/', $html);
        $this->assertMatchesRegularExpression('/name="fleet_active"[^>]*disabled/', $html);
        $this->assertMatchesRegularExpression('/name="fleet_log_heartbeats"[^>]*disabled/', $html);
        $this->assertMatchesRegularExpression('/name="fleet_mapping\[hostname\]"[^>]*disabled/', $html);

        // Same coverage for the key-pair auth shape (Addigy). Different
        // partial, same shell, so locking must apply symmetrically.
        $this->assertMatchesRegularExpression('/name="addigy_key_id"[^>]*disabled/', $html);
        $this->assertMatchesRegularExpression('/name="addigy_key_secret"[^>]*disabled/', $html);
        $this->assertMatchesRegularExpression('/name="addigy_active"[^>]*disabled/', $html);

        // Save button lives in the footer, disabled by the index page
        // directly. Sync Now lives per-pane in the sync-adapter-panel
        // component and the panel disables it when locked, so every
        // pane's sync button carries the disabled attribute.
        $this->assertMatchesRegularExpression('/id="adapter-save-button"[^>]*disabled/', $html);
        $this->assertStringContainsString(trans('admin/settings/general.sync_adapter_sync_now'), $html);

        // Walk every <button>...</button> in the rendered HTML: any
        // that contains the sync-now label must also carry a disabled
        // attribute. Catches a regression that would leave the button
        // clickable in demo mode.
        $label = trans('admin/settings/general.sync_adapter_sync_now');
        preg_match_all('/<button\b(?<attrs>[^>]*)>(?<body>.*?)<\/button>/s', $html, $buttons, PREG_SET_ORDER);
        $syncButtonsSeen = 0;
        foreach ($buttons as $match) {
            if (! str_contains($match['body'], $label)) {
                continue;
            }
            $syncButtonsSeen++;
            $this->assertStringContainsString('disabled', $match['attrs'], 'Sync-now button rendered without disabled attribute in demo mode.');
        }
        $this->assertGreaterThan(0, $syncButtonsSeen);
    }

    public function test_non_demo_mode_leaves_interactive_controls_enabled()
    {
        config()->set('app.lock_passwords', false);

        $html = $this->actingAs(User::factory()->superuser()->create())
            ->get(route('settings.adapters.index'))
            ->assertOk()
            ->getContent();

        // Guardrail: if the shell ever accidentally hardcodes `disabled`
        // instead of gating on the config, this catches it.
        $this->assertDoesNotMatchRegularExpression('/name="fleet_url"[^>]*disabled/', $html);
        $this->assertDoesNotMatchRegularExpression('/name="fleet_active"[^>]*disabled/', $html);
        $this->assertDoesNotMatchRegularExpression('/name="fleet_mapping\[hostname\]"[^>]*disabled/', $html);
    }
}
