<?php

namespace Tests\Feature\SyncAdapters\GoogleWorkspace;

use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\GoogleWorkspace\GoogleWorkspaceAdapter;
use App\SyncAdapters\SyncAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end pull coverage for the Google Workspace (ChromeOS)
 * adapter. Fakes the OAuth token exchange at oauth2.googleapis.com
 * and the Admin SDK Directory API chromeosdevices endpoint. Verifies
 * the normalized record shape, sourceKey / sourceId routing, and OU
 * -> vendorGroupId mapping.
 */
class GoogleWorkspaceAdapterTest extends TestCase
{
    private string $privateKeyPem = '';

    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();

        $key = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => 2048,
        ]);
        $pem = '';
        openssl_pkey_export($key, $pem);
        $this->privateKeyPem = $pem;
    }

    public function test_pulls_chromeos_devices_and_creates_assets(): void
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'stub-bearer', 'expires_in' => 3300]),
            '*/devices/chromeos*' => Http::response([
                'chromeosdevices' => [
                    $this->chromeDevice(deviceId: 'guid-42', serial: 'SN-42'),
                    $this->chromeDevice(deviceId: 'guid-99', serial: 'SN-99'),
                ],
            ]),
        ]);

        foreach ($adapter->pull() as $record) {
            SyncAdapter::syncFromRecord($record);
        }

        $this->assertDatabaseCount('asset_external_sources', 2);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'google_workspace', 'external_id' => 'guid-42']);
        $this->assertDatabaseHas('asset_external_sources', ['source' => 'google_workspace', 'external_id' => 'guid-99']);
    }

    public function test_normalized_record_carries_expected_fields(): void
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'stub-bearer', 'expires_in' => 3300]),
            '*/devices/chromeos*' => Http::response([
                'chromeosdevices' => [
                    [
                        'deviceId' => 'guid-7',
                        'serialNumber' => 'CHROM-7',
                        'model' => 'HP Chromebook 14 G6',
                        'annotatedAssetId' => 'ACME-042',
                        'annotatedLocation' => 'HQ / Row 3',
                        'annotatedUser' => 'alice@example.test',
                        'ethernetMacAddress' => 'aa:bb:cc:dd:ee:ff',
                        'macAddress' => '11:22:33:44:55:66',
                        'osVersion' => '119.0.6045.184',
                        'platformVersion' => '15633.69.0',
                        'firmwareVersion' => 'Google_Careena.13434.687.0',
                        'bootMode' => 'Verified',
                        'devMode' => false,
                        'lastSync' => '2026-01-15T10:00:00Z',
                        'lastEnrollmentTime' => '2024-08-10T09:15:00Z',
                        'orgUnitPath' => '/Sales/USA',
                        'recentUsers' => [
                            ['email' => 'alice@example.test', 'type' => 'USER_TYPE_MANAGED'],
                        ],
                        'lastKnownNetwork' => [
                            ['ipAddress' => '10.0.4.42'],
                        ],
                    ],
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertCount(1, $records);

        $record = $records[0];
        $this->assertSame('google_workspace', $record->sourceKey);
        $this->assertSame('guid-7', $record->sourceId);
        $this->assertSame('ACME-042', $record->hostname);
        $this->assertSame('CHROM-7', $record->hardwareSerial);
        $this->assertSame('HP Chromebook 14 G6', $record->hardwareModel);
        $this->assertSame('aa:bb:cc:dd:ee:ff', $record->primaryMac);
        $this->assertSame('10.0.4.42', $record->primaryIp);
        $this->assertSame('ChromeOS', $record->os);
        $this->assertSame('119.0.6045.184', $record->osVersion);
        $this->assertSame('ACME-042', $record->assetTag);
        $this->assertSame('alice@example.test', $record->assignedUserEmail);
        $this->assertSame('/Sales/USA', $record->vendorGroupId);
        $this->assertNotNull($record->lastSeen);

        // Extras carry ChromeOS-specific data for the extra-field
        // mapping UI to route into custom fields.
        $this->assertSame('HQ / Row 3', $record->extra['google_workspace_annotated_location']);
        $this->assertSame('Verified', $record->extra['google_workspace_boot_mode']);
        $this->assertFalse($record->extra['google_workspace_dev_mode']);
        $this->assertSame('15633.69.0', $record->extra['google_workspace_platform_version']);
        $this->assertSame('/Sales/USA', $record->extra['google_workspace_org_unit_path']);
    }

    public function test_hostname_falls_back_to_model_plus_serial_when_annotated_asset_id_is_missing(): void
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'stub', 'expires_in' => 3300]),
            '*/devices/chromeos*' => Http::response([
                'chromeosdevices' => [
                    [
                        'deviceId' => 'guid-none',
                        'serialNumber' => 'SN-NAKED',
                        'model' => 'Lenovo Chromebook Duet 5',
                    ],
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertSame('Lenovo Chromebook Duet 5 (SN-NAKED)', $records[0]->hostname);
        $this->assertNull($records[0]->assetTag);
    }

    public function test_unknown_user_recent_user_is_treated_as_no_user(): void
    {
        // Google Admin uses "UNKNOWN_USER" as the sentinel string when
        // a Chrome device is signed in with a personal account and the
        // domain policy is set to hide personal-account details. The
        // sync path must not try to match "UNKNOWN_USER" against a
        // Snipe-IT user.
        $adapter = $this->configuredAdapter();

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'stub', 'expires_in' => 3300]),
            '*/devices/chromeos*' => Http::response([
                'chromeosdevices' => [
                    [
                        'deviceId' => 'guid-anon',
                        'serialNumber' => 'SN-ANON',
                        'model' => 'Acer Chromebook',
                        'recentUsers' => [
                            ['email' => 'UNKNOWN_USER', 'type' => 'USER_TYPE_UNMANAGED'],
                        ],
                    ],
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());
        $this->assertNull($records[0]->assignedUserEmail);
    }

    public function test_org_units_refresh_returns_flat_list_with_root(): void
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'stub', 'expires_in' => 3300]),
            '*/orgunits*' => Http::response([
                'organizationUnits' => [
                    ['orgUnitPath' => '/Sales', 'name' => 'Sales'],
                    ['orgUnitPath' => '/Sales/USA', 'name' => 'USA'],
                    ['orgUnitPath' => '/Engineering', 'name' => 'Engineering'],
                ],
            ]),
        ]);

        $groups = $adapter->fetchGroups();

        // Root OU is prepended so admins can route devices at the
        // customer-root level.
        $this->assertSame('/', $groups[0]['id']);
        $ids = array_column($groups, 'id');
        $this->assertContains('/Sales', $ids);
        $this->assertContains('/Sales/USA', $ids);
        $this->assertContains('/Engineering', $ids);
    }

    public function test_pull_succeeds_when_optional_customer_id_is_not_stored(): void
    {
        /*
         * Regression: customer_id is declared `required => false` on
         * the adapter schema, with a documented fallback to Google's
         * 'my_customer' sentinel when unset. The base-class
         * credential() helper throws RuntimeException on a null
         * stored value, so routing an optional credential through it
         * turns "admin left the field blank" into a hard sync
         * failure. The adapter now reads customer_id via
         * SyncAdapterConfig::get and applies the my_customer default
         * itself.
         */
        $instance = SyncAdapterInstance::where('slug', 'google_workspace')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'service_account_email', 'sa@example.iam.gserviceaccount.com');
        SyncAdapterConfig::put($instance->id, 'impersonate_email', 'admin@example.test');
        SyncAdapterConfig::put($instance->id, 'private_key', Crypt::encrypt($this->privateKeyPem));
        // Note the deliberate absence of customer_id.
        $adapter = new GoogleWorkspaceAdapter($instance->fresh());

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'stub-bearer', 'expires_in' => 3300]),
            '*/customer/my_customer/devices/chromeos*' => Http::response([
                'chromeosdevices' => [
                    $this->chromeDevice(deviceId: 'guid-7', serial: 'SN-7'),
                ],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());

        $this->assertCount(1, $records);
        Http::assertSent(fn ($request) => str_contains($request->url(), '/customer/my_customer/devices/chromeos'));
    }

    private function configuredAdapter(): GoogleWorkspaceAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'google_workspace')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'service_account_email', 'sa@example.iam.gserviceaccount.com');
        SyncAdapterConfig::put($instance->id, 'impersonate_email', 'admin@example.test');
        SyncAdapterConfig::put($instance->id, 'customer_id', 'my_customer');
        SyncAdapterConfig::put($instance->id, 'private_key', Crypt::encrypt($this->privateKeyPem));

        return new GoogleWorkspaceAdapter($instance->fresh());
    }

    /**
     * @return array<string, mixed>
     */
    private function chromeDevice(string $deviceId, string $serial): array
    {
        return [
            'deviceId' => $deviceId,
            'serialNumber' => $serial,
            'model' => 'Chromebook Generic',
            'orgUnitPath' => '/',
        ];
    }
}
