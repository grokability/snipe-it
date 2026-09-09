<?php

namespace Tests\Feature\SyncAdapters\AppleBusinessManager;

use App\Models\CustomField;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\SyncAdapters\AppleBusinessManager\AppleBusinessManagerAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Coverage for the ABM AppleCare enrichment path. Fetching AppleCare
 * per device adds an API call per asset, so we gate it on at least one
 * AppleCare extra being mapped to a target. Additional coverage:
 *  - the ACTIVE > PAID_UP_FRONT > later-end selection order
 *  - failure survival (transient AppleCare outage still yields a record)
 *  - date normalization to YYYY-MM-DD
 */
class AppleCareEnrichmentTest extends TestCase
{
    private string $privateKeyPem;

    protected function setUp(): void
    {
        parent::setUp();
        Statuslabel::factory()->rtd()->create();

        $key = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
        ]);
        openssl_pkey_export($key, $pem);
        $this->privateKeyPem = $pem;
    }

    public function test_pull_enriches_with_applecare_when_a_field_is_mapped()
    {
        $customField = CustomField::factory()->create(['element' => 'text', 'name' => 'Warranty End']);
        $adapter = $this->configuredAdapter();

        $instance = SyncAdapterInstance::where('slug', 'apple_business_manager')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'mapping.abm_applecare_end_date', 'custom:'.$customField->id);

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices/abm-guid-a/appleCareCoverage' => Http::response([
                'data' => [[
                    'id' => 'ac-1',
                    'type' => 'appleCareCoverage',
                    'attributes' => [
                        'agreementNumber' => 'AC-987',
                        'status' => 'ACTIVE',
                        'paymentType' => 'PAID_UP_FRONT',
                        'startDateTime' => '2025-08-15T14:22:00Z',
                        'endDateTime' => '2028-08-15T14:22:00Z',
                        'description' => 'AppleCare+ for MacBook Pro',
                        'isCanceled' => false,
                        'isRenewable' => true,
                    ],
                ]],
            ]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [$this->minimalAbmDevice('abm-guid-a')],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];

        $this->assertSame('AC-987', $record->extra['abm_applecare_agreement_number']);
        $this->assertSame('Active', $record->extra['abm_applecare_status']);
        $this->assertSame('Paid_up_front', $record->extra['abm_applecare_payment_type']);
        $this->assertSame('2025-08-15', $record->extra['abm_applecare_start_date']);
        $this->assertSame('2028-08-15', $record->extra['abm_applecare_end_date']);
        $this->assertSame('AppleCare+ for MacBook Pro', $record->extra['abm_applecare_description']);
        $this->assertFalse($record->extra['abm_applecare_is_canceled']);
        $this->assertTrue($record->extra['abm_applecare_is_renewable']);
    }

    public function test_pull_skips_applecare_fetch_when_no_field_is_mapped()
    {
        $adapter = $this->configuredAdapter();

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices/*/appleCareCoverage' => Http::response(
                ['error' => 'unreachable'],
                500,
            ),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [$this->minimalAbmDevice('abm-guid-b')],
            ]),
        ]);

        iterator_to_array($adapter->pull());

        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/appleCareCoverage'));
    }

    public function test_pull_selects_active_over_inactive_coverage()
    {
        $customField = CustomField::factory()->create(['element' => 'text', 'name' => 'Warranty']);
        $adapter = $this->configuredAdapter();

        $instance = SyncAdapterInstance::where('slug', 'apple_business_manager')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'mapping.abm_applecare_agreement_number', 'custom:'.$customField->id);

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices/dev-1/appleCareCoverage' => Http::response([
                'data' => [
                    ['attributes' => [
                        'agreementNumber' => 'INACTIVE-EXPIRED',
                        'status' => 'INACTIVE',
                        'paymentType' => 'PAID_UP_FRONT',
                        'endDateTime' => '2030-01-01T00:00:00Z',
                    ]],
                    ['attributes' => [
                        'agreementNumber' => 'ACTIVE-WINS',
                        'status' => 'ACTIVE',
                        'paymentType' => 'SUBSCRIPTION',
                        'endDateTime' => '2026-01-01T00:00:00Z',
                    ]],
                ],
            ]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [$this->minimalAbmDevice('dev-1')],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];
        // Active wins over inactive even though inactive has a
        // later end date and a paid-up-front payment.
        $this->assertSame('ACTIVE-WINS', $record->extra['abm_applecare_agreement_number']);
    }

    public function test_pull_prefers_paid_up_front_when_both_active()
    {
        $customField = CustomField::factory()->create(['element' => 'text', 'name' => 'Warranty']);
        $adapter = $this->configuredAdapter();

        $instance = SyncAdapterInstance::where('slug', 'apple_business_manager')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'mapping.abm_applecare_agreement_number', 'custom:'.$customField->id);

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices/dev-2/appleCareCoverage' => Http::response([
                'data' => [
                    ['attributes' => [
                        'agreementNumber' => 'SUB-1',
                        'status' => 'ACTIVE',
                        'paymentType' => 'SUBSCRIPTION',
                        'endDateTime' => '2030-01-01T00:00:00Z',
                    ]],
                    ['attributes' => [
                        'agreementNumber' => 'PAID-WINS',
                        'status' => 'ACTIVE',
                        'paymentType' => 'PAID_UP_FRONT',
                        'endDateTime' => '2027-01-01T00:00:00Z',
                    ]],
                ],
            ]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [$this->minimalAbmDevice('dev-2')],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];
        $this->assertSame('PAID-WINS', $record->extra['abm_applecare_agreement_number']);
    }

    public function test_pull_falls_through_to_later_end_date_when_active_and_payment_tie()
    {
        $customField = CustomField::factory()->create(['element' => 'text', 'name' => 'Warranty']);
        $adapter = $this->configuredAdapter();

        $instance = SyncAdapterInstance::where('slug', 'apple_business_manager')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'mapping.abm_applecare_agreement_number', 'custom:'.$customField->id);

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices/dev-3/appleCareCoverage' => Http::response([
                'data' => [
                    ['attributes' => [
                        'agreementNumber' => 'EARLY',
                        'status' => 'ACTIVE',
                        'paymentType' => 'PAID_UP_FRONT',
                        'endDateTime' => '2026-01-01T00:00:00Z',
                    ]],
                    ['attributes' => [
                        'agreementNumber' => 'LATE-WINS',
                        'status' => 'ACTIVE',
                        'paymentType' => 'PAID_UP_FRONT',
                        'endDateTime' => '2029-06-15T00:00:00Z',
                    ]],
                ],
            ]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [$this->minimalAbmDevice('dev-3')],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];
        $this->assertSame('LATE-WINS', $record->extra['abm_applecare_agreement_number']);
    }

    public function test_pull_survives_applecare_endpoint_failure()
    {
        $customField = CustomField::factory()->create(['element' => 'text', 'name' => 'Warranty']);
        $adapter = $this->configuredAdapter();

        $instance = SyncAdapterInstance::where('slug', 'apple_business_manager')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'mapping.abm_applecare_end_date', 'custom:'.$customField->id);

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices/*/appleCareCoverage' => Http::response(['error' => 'boom'], 503),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [$this->minimalAbmDevice('dev-fail')],
            ]),
        ]);

        $records = iterator_to_array($adapter->pull());

        // Record still yields; the applecare fields just stay absent.
        $this->assertCount(1, $records);
        $this->assertArrayNotHasKey('abm_applecare_end_date', $records[0]->extra);
    }

    public function test_empty_applecare_data_leaves_extras_untouched()
    {
        $customField = CustomField::factory()->create(['element' => 'text', 'name' => 'Warranty']);
        $adapter = $this->configuredAdapter();

        $instance = SyncAdapterInstance::where('slug', 'apple_business_manager')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'mapping.abm_applecare_end_date', 'custom:'.$customField->id);

        Http::fake([
            'account.apple.com/*' => Http::response(['access_token' => 'stub-bearer']),
            'api-business.apple.com/v1/mdmServers' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices/*/appleCareCoverage' => Http::response(['data' => []]),
            'api-business.apple.com/v1/orgDevices*' => Http::response([
                'data' => [$this->minimalAbmDevice('dev-no-ac')],
            ]),
        ]);

        $record = iterator_to_array($adapter->pull())[0];
        $this->assertArrayNotHasKey('abm_applecare_end_date', $record->extra);
    }

    private function configuredAdapter(): AppleBusinessManagerAdapter
    {
        $instance = SyncAdapterInstance::where('slug', 'apple_business_manager')->firstOrFail();
        SyncAdapterConfig::put($instance->id, 'mode', 'business');
        SyncAdapterConfig::put($instance->id, 'client_id', 'stub-client-id');
        SyncAdapterConfig::put($instance->id, 'key_id', 'stub-key-id');
        SyncAdapterConfig::put($instance->id, 'private_key', Crypt::encrypt($this->privateKeyPem));

        return new AppleBusinessManagerAdapter($instance->fresh());
    }

    /**
     * @return array<string, mixed>
     */
    private function minimalAbmDevice(string $id): array
    {
        return [
            'id' => $id,
            'attributes' => [
                'serialNumber' => 'SN-'.$id,
                'partNumber' => 'MK1E3LL/A',
                'productFamily' => 'Mac',
            ],
        ];
    }
}
