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
 * presence. v2 authenticates with a single x-api-key, so the
 * settings form carries one secret credential rather than v1's
 * client-id + client-secret pair. The adapter's base URL is fixed
 * (api.addigy.com), so no `url` field is on the form either.
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

    public function test_saving_addigy_encrypts_the_api_key()
    {
        $addigy = SyncAdapterInstance::where('slug', 'addigy')->firstOrFail();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.adapters.save', $addigy), [
                'addigy_api_key' => 'plaintext-addigy-api-key',
                'addigy_default_category_id' => Category::factory()->create()->id,
                'addigy_default_status_id' => Statuslabel::factory()->rtd()->create()->id,
            ])
            ->assertRedirect(route('settings.adapters.index', ['adapter' => $addigy->slug]));

        $storedApiKey = SyncAdapterConfig::get($addigy->id, 'api_key');
        $this->assertNotSame('plaintext-addigy-api-key', $storedApiKey);
        $this->assertSame('plaintext-addigy-api-key', Crypt::decrypt($storedApiKey));
    }

    public function test_blank_api_key_on_save_fails_validation()
    {
        $addigy = SyncAdapterInstance::where('slug', 'addigy')->firstOrFail();
        SyncAdapterConfig::put($addigy->id, 'api_key', Crypt::encrypt('existing-api-key'));

        $this->actingAs(User::factory()->superuser()->create())
            ->from(route('settings.adapters.index', ['adapter' => $addigy->slug]))
            ->post(route('settings.adapters.save', $addigy), [
                'addigy_api_key' => '',
                'addigy_default_category_id' => Category::factory()->create()->id,
                'addigy_default_status_id' => Statuslabel::factory()->rtd()->create()->id,
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['addigy_api_key']);

        // Stored value stays put because the save was rejected.
        $this->assertSame('existing-api-key', Crypt::decrypt(SyncAdapterConfig::get($addigy->id, 'api_key')));
    }
}
