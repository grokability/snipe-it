<?php

namespace Tests\Feature\SyncAdapters\Action1;

use App\Models\Category;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

/**
 * Smoke coverage for the Action1 adapter's registration + settings
 * page. Action1 has no Base URL field (region dropdown resolves to
 * one of the five shipped URLs) so tests here focus on the schema
 * fields: region, org_id, client_id, client_secret.
 */
class Action1SettingsPageTest extends TestCase
{
    public function test_action1_tab_renders_on_the_adapters_page()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('settings.adapters.index'))
            ->assertOk()
            ->assertSee('Action1')
            ->assertSee('Region')
            ->assertSee('Organization ID')
            ->assertSee('Client ID')
            ->assertSee('Client Secret');
    }

    public function test_saving_action1_encrypts_only_the_secret_field()
    {
        $action1 = SyncAdapterInstance::where('slug', 'action1')->firstOrFail();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.adapters.save', $action1), [
                'action1_region' => 'europe',
                'action1_org_id' => '00000000-0000-0000-0000-000000000001',
                'action1_client_id' => 'fake-client-id',
                'action1_client_secret' => 'plaintext-client-secret',
                'action1_default_category_id' => Category::factory()->create()->id,
                'action1_default_status_id' => Statuslabel::factory()->rtd()->create()->id,
            ])
            ->assertRedirect(route('settings.adapters.index', ['adapter' => $action1->slug]));

        $this->assertSame('europe', SyncAdapterConfig::get($action1->id, 'region'));
        $this->assertSame('00000000-0000-0000-0000-000000000001', SyncAdapterConfig::get($action1->id, 'org_id'));
        $this->assertSame('fake-client-id', SyncAdapterConfig::get($action1->id, 'client_id'));

        $storedSecret = SyncAdapterConfig::get($action1->id, 'client_secret');
        $this->assertNotSame('plaintext-client-secret', $storedSecret);
        $this->assertSame('plaintext-client-secret', Crypt::decrypt($storedSecret));
    }

    public function test_blank_credentials_on_save_fail_validation()
    {
        $action1 = SyncAdapterInstance::where('slug', 'action1')->firstOrFail();
        SyncAdapterConfig::put($action1->id, 'org_id', 'existing-org');
        SyncAdapterConfig::put($action1->id, 'client_id', 'existing-client-id');
        SyncAdapterConfig::put($action1->id, 'client_secret', Crypt::encrypt('existing-client-secret'));

        $this->actingAs(User::factory()->superuser()->create())
            ->from(route('settings.adapters.index', ['adapter' => $action1->slug]))
            ->post(route('settings.adapters.save', $action1), [
                'action1_region' => 'north_america',
                'action1_org_id' => '',
                'action1_client_id' => '',
                'action1_client_secret' => '',
                'action1_default_category_id' => Category::factory()->create()->id,
                'action1_default_status_id' => Statuslabel::factory()->rtd()->create()->id,
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['action1_org_id', 'action1_client_id', 'action1_client_secret']);

        // Rejected save keeps the previously stored credentials intact.
        $this->assertSame('existing-org', SyncAdapterConfig::get($action1->id, 'org_id'));
        $this->assertSame('existing-client-id', SyncAdapterConfig::get($action1->id, 'client_id'));
        $this->assertSame('existing-client-secret', Crypt::decrypt(SyncAdapterConfig::get($action1->id, 'client_secret')));
    }
}
