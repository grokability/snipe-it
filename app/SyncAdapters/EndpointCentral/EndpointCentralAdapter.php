<?php

namespace App\SyncAdapters\EndpointCentral;

use App\Models\Asset;
use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\PushableSyncAdapter;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * ManageEngine Endpoint Central adapter. Pulls managed-computer
 * inventory from a cloud OR on-prem Endpoint Central tenant and
 * normalizes it into HostInventoryRecord objects. Also pushes
 * Snipe-IT's asset_tag back to the vendor as the device's "Remarks"
 * freeform field (the writable free-text slot ME exposes per
 * computer).
 *
 * Deployment variants:
 *
 *   Cloud: hosted on Zoho's infrastructure, OAuth 2.0 refresh-token
 *   flow against accounts.zoho.{region}. Admin generates a refresh
 *   token at api-console.zoho.com with scopes DesktopCentralCloud.SOM
 *   (plus .Inventory for the hardware opt-in and .SOM.CREATE for
 *   push) and pastes the client ID + secret + refresh token into
 *   Snipe-IT. The data_center setting selects the right regional
 *   accounts host.
 *
 *   On-prem: admin issues an API Key / AuthToken from Admin ->
 *   Integrations in the on-prem console and pastes it in. Snipe-IT
 *   sends it as a Zoho-authtoken header per ME's historical scheme.
 *   FIXME: unverified against a modern on-prem tenant.
 *
 * Serial resolution: the list endpoint omits hardware serial. An
 * opt-in setting enables a per-device detail fetch at pull time
 * so adopt_by_serial works. Costs an extra API call per device per
 * sync when the opt-in is on.
 *
 * ME API docs: https://www.manageengine.com/products/desktop-central/help/api/cloud/api-index.html
 */
class EndpointCentralAdapter extends PushableSyncAdapter
{
    private const DATA_CENTERS = [
        'us' => 'US (accounts.zoho.com)',
        'eu' => 'EU (accounts.zoho.eu)',
        'in' => 'India (accounts.zoho.in)',
        'au' => 'Australia (accounts.zoho.com.au)',
        'cn' => 'China (accounts.zoho.com.cn)',
        'jp' => 'Japan (accounts.zoho.jp)',
    ];

    public static function typeLabel(): string
    {
        return 'ManageEngine Endpoint Central';
    }

    public static function typeSlug(): string
    {
        return 'endpoint_central';
    }

    public static function docsUrl(): ?string
    {
        return 'https://www.manageengine.com/products/desktop-central/help/api/cloud/api-index.html';
    }

    public function baseUrlPlaceholder(): ?string
    {
        return 'https://desktopcentralcloud.manageengine.com';
    }

    public function settingsSchema(): array
    {
        return [
            [
                'key' => 'mode',
                'label' => trans('admin/settings/sync_adapters.endpoint_central_label_mode'),
                'type' => 'select',
                'options' => [
                    EndpointCentralClient::MODE_CLOUD => trans('admin/settings/sync_adapters.endpoint_central_option_mode_cloud'),
                    EndpointCentralClient::MODE_ONPREM => trans('admin/settings/sync_adapters.endpoint_central_option_mode_onprem'),
                ],
                'default' => EndpointCentralClient::MODE_CLOUD,
                'help' => trans('admin/settings/sync_adapters.endpoint_central_mode_help'),
            ],
            // Cloud-only credentials.
            [
                'key' => 'data_center',
                'label' => trans('admin/settings/sync_adapters.endpoint_central_label_data_center'),
                'type' => 'select',
                'options' => self::DATA_CENTERS,
                'default' => 'us',
                'required' => false,
                'help' => trans('admin/settings/sync_adapters.endpoint_central_data_center_help'),
            ],
            [
                'key' => 'client_id',
                'label' => trans('admin/settings/sync_adapters.label_client_id'),
                'required' => false,
                'help' => trans('admin/settings/sync_adapters.endpoint_central_client_id_help'),
            ],
            [
                'key' => 'client_secret',
                'label' => trans('admin/settings/sync_adapters.label_client_secret'),
                'secret' => true,
                'required' => false,
                'help' => trans('admin/settings/sync_adapters.endpoint_central_client_secret_help'),
            ],
            [
                'key' => 'refresh_token',
                'label' => trans('admin/settings/sync_adapters.endpoint_central_label_refresh_token'),
                'secret' => true,
                'required' => false,
                'help' => trans('admin/settings/sync_adapters.endpoint_central_refresh_token_help'),
            ],
            // On-prem-only credential.
            [
                'key' => 'auth_token',
                'label' => trans('admin/settings/sync_adapters.endpoint_central_label_auth_token'),
                'secret' => true,
                'required' => false,
                'help' => trans('admin/settings/sync_adapters.endpoint_central_auth_token_help'),
            ],
            // Opt-in N+1 hardware fetch for serial resolution.
            [
                'key' => 'fetch_hardware_details',
                'label' => trans('admin/settings/sync_adapters.endpoint_central_label_fetch_hardware_details'),
                'type' => 'checkbox',
                'required' => false,
                'help' => trans('admin/settings/sync_adapters.endpoint_central_fetch_hardware_details_help'),
            ],
        ];
    }

    public function extraFields(): array
    {
        return [
            'endpoint_central_resource_id' => ['label_key' => 'admin/settings/sync_adapters.endpoint_central_extra_resource_id'],
            'endpoint_central_live_status' => ['label_key' => 'admin/settings/sync_adapters.endpoint_central_extra_live_status'],
            'endpoint_central_agent_version' => ['label_key' => 'admin/settings/sync_adapters.extra_agent_version'],
            'endpoint_central_domain' => ['label_key' => 'admin/settings/sync_adapters.endpoint_central_extra_domain'],
            'endpoint_central_remote_office' => ['label_key' => 'admin/settings/sync_adapters.endpoint_central_extra_remote_office'],
        ];
    }

    public function supportsGroupScoping(): bool
    {
        // Remote Office is ME's per-site tenancy construct. Admins can
        // route devices to Snipe-IT companies via the group mapping UI
        // once we expose the Remote Office id as the vendor group id.
        return true;
    }

    public function vendorGroupLabel(): string
    {
        return trans('admin/settings/sync_adapters.endpoint_central_vendor_group_remote_office');
    }

    public function fetchGroups(): array
    {
        // FIXME: ME has a Remote Offices endpoint
        // (/dcapi/som/remoteOffices) that would populate this. Returning
        // an empty list for v1 so the group-mapping UI is visible but
        // callers cannot pick a vendor group until this is wired up
        // against a tenant.
        return [];
    }

    public function pull(): iterable
    {
        $client = $this->buildClient();
        $fetchHardware = $this->credentialBool('fetch_hardware_details');

        foreach ($client->computers() as $computer) {
            $hardwareDetails = null;
            if ($fetchHardware) {
                $resourceId = (string) ($computer['computerId'] ?? '');
                if ($resourceId !== '') {
                    $hardwareDetails = $client->hardwareDetails($resourceId);
                }
            }

            yield $this->normalize($computer, $hardwareDetails);
        }
    }

    private function buildClient(): EndpointCentralClient
    {
        $mode = $this->credentialOrDefault('mode', EndpointCentralClient::MODE_CLOUD);
        $dataCenter = $this->credentialOrDefault('data_center', 'us');
        $accountsHost = EndpointCentralClient::ZOHO_ACCOUNTS_HOSTS[$dataCenter]
            ?? EndpointCentralClient::ZOHO_ACCOUNTS_HOSTS['us'];

        return new EndpointCentralClient(
            mode: $mode,
            baseUrl: $this->url(),
            clientId: $this->credentialOrNull('client_id'),
            clientSecret: $this->credentialOrNull('client_secret'),
            refreshToken: $this->credentialOrNull('refresh_token'),
            accountsHost: $accountsHost,
            authToken: $this->credentialOrNull('auth_token'),
        );
    }

    /**
     * Convert one computer payload into the normalized record shape.
     * The computerId from the list endpoint is stable per enrolled
     * device and is what we key asset_external_sources on. When the
     * hardware opt-in is on, pull the serial out of
     * biosDetails[0].serialNumber (the common location per the ME
     * hardware response).
     *
     * @param  array<string, mixed>  $computer
     * @param  array<string, mixed>|null  $hardwareDetails
     */
    private function normalize(array $computer, ?array $hardwareDetails): HostInventoryRecord
    {
        // ME's list endpoint has no hardware model string. When the
        // opt-in hardware fetch is off, fall back to the osName so
        // admins get a reasonable grouping (per Windows release, per
        // macOS version, etc.) rather than a null that would reject
        // the AssetModel save. Admins who turn on fetch_hardware_details
        // get the actual motherboard model name.
        $hardwareModel = self::extractHardwareModel($hardwareDetails)
            ?? Arr::get($computer, 'osName')
            ?? Arr::get($computer, 'osPlatform');

        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: (string) Arr::get($computer, 'computerId'),
            hostname: Arr::get($computer, 'computerName'),
            hardwareSerial: self::extractSerialNumber($hardwareDetails),
            hardwareModel: $hardwareModel,
            manufacturer: self::extractManufacturer($hardwareDetails),
            primaryMac: self::firstMacAddress(Arr::get($computer, 'macAddress')),
            primaryIp: Arr::get($computer, 'ipAddress'),
            os: Arr::get($computer, 'osPlatform') ?? Arr::get($computer, 'osName'),
            osVersion: Arr::get($computer, 'osVersion'),
            lastSeen: $this->parseTimestamp(Arr::get($computer, 'lastContactTime')),
            assignedUserName: self::firstLoggedUser(Arr::get($computer, 'loggedUsers')),
            vendorGroupId: Arr::has($computer, 'remoteOfficeID') ? (string) Arr::get($computer, 'remoteOfficeID') : null,
            extra: [
                'endpoint_central_resource_id' => (string) Arr::get($computer, 'computerId'),
                'endpoint_central_live_status' => Arr::get($computer, 'liveStatus'),
                'endpoint_central_agent_version' => Arr::get($computer, 'agentVersion'),
                'endpoint_central_domain' => Arr::get($computer, 'domainName'),
                'endpoint_central_remote_office' => Arr::get($computer, 'remoteOfficeName'),
            ],
        );
    }

    /**
     * ME's hardware endpoint groups BIOS entries under biosDetails as
     * an array. The device serial lives on the first (typically only)
     * entry. motherBoardDetails[0].serialNumber is the fallback when
     * biosDetails is absent, which happens on some device classes.
     *
     * @param  array<string, mixed>|null  $hardwareDetails
     */
    private static function extractSerialNumber(?array $hardwareDetails): ?string
    {
        if ($hardwareDetails === null) {
            return null;
        }

        $candidates = [
            Arr::get($hardwareDetails, 'biosDetails.0.serialNumber'),
            Arr::get($hardwareDetails, 'motherBoardDetails.0.serialNumber'),
        ];

        foreach ($candidates as $value) {
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * Hardware model string from the motherboard or system group.
     *
     * @param  array<string, mixed>|null  $hardwareDetails
     */
    private static function extractHardwareModel(?array $hardwareDetails): ?string
    {
        if ($hardwareDetails === null) {
            return null;
        }

        $candidates = [
            Arr::get($hardwareDetails, 'motherBoardDetails.0.hardwareName'),
            Arr::get($hardwareDetails, 'motherBoardDetails.0.hardwareDescription'),
        ];

        foreach ($candidates as $value) {
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * Manufacturer string from the motherboard or BIOS group.
     *
     * @param  array<string, mixed>|null  $hardwareDetails
     */
    private static function extractManufacturer(?array $hardwareDetails): ?string
    {
        if ($hardwareDetails === null) {
            return null;
        }

        $candidates = [
            Arr::get($hardwareDetails, 'motherBoardDetails.0.hardwareManufacturer'),
            Arr::get($hardwareDetails, 'biosDetails.0.hardwareManufacturer'),
        ];

        foreach ($candidates as $value) {
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * ME returns macAddress as a comma-separated list when a device
     * has multiple interfaces. Grab the first non-empty entry for
     * the normalized primaryMac slot. The rest live on the raw
     * payload and admins can map them to custom fields separately.
     */
    private static function firstMacAddress(mixed $raw): ?string
    {
        if (! is_string($raw) || $raw === '') {
            return null;
        }

        foreach (explode(',', $raw) as $candidate) {
            $candidate = trim($candidate);
            if ($candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * loggedUsers is a comma-separated list of recent logged-in
     * accounts, newest first per ME's convention. We take the first
     * for the normalized assignedUserName slot and let admins map
     * the raw string to a custom field if they want the full list.
     */
    private static function firstLoggedUser(mixed $raw): ?string
    {
        if (! is_string($raw) || $raw === '') {
            return null;
        }

        foreach (explode(',', $raw) as $candidate) {
            $candidate = trim($candidate);
            if ($candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }

    private function parseTimestamp(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            // ME emits timestamps as both ISO 8601 strings and Unix epoch
            // milliseconds depending on endpoint version. Numeric input
            // in the plausible ms range is treated as epoch milliseconds.
            if (is_numeric($value) && $value >= 1_000_000_000_000 && $value <= 100_000_000_000_000) {
                return Carbon::createFromTimestampMsUTC((int) $value);
            }

            return Carbon::parse($value);
        } catch (\Throwable $e) {
            Log::channel('sync-adapters')->warning(
                $this->name().': skipped unparseable Endpoint Central timestamp ('.var_export($value, true).'): '.$e->getMessage()
            );

            return null;
        }
    }

    private function credentialOrNull(string $key): ?string
    {
        try {
            $value = $this->credential($key);
        } catch (\Throwable) {
            return null;
        }

        return $value === '' ? null : $value;
    }

    private function credentialOrDefault(string $key, string $default): string
    {
        $value = $this->credentialOrNull($key);

        return $value ?? $default;
    }

    private function credentialBool(string $key): bool
    {
        return $this->credentialOrNull($key) === '1';
    }

    /**
     * ME exposes Remarks as the writable per-computer freeform slot
     * admins commonly use for inventory tags. canPush is always true
     * for a configured instance since the write capability is tied to
     * the OAuth scope / API token (gated at auth time rather than
     * per-request).
     */
    public function canPush(): bool
    {
        return true;
    }

    /**
     * ME's "Remarks" freeform field is a plausible notes target but
     * we already route asset_tag there (the writable per-computer
     * slot). Keeping notes unmapped for v1 so admins can configure
     * push-asset_tag without accidentally overwriting Remarks via
     * two paths. Revisit once ME exposes a separate notes field.
     */
    public function notesFieldTarget(): ?string
    {
        return null;
    }

    /**
     * Snipe-IT asset_tag flows to ME's Remarks freeform field.
     *
     * @param  array<int, string>  $changedFields
     */
    public function push(Asset $asset, array $changedFields = []): bool
    {
        return $this->pushViaSinglePayload($asset, $changedFields);
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<int, string>}
     */
    protected function buildPushPayload(Asset $asset): array
    {
        $payload = [];
        $pushed = [];

        if (in_array('asset_tag', $this->pushDirectedFields(), true)) {
            $value = $this->assetValueForSourceField($asset, 'asset_tag');
            if (is_string($value) && $value !== '') {
                $payload['remarks'] = $value;
                $pushed[] = 'asset_tag';
            }
        }

        return [$payload, $pushed];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function dispatchPush(\App\Models\AssetExternalSource $externalSource, array $payload): void
    {
        $this->buildClient()->updateRemarks($externalSource->external_id, $payload);
    }
}
