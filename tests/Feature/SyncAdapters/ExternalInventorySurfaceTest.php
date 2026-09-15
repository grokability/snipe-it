<?php

namespace Tests\Feature\SyncAdapters;

use App\Models\Asset;
use App\Models\AssetExternalSource;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Covers the surfacing of sync-adapter-populated external inventory
 * (MAC / IP / OS / OS version / last seen) on the asset detail view
 * and via the assets API transformer.
 *
 * The write path is already covered by the per-adapter tests +
 * SyncHostFromAdapter integration tests. This file is the read path.
 */
class ExternalInventorySurfaceTest extends TestCase
{
    public function test_asset_detail_view_renders_external_inventory_rows_when_data_exists()
    {
        $asset = Asset::factory()->create();

        DB::table('asset_external_sources')->insert([
            'asset_id' => $asset->id,
            'source' => 'fleet',
            'external_id' => 'fleet-'.$asset->id,
            'primary_mac' => 'aa:bb:cc:dd:ee:ff',
            'primary_ip' => '10.0.0.42',
            'os' => 'macOS',
            'os_version' => '14.2.1',
            'last_seen' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $html = $this->actingAs(User::factory()->superuser()->create())
            ->get(route('hardware.show', $asset))
            ->assertOk()
            ->getContent();

        // Each value appears in the rendered detail view.
        $this->assertStringContainsString('aa:bb:cc:dd:ee:ff', $html);
        $this->assertStringContainsString('10.0.0.42', $html);
        $this->assertStringContainsString('macOS', $html);
        $this->assertStringContainsString('14.2.1', $html);
    }

    public function test_asset_detail_view_omits_external_inventory_block_when_no_row_exists()
    {
        $asset = Asset::factory()->create();

        $html = $this->actingAs(User::factory()->superuser()->create())
            ->get(route('hardware.show', $asset))
            ->assertOk()
            ->getContent();

        // Each external-inventory data-row emits an
        // `js-copy-external_*` CSS class on its copy-to-clipboard
        // span. Their absence proves the block did not render.
        // Asserting on the field labels alone is unreliable because
        // the same lang keys leak into the assets-table column-picker
        // JSON on other pages, causing false-positive matches.
        $this->assertStringNotContainsString('js-copy-external_mac', $html);
        $this->assertStringNotContainsString('js-copy-external_ip', $html);
        $this->assertStringNotContainsString('js-copy-external_last_seen', $html);
    }

    public function test_asset_detail_view_omits_individual_rows_for_null_fields()
    {
        // Partial data: only MAC present, other fields null.
        $asset = Asset::factory()->create();
        DB::table('asset_external_sources')->insert([
            'asset_id' => $asset->id,
            'source' => 'fleet',
            'external_id' => 'fleet-'.$asset->id,
            'primary_mac' => 'aa:bb:cc:dd:ee:ff',
            'primary_ip' => null,
            'os' => null,
            'os_version' => null,
            'last_seen' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $html = $this->actingAs(User::factory()->superuser()->create())
            ->get(route('hardware.show', $asset))
            ->assertOk()
            ->getContent();

        // MAC row rendered (marker + value both present).
        $this->assertStringContainsString('js-copy-external_mac', $html);
        $this->assertStringContainsString('aa:bb:cc:dd:ee:ff', $html);
        // Null-valued rows did not render (no marker).
        $this->assertStringNotContainsString('js-copy-external_ip', $html);
        $this->assertStringNotContainsString('js-copy-external_os', $html);
        $this->assertStringNotContainsString('js-copy-external_last_seen', $html);
    }

    public function test_assets_api_exposes_external_inventory_fields_as_flat_keys()
    {
        $asset = Asset::factory()->create();
        DB::table('asset_external_sources')->insert([
            'asset_id' => $asset->id,
            'source' => 'fleet',
            'external_id' => 'fleet-'.$asset->id,
            'primary_mac' => 'aa:bb:cc:dd:ee:ff',
            'primary_ip' => '10.0.0.42',
            'os' => 'macOS',
            'os_version' => '14.2.1',
            'last_seen' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs(User::factory()->superuser()->create(), 'api')
            ->getJson(route('api.assets.show', $asset));

        $response->assertOk()
            ->assertJsonPath('primary_mac', 'aa:bb:cc:dd:ee:ff')
            ->assertJsonPath('primary_ip', '10.0.0.42')
            ->assertJsonPath('external_os', 'macOS')
            ->assertJsonPath('external_os_version', '14.2.1');

        // last_seen is present as a formatted datetime string, not
        // asserting the exact value (locale + timezone would make it
        // brittle) but confirming the key is populated.
        $this->assertNotEmpty($response->json('last_seen'));
    }

    public function test_assets_api_returns_null_external_inventory_keys_when_no_row_exists()
    {
        $asset = Asset::factory()->create();

        $response = $this->actingAs(User::factory()->superuser()->create(), 'api')
            ->getJson(route('api.assets.show', $asset));

        // Keys still present in the payload shape (so bs-table's
        // column bindings never throw missing-property errors), just
        // null when no external inventory row exists.
        $response->assertOk()
            ->assertJsonPath('primary_mac', null)
            ->assertJsonPath('primary_ip', null)
            ->assertJsonPath('external_os', null)
            ->assertJsonPath('external_os_version', null);
    }

    public function test_asset_external_source_belongs_to_asset()
    {
        $asset = Asset::factory()->create();
        $source = AssetExternalSource::create([
            'asset_id' => $asset->id,
            'source' => 'fleet',
            'external_id' => 'fleet-'.$asset->id,
            'primary_mac' => 'aa:bb:cc:dd:ee:ff',
        ]);

        $this->assertTrue($asset->externalSource->is($source));
        $this->assertTrue($source->asset->is($asset));
    }
}
