<?php

namespace Tests\Feature\SyncAdapters\Kandji;

use App\Models\Category;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

/**
 * Smoke coverage for the Kandji adapter's registration + settings-page
 * presence. Kandji shares the URL+token partial with Fleet, so this
 * test proves the shared partial works for a second adapter and that
 * the registry-driven dispatch resolves Kandji correctly. pull() is
 * still a stub, so no sync-flow coverage here.
 */
class KandjiSettingsPageTest extends TestCase
{
    public function test_kandji_tab_renders_on_the_adapters_page()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('settings.adapters.index'))
            ->assertOk()
            ->assertSee('Kandji');
    }

    public function test_saving_kandji_persists_url_and_encrypts_token()
    {
        $kandji = SyncAdapterInstance::where('slug', 'kandji')->firstOrFail();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.adapters.save', $kandji), [
                'kandji_url' => 'https://example.api.kandji.io',
                'kandji_token' => 'plaintext-kandji-token',
                'kandji_default_category_id' => Category::factory()->create()->id,
                'kandji_default_status_id' => Statuslabel::factory()->rtd()->create()->id,
            ])
            ->assertRedirect(route('settings.adapters.index', ['adapter' => $kandji->slug]));

        $this->assertSame('https://example.api.kandji.io', SyncAdapterConfig::get($kandji->id, 'url'));

        // Independent storage from Fleet. Saving Kandji doesn't touch
        // Fleet's rows.
        $fleet = SyncAdapterInstance::where('slug', 'fleet')->firstOrFail();
        $this->assertNull(SyncAdapterConfig::get($fleet->id, 'url'));

        $storedToken = SyncAdapterConfig::get($kandji->id, 'token');
        $this->assertNotSame('plaintext-kandji-token', $storedToken);
        $this->assertSame('plaintext-kandji-token', Crypt::decrypt($storedToken));
    }
}
