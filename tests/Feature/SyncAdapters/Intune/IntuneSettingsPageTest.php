<?php

namespace Tests\Feature\SyncAdapters\Intune;

use App\Models\Category;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

/**
 * Smoke coverage for the Intune adapter's registration + settings-page
 * presence. Intune's schema mixes non-secret fields (tenant_id,
 * client_id) with a secret field (client_secret), so this test proves
 * the SyncAdapter base's per-schema encryption dispatch works:
 * secrets get encrypted at rest, plain-text fields stay plain. pull()
 * is still a stub so no sync coverage.
 */
class IntuneSettingsPageTest extends TestCase
{
    public function test_intune_tab_renders_on_the_adapters_page()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('settings.adapters.index'))
            ->assertOk()
            ->assertSee('Microsoft Intune') // tab label
            ->assertSee('Tenant ID')        // schema-declared label
            ->assertSee('Client ID')
            ->assertSee('Client Secret');
    }

    public function test_saving_intune_encrypts_only_the_secret_field()
    {
        $intune = SyncAdapterInstance::where('slug', 'intune')->firstOrFail();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.adapters.save', $intune), [
                'intune_url' => 'https://graph.microsoft.com',
                'intune_tenant_id' => 'contoso.onmicrosoft.com',
                'intune_client_id' => '00000000-0000-0000-0000-000000000001',
                'intune_client_secret' => 'plaintext-client-secret',
                'intune_default_category_id' => Category::factory()->create()->id,
                'intune_default_status_id' => Statuslabel::factory()->rtd()->create()->id,
            ])
            ->assertRedirect(route('settings.adapters.index', ['adapter' => $intune->slug]));

        // Non-secret fields land as plain text so admins can audit
        // which Azure app registration is in use without a decrypt
        // step.
        $this->assertSame('contoso.onmicrosoft.com', SyncAdapterConfig::get($intune->id, 'tenant_id'));
        $this->assertSame('00000000-0000-0000-0000-000000000001', SyncAdapterConfig::get($intune->id, 'client_id'));

        // Secret field is Crypt-encrypted at rest. Regression that
        // stored it plain would surface here.
        $storedSecret = SyncAdapterConfig::get($intune->id, 'client_secret');
        $this->assertNotSame('plaintext-client-secret', $storedSecret);
        $this->assertSame('plaintext-client-secret', Crypt::decrypt($storedSecret));
    }

    public function test_unchecking_create_snipeit_assets_on_pull_persists_as_off()
    {
        // Regression for #19917. The write-side default on
        // $request->boolean(..., true) flipped unchecked checkboxes back
        // to '1' on every save, silently re-enabling asset creation. The
        // "default on when never set" semantic lives on the read side
        // (createsSnipeitAssetsOnPull), so the write side must honor the
        // submitted value as-is.
        $intune = SyncAdapterInstance::where('slug', 'intune')->firstOrFail();
        SyncAdapterConfig::put($intune->id, 'create_snipeit_assets_on_pull', '1');

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.adapters.save', $intune), [
                'intune_url' => 'https://graph.microsoft.com',
                'intune_tenant_id' => 'contoso.onmicrosoft.com',
                'intune_client_id' => '00000000-0000-0000-0000-000000000001',
                'intune_client_secret' => 'plaintext-client-secret',
                'intune_default_category_id' => Category::factory()->create()->id,
                'intune_default_status_id' => Statuslabel::factory()->rtd()->create()->id,
                // Checkbox absent, mirroring an unchecked form submit.
            ])
            ->assertRedirect(route('settings.adapters.index', ['adapter' => $intune->slug]));

        $this->assertSame('0', SyncAdapterConfig::get($intune->id, 'create_snipeit_assets_on_pull'));
    }

    public function test_checking_create_snipeit_assets_on_pull_persists_as_on()
    {
        $intune = SyncAdapterInstance::where('slug', 'intune')->firstOrFail();
        SyncAdapterConfig::put($intune->id, 'create_snipeit_assets_on_pull', '0');

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.adapters.save', $intune), [
                'intune_url' => 'https://graph.microsoft.com',
                'intune_tenant_id' => 'contoso.onmicrosoft.com',
                'intune_client_id' => '00000000-0000-0000-0000-000000000001',
                'intune_client_secret' => 'plaintext-client-secret',
                'intune_default_category_id' => Category::factory()->create()->id,
                'intune_default_status_id' => Statuslabel::factory()->rtd()->create()->id,
                'intune_create_snipeit_assets_on_pull' => '1',
            ])
            ->assertRedirect(route('settings.adapters.index', ['adapter' => $intune->slug]));

        $this->assertSame('1', SyncAdapterConfig::get($intune->id, 'create_snipeit_assets_on_pull'));
    }

    public function test_blank_credentials_on_save_fail_validation()
    {
        $intune = SyncAdapterInstance::where('slug', 'intune')->firstOrFail();
        SyncAdapterConfig::put($intune->id, 'tenant_id', 'existing-tenant');
        SyncAdapterConfig::put($intune->id, 'client_id', 'existing-client-id');
        SyncAdapterConfig::put($intune->id, 'client_secret', Crypt::encrypt('existing-client-secret'));

        $this->actingAs(User::factory()->superuser()->create())
            ->from(route('settings.adapters.index', ['adapter' => $intune->slug]))
            ->post(route('settings.adapters.save', $intune), [
                'intune_url' => 'https://graph.microsoft.us',
                'intune_tenant_id' => '',
                'intune_client_id' => '',
                'intune_client_secret' => '',
                'intune_default_category_id' => Category::factory()->create()->id,
                'intune_default_status_id' => Statuslabel::factory()->rtd()->create()->id,
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['intune_tenant_id', 'intune_client_id', 'intune_client_secret']);

        // Nothing persisted since the whole save was rejected.
        $this->assertSame('existing-tenant', SyncAdapterConfig::get($intune->id, 'tenant_id'));
        $this->assertSame('existing-client-id', SyncAdapterConfig::get($intune->id, 'client_id'));
        $this->assertSame('existing-client-secret', Crypt::decrypt(SyncAdapterConfig::get($intune->id, 'client_secret')));
    }

    public function test_save_rejects_non_microsoft_graph_base_url_with_a_listed_error()
    {
        // Preset-locked Base URL. An admin typing anything other than
        // a known Microsoft Graph cloud (e.g. a loopback probe or a
        // typo'd `/beta/deviceManagement/managedDevices` endpoint)
        // should 422 with a message that enumerates the valid URLs,
        // not Laravel's stock "The selected intune_url is invalid".
        $intune = SyncAdapterInstance::where('slug', 'intune')->firstOrFail();

        $this->actingAs(User::factory()->superuser()->create())
            ->from(route('settings.adapters.index', ['adapter' => $intune->slug]))
            ->post(route('settings.adapters.save', $intune), [
                'intune_url' => 'https://graph.microsoft.com/beta/deviceManagement/managedDevices',
                'intune_tenant_id' => 'contoso.onmicrosoft.com',
                'intune_client_id' => '00000000-0000-0000-0000-000000000001',
                'intune_client_secret' => 'plaintext-client-secret',
                'intune_default_status_id' => Statuslabel::factory()->rtd()->create()->id,
            ])
            ->assertRedirect()
            ->assertSessionHasErrorsIn('default', [
                'intune_url' => 'Base URL must be one of: https://graph.microsoft.com, https://graph.microsoft.us, https://microsoftgraph.chinacloudapi.cn',
            ]);
    }

    public function test_blanking_scope_tag_filter_clears_the_stored_value()
    {
        // Regression. Non-secret text fields in the shared
        // settingsSchema path used to skip the save branch on blank
        // input (preserve-on-blank, same as secrets), so admins who
        // removed their scope_tag_filter saw the previous value
        // re-appear on reload. Blanking should clear.
        $intune = SyncAdapterInstance::where('slug', 'intune')->firstOrFail();
        SyncAdapterConfig::put($intune->id, 'scope_tag_filter', 'Marketing, 7');

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.adapters.save', $intune), [
                'intune_url' => 'https://graph.microsoft.com',
                'intune_tenant_id' => 'contoso.onmicrosoft.com',
                'intune_client_id' => '00000000-0000-0000-0000-000000000001',
                'intune_client_secret' => 'plaintext-client-secret',
                'intune_scope_tag_filter' => '',
                'intune_default_status_id' => Statuslabel::factory()->rtd()->create()->id,
            ])
            ->assertRedirect(route('settings.adapters.index', ['adapter' => $intune->slug]))
            ->assertSessionHasNoErrors();

        $this->assertSame('', SyncAdapterConfig::get($intune->id, 'scope_tag_filter'));
    }

    public function test_save_normalizes_trailing_slash_before_validation()
    {
        // Admin pastes `https://graph.microsoft.com/` from vendor docs
        // (or an existing config stored that way) and expects it to
        // save. Controller strips the trailing slash before the
        // Rule::in check runs so the preset still matches.
        $intune = SyncAdapterInstance::where('slug', 'intune')->firstOrFail();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.adapters.save', $intune), [
                'intune_url' => 'https://graph.microsoft.com/',
                'intune_tenant_id' => 'contoso.onmicrosoft.com',
                'intune_client_id' => '00000000-0000-0000-0000-000000000001',
                'intune_client_secret' => 'plaintext-client-secret',
                'intune_default_status_id' => Statuslabel::factory()->rtd()->create()->id,
            ])
            ->assertRedirect(route('settings.adapters.index', ['adapter' => $intune->slug]))
            ->assertSessionHasNoErrors();

        $this->assertSame('https://graph.microsoft.com', SyncAdapterConfig::get($intune->id, 'url'));
    }
}
