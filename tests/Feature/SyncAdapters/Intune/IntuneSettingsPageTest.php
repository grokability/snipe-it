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
 * the ConfigurableAdapter base's per-schema encryption dispatch works:
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
}
