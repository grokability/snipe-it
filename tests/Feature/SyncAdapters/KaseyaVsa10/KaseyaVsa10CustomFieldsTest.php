<?php

namespace Tests\Feature\SyncAdapters\KaseyaVsa10;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\Models\User;
use App\SyncAdapters\KaseyaVsa10\KaseyaVsa10Adapter;
use App\SyncAdapters\Support\MappingTargets;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Coverage for the VSA 10 vendor-custom-field flow:
 *  - fetching the definitions from /customfields
 *  - caching them under vendor_custom_fields
 *  - surfacing them in extraFields() with admin_defined = true
 *  - narrowing the mapping-target pool to skip + custom-only
 *  - enriching pulled records with per-device values only when at
 *    least one field has been mapped
 *  - refresh route flow via the settings controller
 */
class KaseyaVsa10CustomFieldsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_fetch_vendor_custom_fields_filters_to_device_context()
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/api/v3/customfields*' => Http::response([
                'Data' => [
                    ['Id' => 850, 'Name' => 'Purchase Cost', 'Type' => 'Number', 'Contexts' => ['Device']],
                    ['Id' => 851, 'Name' => 'Compliance', 'Type' => 'Boolean', 'Contexts' => ['Device']],
                ],
                'Meta' => ['TotalCount' => 2, 'ResponseCode' => 200],
            ]),
        ]);

        $fields = $adapter->fetchVendorCustomFields();

        $this->assertCount(2, $fields);
        $this->assertSame(['id' => '850', 'name' => 'Purchase Cost', 'type' => 'Number'], $fields[0]);
        $this->assertSame(['id' => '851', 'name' => 'Compliance', 'type' => 'Boolean'], $fields[1]);

        Http::assertSent(fn ($request) => str_contains(urldecode($request->url()), "Contexts/any(p: p eq 'Device')"));
    }

    public function test_cached_custom_fields_show_up_in_extra_fields_with_admin_defined_flag()
    {
        $adapter = $this->configuredAdapter();

        SyncAdapterConfig::put(
            $this->instanceId(),
            'vendor_custom_fields',
            json_encode([
                ['id' => '850', 'name' => 'Purchase Cost', 'type' => 'Number'],
                ['id' => '851', 'name' => 'Compliance', 'type' => 'Boolean'],
            ]),
        );

        $extras = $adapter->extraFields();

        $this->assertArrayHasKey('kaseya_vsa10_cf_purchase_cost', $extras);
        $this->assertArrayHasKey('kaseya_vsa10_cf_compliance', $extras);

        $cost = $extras['kaseya_vsa10_cf_purchase_cost'];
        $this->assertSame('Kaseya: Purchase Cost', $cost['label']);
        $this->assertSame('text', $cost['type']);
        $this->assertTrue($cost['admin_defined']);

        $compliance = $extras['kaseya_vsa10_cf_compliance'];
        $this->assertSame('boolean', $compliance['type']);
        $this->assertTrue($compliance['admin_defined']);
    }

    public function test_options_for_admin_defined_extra_omits_native_targets()
    {
        \App\Models\CustomField::factory()->create(['element' => 'text', 'name' => 'Purchase Info']);

        $adapterExtras = MappingTargets::optionsForExtra('text', adminDefined: false);
        $adminExtras = MappingTargets::optionsForExtra('text', adminDefined: true);

        // Adapter-declared text extras keep the native branch
        // (asset_tag, model, notes).
        $this->assertArrayHasKey('native:asset_tag', $adapterExtras);
        $this->assertArrayHasKey('native:model', $adapterExtras);
        $this->assertArrayHasKey('native:notes', $adapterExtras);

        // Admin-defined extras drop them. Skip + custom fields only.
        $this->assertArrayNotHasKey('native:asset_tag', $adminExtras);
        $this->assertArrayNotHasKey('native:model', $adminExtras);
        $this->assertArrayNotHasKey('native:notes', $adminExtras);
        $this->assertArrayHasKey('skip', $adminExtras);
        foreach (array_keys($adminExtras) as $target) {
            if ($target === 'skip') {
                continue;
            }
            $this->assertStringStartsWith('custom:', $target);
        }
    }

    public function test_pull_enriches_records_when_a_vendor_custom_field_is_mapped()
    {
        $customField = \App\Models\CustomField::factory()->create(['element' => 'text', 'name' => 'Purchase Cost']);
        $adapter = $this->configuredAdapter();

        SyncAdapterConfig::put(
            $this->instanceId(),
            'vendor_custom_fields',
            json_encode([['id' => '850', 'name' => 'Purchase Cost', 'type' => 'Text']]),
        );
        SyncAdapterConfig::put(
            $this->instanceId(),
            'mapping.kaseya_vsa10_cf_purchase_cost',
            'custom:'.$customField->id,
        );

        Http::fake([
            '*/api/v3/assets*' => Http::response([
                'Data' => [$this->minimalAsset('guid-1')],
            ]),
            '*/api/v3/devices/guid-1/customfields' => Http::response([
                'Data' => [
                    ['Id' => 850, 'Name' => 'Purchase Cost', 'Value' => '1299.00', 'Type' => 'Text'],
                    ['Id' => 851, 'Name' => 'Unmapped Field', 'Value' => 'ignored', 'Type' => 'Text'],
                ],
                'Meta' => ['TotalCount' => 2, 'ResponseCode' => 200],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];

        $this->assertSame('1299.00', $record->extra['kaseya_vsa10_cf_purchase_cost']);
        // Unmapped field still gets stashed under its slug key, so a
        // follow-up mapping picks up the value from the same pull if
        // the admin adds it. Extras that aren't mapped simply never
        // get written (the mapping-loop's target check handles it).
        $this->assertSame('ignored', $record->extra['kaseya_vsa10_cf_unmapped_field']);
    }

    public function test_pull_skips_customfields_call_when_nothing_is_mapped()
    {
        $adapter = $this->configuredAdapter();

        SyncAdapterConfig::put(
            $this->instanceId(),
            'vendor_custom_fields',
            json_encode([['id' => '850', 'name' => 'Purchase Cost', 'type' => 'Text']]),
        );
        // No mapping.kaseya_vsa10_cf_purchase_cost row.

        Http::fake([
            '*/api/v3/assets*' => Http::response([
                'Data' => [$this->minimalAsset('guid-x')],
            ]),
            '*/api/v3/devices/*/customfields' => Http::response(
                ['Data' => []],
                500, // deliberately unreachable so any accidental call would fail loudly
            ),
        ]);

        iterator_to_array($adapter->pull());

        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/customfields'));
    }

    public function test_pull_survives_customfields_endpoint_failure()
    {
        $customField = \App\Models\CustomField::factory()->create(['element' => 'text', 'name' => 'Anything']);
        $adapter = $this->configuredAdapter();

        SyncAdapterConfig::put(
            $this->instanceId(),
            'vendor_custom_fields',
            json_encode([['id' => '850', 'name' => 'Anything', 'type' => 'Text']]),
        );
        SyncAdapterConfig::put(
            $this->instanceId(),
            'mapping.kaseya_vsa10_cf_anything',
            'custom:'.$customField->id,
        );

        Http::fake([
            '*/api/v3/assets*' => Http::response(['Data' => [$this->minimalAsset('guid-transient')]]),
            '*/api/v3/devices/*/customfields' => Http::response(['error' => 'boom'], 503),
        ]);

        $records = iterator_to_array($adapter->pull());

        // Record still comes through; the un-enriched fields just stay
        // absent from extra. Sync doesn't abort on a partial customfields
        // outage.
        $this->assertCount(1, $records);
        $this->assertArrayNotHasKey('kaseya_vsa10_cf_anything', $records[0]->extra);
    }

    public function test_refresh_route_populates_the_cache_and_flashes_a_success()
    {
        $this->configuredAdapter();

        Http::fake([
            '*/api/v3/customfields*' => Http::response([
                'Data' => [
                    ['Id' => 850, 'Name' => 'Purchase Cost', 'Type' => 'Number', 'Contexts' => ['Device']],
                ],
            ]),
        ]);

        $instance = SyncAdapterInstance::where('slug', 'kaseya_vsa10')->firstOrFail();
        $instance->active = true;
        $instance->save();

        $response = $this
            ->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.adapters.refresh_custom_fields', $instance->slug));

        $response->assertRedirect(route('settings.adapters.index', ['adapter' => $instance->slug]));
        $response->assertSessionHas('success');

        $stored = SyncAdapterConfig::get($instance->id, 'vendor_custom_fields');
        $decoded = json_decode($stored, true);
        $this->assertSame('Purchase Cost', $decoded[0]['name']);
    }

    public function test_refresh_route_404s_for_adapters_that_do_not_support_vendor_custom_fields()
    {
        $fleet = SyncAdapterInstance::where('slug', 'fleet')->firstOrFail();
        $fleet->active = true;
        $fleet->save();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.adapters.refresh_custom_fields', $fleet->slug))
            ->assertNotFound();
    }

    private function configuredAdapter(): KaseyaVsa10Adapter
    {
        $instance = SyncAdapterInstance::where('slug', 'kaseya_vsa10')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://acme.vsax.net');
        SyncAdapterConfig::put($instance->id, 'token_id', Crypt::encrypt('stub-token-id'));
        SyncAdapterConfig::put($instance->id, 'token_secret', Crypt::encrypt('fake-token-secret'));

        return new KaseyaVsa10Adapter($instance->fresh());
    }

    private function instanceId(): int
    {
        return SyncAdapterInstance::where('slug', 'kaseya_vsa10')->firstOrFail()->id;
    }

    /**
     * Minimum asset row with enough hardware fields for a shell save.
     *
     * @return array<string, mixed>
     */
    private function minimalAsset(string $identifier): array
    {
        return [
            'Identifier' => $identifier,
            'Name' => 'vsa-'.$identifier,
            'OrganizationId' => 1,
            'AssetInfo' => [
                [
                    'CategoryName' => 'System',
                    'CategoryData' => ['Manufacturer' => 'Test Corp', 'Model' => 'Model X'],
                ],
                [
                    'CategoryName' => 'BIOS',
                    'CategoryData' => ['Serial Number' => 'SN-'.$identifier],
                ],
            ],
        ];
    }
}
