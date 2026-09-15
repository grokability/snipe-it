<?php

namespace Tests\Feature\SyncAdapters\Fleet;

use App\Models\Category;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

/**
 * Coverage for the Fleet section of the shared Settings → Sync Adapters
 * page. Access gates, form render, persistence with encryption, and
 * preserve-existing-token behavior when the admin leaves the token
 * field blank on a subsequent save. Also covers ExternalUrl SSRF-guard
 * enforcement so a superadmin can't point the adapter at 127.0.0.1.
 */
class FleetSettingsPageTest extends TestCase
{
    /**
     * default_category_id + default_status_id are now required by
     * postAdapterConfig, so every settings-save happy-path test needs
     * to seed them. Kept in one place so a rename or additional
     * required field lands in one spot rather than 13.
     *
     * @return array<string, int>
     */
    private function requiredFields(string $slug): array
    {
        return [
            $slug.'_default_category_id' => Category::factory()->create()->id,
            $slug.'_default_status_id' => Statuslabel::factory()->rtd()->create()->id,
        ];
    }

    public function test_superuser_sees_the_fleet_section_on_the_adapters_page()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('settings.adapters.index'))
            ->assertOk()
            ->assertSee('Fleet')       // tab label
            ->assertSee('Base URL')    // generic field label
            ->assertSee('API Token');  // generic field label
    }

    public function test_non_superuser_gets_forbidden()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('settings.adapters.index'))
            ->assertForbidden();
    }

    public function test_saving_persists_url_and_encrypts_token()
    {
        $fleet = SyncAdapterInstance::where('slug', 'fleet')->firstOrFail();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.adapters.save', $fleet), $this->requiredFields('fleet') + [
                'fleet_url' => 'https://example.com/fleet',
                'fleet_token' => 'plaintext-token-value',
            ])
            ->assertRedirect(route('settings.adapters.index', ['adapter' => $fleet->slug]));

        $this->assertSame('https://example.com/fleet', SyncAdapterConfig::get($fleet->id, 'url'));

        $storedToken = SyncAdapterConfig::get($fleet->id, 'token');
        $this->assertNotSame('plaintext-token-value', $storedToken);
        $this->assertSame('plaintext-token-value', Crypt::decrypt($storedToken));
    }

    public function test_blank_token_on_save_fails_validation()
    {
        $fleet = SyncAdapterInstance::where('slug', 'fleet')->firstOrFail();
        SyncAdapterConfig::put($fleet->id, 'url', 'https://example.com/fleet-old');
        SyncAdapterConfig::put($fleet->id, 'token', Crypt::encrypt('previously-saved-token'));

        $this->actingAs(User::factory()->superuser()->create())
            ->from(route('settings.adapters.index', ['adapter' => $fleet->slug]))
            ->post(route('settings.adapters.save', $fleet), $this->requiredFields('fleet') + [
                'fleet_url' => 'https://example.com/fleet-new',
                'fleet_token' => '',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('fleet_token');

        // URL didn't persist because the whole save was rejected.
        // Stored token stays put too.
        $this->assertSame('https://example.com/fleet-old', SyncAdapterConfig::get($fleet->id, 'url'));
        $this->assertSame('previously-saved-token', Crypt::decrypt(SyncAdapterConfig::get($fleet->id, 'token')));
    }

    public function test_unknown_instance_on_save_returns_404()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.adapters.save', 'does-not-exist'), [
                'fleet_url' => 'https://example.com/fleet',
            ])
            ->assertNotFound();
    }

    public function test_loopback_url_is_rejected_by_external_url_guard()
    {
        $fleet = SyncAdapterInstance::where('slug', 'fleet')->firstOrFail();

        $this->actingAs(User::factory()->superuser()->create())
            ->from(route('settings.adapters.index', ['adapter' => $fleet->slug]))
            ->post(route('settings.adapters.save', $fleet), $this->requiredFields('fleet') + [
                'fleet_url' => 'http://127.0.0.1:9999/api',
                'fleet_token' => 'secret',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('fleet_url');

        // Config didn't persist the loopback URL.
        $this->assertNull(SyncAdapterConfig::get($fleet->id, 'url'));
    }

    public function test_metadata_ip_is_rejected_by_external_url_guard()
    {
        $fleet = SyncAdapterInstance::where('slug', 'fleet')->firstOrFail();

        $this->actingAs(User::factory()->superuser()->create())
            ->from(route('settings.adapters.index', ['adapter' => $fleet->slug]))
            ->post(route('settings.adapters.save', $fleet), $this->requiredFields('fleet') + [
                'fleet_url' => 'http://169.254.169.254/latest/meta-data/',
                'fleet_token' => 'secret',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('fleet_url');

        $this->assertNull(SyncAdapterConfig::get($fleet->id, 'url'));
    }
}
