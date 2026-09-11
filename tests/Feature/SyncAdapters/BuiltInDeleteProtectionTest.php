<?php

namespace Tests\Feature\SyncAdapters;

use App\Models\SyncAdapterInstance;
use App\Models\User;
use Tests\TestCase;

/**
 * Built-in adapters (Fleet, Kandji, Intune, and the 8 others seeded by
 * the migration) must never be deletable. If a user doesn't want to
 * use one, they leave it inactive. the record has to persist because
 * asset_external_sources.source may still reference it after past syncs.
 *
 * Protection lives in three places: the settings-page delete button is
 * hidden by @if(! isBuiltIn()) in the blade, the controller refuses
 * built_in=1 rows in deleteAdapterInstance, and the seed migration
 * sets built_in=1 on every shipped adapter. This test exercises all
 * three so a future refactor can't silently drop the guard.
 */
class BuiltInDeleteProtectionTest extends TestCase
{
    public function test_delete_button_is_hidden_when_a_built_in_adapter_is_selected()
    {
        $fleet = SyncAdapterInstance::where('slug', 'fleet')->firstOrFail();

        $html = $this->actingAs(User::factory()->superuser()->create())
            ->get(route('settings.adapters.index', ['adapter' => $fleet->slug]))
            ->assertOk()
            ->getContent();

        // Trigger is always rendered so the tab-switch JS can flip its
        // visibility as the admin moves between adapters. Server-side
        // initial state must hide it when a built-in is the selected
        // adapter (matches what the JS also enforces).
        $this->assertMatchesRegularExpression(
            '/id="adapter-delete-trigger"[^>]*style="[^"]*display:\s*none/i',
            $html,
        );
    }

    public function test_delete_button_is_visible_when_a_user_created_adapter_is_selected()
    {
        $userMade = SyncAdapterInstance::create([
            'adapter_type' => 'fleet',
            'label' => 'Second Fleet',
            'built_in' => false,
        ]);

        $html = $this->actingAs(User::factory()->superuser()->create())
            ->get(route('settings.adapters.index', ['adapter' => $userMade->slug]))
            ->assertOk()
            ->getContent();

        // Visible = style="" (empty) when initially selected. The regex
        // is generous to survive attribute-order shuffles from Blade
        // formatting changes.
        $this->assertMatchesRegularExpression(
            '/id="adapter-delete-trigger"[^>]*style=""/i',
            $html,
        );

        // data-href points at the user-created adapter, not any of the
        // built-ins that share the tab list. The shared .delete-asset
        // handler in snipeit.js reads data-href when the button is
        // clicked and swaps it into #dataConfirmModal's <form action>.
        $this->assertStringContainsString(
            'data-href="'.route('settings.adapters.destroy', $userMade->slug).'"',
            $html,
        );
    }

    public function test_tab_list_carries_data_attrs_for_the_js_toggle()
    {
        // Every tab link gets data-adapter-built-in + data-adapter-destroy-url
        // so the shown.bs.tab handler can flip the delete form's
        // visibility and action URL on client-side tab switches.
        SyncAdapterInstance::create([
            'adapter_type' => 'fleet',
            'label' => 'Second Fleet',
            'built_in' => false,
        ]);

        $html = $this->actingAs(User::factory()->superuser()->create())
            ->get(route('settings.adapters.index'))
            ->assertOk()
            ->getContent();

        // Built-in flag on the Fleet tab
        $this->assertMatchesRegularExpression(
            '/data-adapter-name="fleet"[^>]*data-adapter-built-in="1"/',
            $html,
        );
        // User-created gets the 0 flag
        $this->assertMatchesRegularExpression(
            '/data-adapter-name="second-fleet"[^>]*data-adapter-built-in="0"/',
            $html,
        );
    }

    public function test_backend_refuses_to_delete_a_built_in_adapter()
    {
        $fleet = SyncAdapterInstance::where('slug', 'fleet')->firstOrFail();

        $this->actingAs(User::factory()->superuser()->create())
            ->delete(route('settings.adapters.destroy', $fleet->slug))
            ->assertRedirect(route('settings.adapters.index', ['adapter' => $fleet->slug]))
            ->assertSessionHas('error');

        // Row still exists. Belt-and-suspenders: even if the blade
        // gate broke and someone crafted a raw DELETE, the built-in
        // stays put.
        $this->assertDatabaseHas('sync_adapter_instances', [
            'id' => $fleet->id,
            'slug' => 'fleet',
        ]);
    }

    public function test_unknown_adapter_slug_redirects_to_index_with_error()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('settings.adapters.index', ['adapter' => 'never-existed']))
            ->assertRedirect(route('settings.adapters.index'))
            ->assertSessionHas('error');
    }

    public function test_backend_deletes_a_user_created_adapter()
    {
        $userMade = SyncAdapterInstance::create([
            'adapter_type' => 'fleet',
            'label' => 'Second Fleet',
            'built_in' => false,
        ]);

        $this->actingAs(User::factory()->superuser()->create())
            ->delete(route('settings.adapters.destroy', $userMade->slug))
            ->assertRedirect(route('settings.adapters.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('sync_adapter_instances', ['id' => $userMade->id]);
    }
}
