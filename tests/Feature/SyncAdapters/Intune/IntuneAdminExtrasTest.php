<?php

namespace Tests\Feature\SyncAdapters\Intune;

use App\Models\CustomField;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\Intune\IntuneAdapter;
use App\SyncAdapters\SyncAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Issue #19745: Intune admins want to map arbitrary Graph fields
 * (both MACs, storage, TPM, encryption, etc.) to Snipe-IT custom
 * fields. Coverage splits into three parts:
 *
 *   1. Preset extras: fields the adapter extracts by default from
 *      the Graph managedDevice payload.
 *   2. Admin-defined extras: the Custom Extras widget lets admins
 *      point at any dot-path in the vendor record.
 *   3. Mapping-targets surface: both preset and admin keys appear
 *      in the mapping picker.
 */
class IntuneAdminExtrasTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();
    }

    public function test_preset_extras_populate_from_list_only_graph_fields()
    {
        // These fields are documented LIST-populated on v1.0.
        // Ethernet MAC and physical memory are LIST-null per Microsoft
        // docs - covered separately by the per-device enrichment test.
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/oauth2/v2.0/token' => Http::response(['access_token' => 'stub-bearer', 'expires_in' => 3600]),
            '*/v1.0/deviceManagement/managedDevices*' => Http::response([
                'value' => [[
                    'id' => 'guid-1',
                    'deviceName' => 'host-one',
                    'serialNumber' => 'SN-ONE',
                    'wiFiMacAddress' => '01:23:45:67:89:AB',
                    'totalStorageSpaceInBytes' => 494000000000,
                    'freeStorageSpaceInBytes' => 353000000000,
                    'isEncrypted' => true,
                    'jailBroken' => 'Unknown',
                    'isSupervised' => false,
                    'azureADDeviceId' => 'azure-guid-1',
                    'azureADRegistered' => true,
                    'imei' => '353912345678901',
                    'meid' => '35123456789012',
                    'exchangeAccessState' => 'Allowed',
                    'userDisplayName' => 'Alice Example',
                    'managedDeviceName' => 'alice_windows_a1b2',
                    'partnerReportedThreatState' => 'secured',
                    'exchangeAccessStateReason' => 'none',
                    'deviceCategoryDisplayName' => 'Engineering',
                    'phoneNumber' => '+15551234567',
                    'subscriberCarrier' => 'Verizon',
                    'androidSecurityPatchLevel' => '2024-11-01',
                    'enrollmentProfileName' => 'Autopilot - Engineering',
                    'deviceEnrollmentType' => 'windowsAutoEnrollment',
                    'managementCertificateExpirationDate' => '2026-12-31T23:59:59Z',
                    'complianceGracePeriodExpirationDateTime' => '2026-10-15T00:00:00Z',
                    'deviceRegistrationState' => 'registered',
                ]],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];

        $this->assertSame('01:23:45:67:89:AB', $record->extra['intune_wifi_mac']);
        $this->assertSame(494, $record->extra['intune_total_storage_gb']);
        $this->assertSame(353, $record->extra['intune_free_storage_gb']);
        $this->assertTrue($record->extra['intune_is_encrypted']);
        $this->assertSame('Unknown', $record->extra['intune_jail_broken']);
        $this->assertFalse($record->extra['intune_is_supervised']);
        $this->assertSame('azure-guid-1', $record->extra['intune_azure_ad_device_id']);
        $this->assertTrue($record->extra['intune_azure_ad_registered']);
        $this->assertSame('353912345678901', $record->extra['intune_imei']);
        $this->assertSame('35123456789012', $record->extra['intune_meid']);
        $this->assertSame('Allowed', $record->extra['intune_exchange_access_state']);
        $this->assertSame('Alice Example', $record->extra['intune_user_display_name']);
        $this->assertSame('alice_windows_a1b2', $record->extra['intune_managed_device_name']);
        $this->assertSame('secured', $record->extra['intune_partner_reported_threat_state']);
        $this->assertSame('Engineering', $record->extra['intune_device_category']);
        $this->assertSame('Verizon', $record->extra['intune_subscriber_carrier']);
        $this->assertSame('windowsAutoEnrollment', $record->extra['intune_device_enrollment_type']);
        $this->assertSame('registered', $record->extra['intune_device_registration_state']);
    }

    public function test_ethernet_mac_and_physical_memory_null_when_enrichment_opt_in_is_off()
    {
        // Default state. Microsoft's LIST endpoint returns null for
        // these fields even if they're present on the device.
        $adapter = $this->configuredAdapter();

        Http::fake([
            '*/oauth2/v2.0/token' => Http::response(['access_token' => 'stub-bearer', 'expires_in' => 3600]),
            '*/v1.0/deviceManagement/managedDevices*' => Http::response([
                'value' => [[
                    'id' => 'guid-no-enrich',
                    'deviceName' => 'host-no-enrich',
                    'serialNumber' => 'SN-NO-ENRICH',
                    'wiFiMacAddress' => '11:22:33:44:55:66',
                ]],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];

        $this->assertNull($record->extra['intune_ethernet_mac']);
        $this->assertNull($record->extra['intune_physical_memory_gb']);
    }

    public function test_per_device_enrichment_backfills_ethernet_mac_and_physical_memory()
    {
        // When the opt-in is on, the adapter fires one GET per device
        // against /managedDevices/{id}?$select=ethernetMacAddress,physicalMemoryInBytes
        // and merges the response into the extras.
        $adapter = $this->configuredAdapter();
        $instance = SyncAdapterInstance::where('slug', 'intune')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'fetch_per_device_details', '1');

        // Specific URL fakes must come before broader ones - Http::fake
        // first-match semantics would otherwise catch the per-device
        // GET with the LIST response.
        Http::fake([
            '*/v1.0/deviceManagement/managedDevices/guid-enrich*' => Http::response([
                'ethernetMacAddress' => 'DE:AD:BE:EF:00:01',
                'physicalMemoryInBytes' => 17179869184,
            ]),
            '*/oauth2/v2.0/token' => Http::response(['access_token' => 'stub-bearer', 'expires_in' => 3600]),
            '*/v1.0/deviceManagement/managedDevices*' => Http::response([
                'value' => [[
                    'id' => 'guid-enrich',
                    'deviceName' => 'host-enrich',
                    'serialNumber' => 'SN-ENRICH',
                ]],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];

        $this->assertSame('DE:AD:BE:EF:00:01', $record->extra['intune_ethernet_mac']);
        // 17179869184 bytes / 1e9 = 17.179869184, rounded to 17.18 GB.
        $this->assertSame(17.18, $record->extra['intune_physical_memory_gb']);
    }

    public function test_admin_defined_extras_overlay_on_vendor_record()
    {
        // Admin configures a Custom Extras row pointing at a Graph
        // field the adapter doesn't preset (processorArchitecture),
        // mapped to a Snipe-IT custom field.
        $customField = CustomField::factory()->create(['element' => 'text', 'name' => 'CPU arch']);
        $adapter = $this->configuredAdapter();
        $instance = SyncAdapterInstance::where('slug', 'intune')->firstOrFail();

        SyncAdapterConfig::put($instance->id, 'field_paths', json_encode([
            'custom:'.$customField->id => 'processorArchitecture',
        ]));
        SyncAdapterConfig::put($instance->id, 'mapping.custom:'.$customField->id, 'custom:'.$customField->id);

        Http::fake([
            '*/oauth2/v2.0/token' => Http::response(['access_token' => 'stub-bearer', 'expires_in' => 3600]),
            '*/v1.0/deviceManagement/managedDevices*' => Http::response([
                'value' => [[
                    'id' => 'guid-cpu',
                    'deviceName' => 'host-cpu',
                    'serialNumber' => 'SN-CPU',
                    'processorArchitecture' => 'arm64',
                ]],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];

        $this->assertSame('arm64', $record->extra['custom:'.$customField->id]);
    }

    public function test_admin_defined_extras_support_dot_path_into_nested_vendor_fields()
    {
        // Deeper Graph fields (e.g. hardwareInformation.deviceGuardLocalSystemAuthorityCredentialGuardState)
        // are reachable via dot-path. Verify the walker handles nesting.
        $customField = CustomField::factory()->create(['element' => 'text', 'name' => 'Device Guard State']);
        $adapter = $this->configuredAdapter();
        $instance = SyncAdapterInstance::where('slug', 'intune')->firstOrFail();

        SyncAdapterConfig::put($instance->id, 'field_paths', json_encode([
            'custom:'.$customField->id => 'hardwareInformation.deviceGuardState',
        ]));
        SyncAdapterConfig::put($instance->id, 'mapping.custom:'.$customField->id, 'custom:'.$customField->id);

        Http::fake([
            '*/oauth2/v2.0/token' => Http::response(['access_token' => 'stub-bearer', 'expires_in' => 3600]),
            '*/v1.0/deviceManagement/managedDevices*' => Http::response([
                'value' => [[
                    'id' => 'guid-dg',
                    'deviceName' => 'host-dg',
                    'serialNumber' => 'SN-DG',
                    'hardwareInformation' => ['deviceGuardState' => 'Running'],
                ]],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];

        $this->assertSame('Running', $record->extra['custom:'.$customField->id]);
    }

    public function test_admin_defined_extras_surface_in_mapping_targets_picker()
    {
        $customField = CustomField::factory()->create(['element' => 'text', 'name' => 'CPU arch']);
        $adapter = $this->configuredAdapter();
        $instance = SyncAdapterInstance::where('slug', 'intune')->firstOrFail();

        SyncAdapterConfig::put($instance->id, 'field_paths', json_encode([
            'custom:'.$customField->id => 'processorArchitecture',
        ]));

        $extraFields = $adapter->extraFields();

        $this->assertArrayHasKey('custom:'.$customField->id, $extraFields);
        $this->assertTrue($extraFields['custom:'.$customField->id]['admin_defined']);
        $this->assertArrayHasKey('intune_wifi_mac', $extraFields);
        $this->assertArrayHasKey('intune_imei', $extraFields);
    }

    public function test_adapter_opts_into_custom_extras_widget()
    {
        $adapter = $this->configuredAdapter();

        $this->assertTrue($adapter->supportsAdminDefinedExtras());
    }

    public function test_dot_path_get_walks_arrays_strings_and_numeric_indices()
    {
        $this->assertSame('top', SyncAdapter::dotPathGet(['a' => 'top'], 'a'));
        $this->assertSame('nested', SyncAdapter::dotPathGet(['a' => ['b' => 'nested']], 'a.b'));
        $this->assertSame('item', SyncAdapter::dotPathGet(['a' => [['b' => 'item']]], 'a.0.b'));
        $this->assertNull(SyncAdapter::dotPathGet(['a' => 'top'], 'a.b'));
        $this->assertNull(SyncAdapter::dotPathGet(['a' => 'top'], 'missing'));
    }

    private function configuredAdapter(): IntuneAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'intune')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'url', 'https://graph.microsoft.com');
        SyncAdapterConfig::put($instance->id, 'tenant_id', 'stub-tenant');
        SyncAdapterConfig::put($instance->id, 'client_id', 'stub-client');
        SyncAdapterConfig::put($instance->id, 'client_secret', Crypt::encrypt('fake-secret'));

        return new IntuneAdapter($instance->fresh());
    }
}
