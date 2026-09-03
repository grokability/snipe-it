<?php

namespace Tests\Feature\SyncAdapters\Addigy;

use App\Models\Category;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

/**
 * Smoke coverage for the Addigy adapter's registration + settings-page
 * presence. Addigy is the first adapter to use the URL+key-pair auth
 * shape (UrlKeyPairAdapter base + url_keypair partial), so this test
 * proves the shell renders, both credential fields persist, and each
 * gets encrypted at rest. pull() is still a stub so no sync coverage.
 */
class AddigySettingsPageTest extends TestCase
{
    public function test_addigy_tab_renders_on_the_adapters_page()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('settings.adapters.index'))
            ->assertOk()
            ->assertSee('Addigy');
    }

    public function test_saving_addigy_persists_url_and_encrypts_both_credentials()
    {
        $addigy = SyncAdapterInstance::where('slug', 'addigy')->firstOrFail();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.adapters.save', $addigy), [
                'addigy_url' => 'https://prod.addigy.com/api',
                'addigy_key_id' => 'plaintext-addigy-client-id',
                'addigy_key_secret' => 'plaintext-addigy-client-secret',
                'addigy_default_category_id' => Category::factory()->create()->id,
                'addigy_default_status_id' => Statuslabel::factory()->rtd()->create()->id,
            ])
            ->assertRedirect(route('settings.adapters.index', ['adapter' => $addigy->slug]));

        $this->assertSame('https://prod.addigy.com/api', SyncAdapterConfig::get($addigy->id, 'url'));

        // key_id is a non-secret credential (Client ID is a public
        // identifier per OAuth 2.0 convention), stored as plain text.
        // key_secret is encrypted at rest. Both are stored under
        // distinct keys so a mix-up would surface here.
        $storedKeyId = SyncAdapterConfig::get($addigy->id, 'key_id');
        $storedKeySecret = SyncAdapterConfig::get($addigy->id, 'key_secret');
        $this->assertSame('plaintext-addigy-client-id', $storedKeyId);
        $this->assertNotSame('plaintext-addigy-client-secret', $storedKeySecret);
        $this->assertSame('plaintext-addigy-client-secret', Crypt::decrypt($storedKeySecret));
    }

    public function test_blank_credentials_on_save_fail_validation()
    {
        $addigy = SyncAdapterInstance::where('slug', 'addigy')->firstOrFail();
        SyncAdapterConfig::put($addigy->id, 'key_id', 'existing-key-id');
        SyncAdapterConfig::put($addigy->id, 'key_secret', Crypt::encrypt('existing-key-secret'));

        $this->actingAs(User::factory()->superuser()->create())
            ->from(route('settings.adapters.index', ['adapter' => $addigy->slug]))
            ->post(route('settings.adapters.save', $addigy), [
                'addigy_url' => 'https://prod.addigy.com/api',
                'addigy_key_id' => '',
                'addigy_key_secret' => '',
                'addigy_default_category_id' => Category::factory()->create()->id,
                'addigy_default_status_id' => Statuslabel::factory()->rtd()->create()->id,
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['addigy_key_id', 'addigy_key_secret']);

        // Stored values stay put because the save was rejected.
        // key_id is plain text, key_secret is encrypted.
        $this->assertSame('existing-key-id', SyncAdapterConfig::get($addigy->id, 'key_id'));
        $this->assertSame('existing-key-secret', Crypt::decrypt(SyncAdapterConfig::get($addigy->id, 'key_secret')));
    }
}
