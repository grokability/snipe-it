<?php

namespace App\SyncAdapters\Intune;

use App\Models\Asset;
use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\PushableAdapter;
use App\SyncAdapters\Support\ConfigurableAdapter;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Microsoft Intune adapter. Pulls managed devices from Microsoft Graph
 * (/v1.0/deviceManagement/managedDevices) via an OAuth 2.0
 * client-credentials app registration and normalizes them into
 * HostInventoryRecord objects. Also pushes composed notes back to
 * Intune's managed-device notes field via Graph beta, matching what
 * community integrations like Brady Widener's PowerShell script do.
 *
 * Sovereign clouds change both the Graph URL and the token endpoint
 * (US Gov uses graph.microsoft.us + login.microsoftonline.us). The
 * Base URL field on the settings page controls the Graph endpoint.
 * The login host is derived from it by swapping the second-level
 * label so a single URL config drives both.
 *
 * Non-secret credentials (tenant + client id) stay plain-text at rest
 * so admins can audit which Azure app registration a Snipe-IT instance
 * is talking to without a decrypt step. Only client_secret is encrypted.
 */
class IntuneAdapter extends ConfigurableAdapter implements PushableAdapter
{
    public static function typeLabel(): string
    {
        return 'Microsoft Intune';
    }

    public function baseUrlPlaceholder(): ?string
    {
        return 'https://graph.microsoft.com';
    }

    public function credentialSchema(): array
    {
        return [
            [
                'key' => 'tenant_id',
                'label' => 'Tenant ID',
                'help' => trans('admin/settings/sync_adapters.intune_tenant_id_help'),
            ],
            [
                'key' => 'client_id',
                'label' => 'Client ID',
                'help' => trans('admin/settings/sync_adapters.intune_client_id_help'),
            ],
            [
                'key' => 'client_secret',
                'label' => 'Client Secret',
                'secret' => true,
                'help' => trans('admin/settings/sync_adapters.intune_client_secret_help'),
            ],
        ];
    }

    public function pull(): iterable
    {
        $graphBaseUrl = $this->url();

        $client = new IntuneClient(
            graphBaseUrl: $graphBaseUrl,
            loginBaseUrl: $this->deriveLoginBaseUrl($graphBaseUrl),
            tenantId: $this->credential('tenant_id'),
            clientId: $this->credential('client_id'),
            clientSecret: $this->credential('client_secret'),
        );

        foreach ($client->managedDevices() as $device) {
            yield $this->normalize($device);
        }
    }

    /**
     * Derive the OAuth login host from the Graph host so sovereign
     * clouds (US Gov, China) work off a single URL config. Falls
     * back to the public Microsoft login endpoint when the base URL
     * doesn't parse cleanly.
     */
    private function deriveLoginBaseUrl(string $graphBaseUrl): string
    {
        $host = parse_url($graphBaseUrl, PHP_URL_HOST);
        if (! is_string($host)) {
            return 'https://login.microsoftonline.com';
        }

        $tld = substr($host, strrpos($host, '.') + 1);

        return match ($tld) {
            'us' => 'https://login.microsoftonline.us',
            'cn' => 'https://login.chinacloudapi.cn',
            default => 'https://login.microsoftonline.com',
        };
    }

    /**
     * Convert a Graph managedDevice payload into the normalized record
     * shape. Graph's stable id is `id` (a GUID); we key
     * asset_external_sources on it. Ethernet MAC is preferred over
     * WiFi because it's more likely to be a hardware-inventory MAC
     * that survives OS reinstall.
     *
     * @param  array<string, mixed>  $device
     */
    private function normalize(array $device): HostInventoryRecord
    {
        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: (string) Arr::get($device, 'id'),
            hostname: Arr::get($device, 'deviceName'),
            hardwareSerial: Arr::get($device, 'serialNumber'),
            hardwareModel: Arr::get($device, 'model'),
            manufacturer: Arr::get($device, 'manufacturer'),
            primaryMac: Arr::get($device, 'ethernetMacAddress')
                ?? Arr::get($device, 'wiFiMacAddress'),
            primaryIp: null,
            os: Arr::get($device, 'operatingSystem'),
            osVersion: Arr::get($device, 'osVersion'),
            lastSeen: $this->parseTimestamp(Arr::get($device, 'lastSyncDateTime')),
            assignedUserEmail: Arr::get($device, 'userPrincipalName')
                ?? Arr::get($device, 'emailAddress'),
            extra: [
                'intune_compliance_state' => Arr::get($device, 'complianceState'),
                'intune_enrolled_at' => Arr::get($device, 'enrolledDateTime'),
                'intune_management_agent' => Arr::get($device, 'managementAgent'),
                'intune_ownership' => Arr::get($device, 'managedDeviceOwnerType'),
            ],
        );
    }

    private function parseTimestamp(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value);
    }

    /**
     * Push always available for a configured Intune instance. The
     * Graph beta endpoint requires the app registration to have the
     * DeviceManagementManagedDevices.ReadWrite.All permission
     * (admin consent required, same as the read scope but broader).
     */
    public function canPush(): bool
    {
        return true;
    }

    /**
     * Intune managed devices carry a freeform `notes` field on the
     * Graph beta endpoint. That's the field Brady Widener's community
     * PowerShell script targets and is the most-asked-for Intune
     * push shape.
     */
    public function notesFieldTarget(): ?string
    {
        return 'notes';
    }

    /**
     * Push composed notes to Intune's managed-device notes field.
     * asset_tag isn't first-class-writable on Intune (Graph v1.0
     * doesn't expose it and the beta shape varies), so v1 supports
     * composed notes only. Admins who want asset_tag on Intune
     * embed it in the notes template.
     *
     * @param  array<int, string>  $changedFields
     */
    public function push(Asset $asset, array $changedFields = []): void
    {
        $externalSource = $this->pushPrologue($asset, $changedFields);
        if ($externalSource === null) {
            return;
        }

        $composed = $this->composeNotesForPush($asset);
        if ($composed === '') {
            return;
        }

        $payload = [$this->effectiveNotesTarget() => $composed];

        if ($this->isPushDryRun()) {
            Log::channel('sync-adapters')->info(sprintf(
                '%s push [dry-run]: would PATCH Intune managed device %s with %s',
                $this->name(),
                $externalSource->external_id,
                json_encode($payload, JSON_UNESCAPED_SLASHES),
            ));

            return;
        }

        $graphBaseUrl = $this->url();
        $client = new IntuneClient(
            graphBaseUrl: $graphBaseUrl,
            loginBaseUrl: $this->deriveLoginBaseUrl($graphBaseUrl),
            tenantId: $this->credential('tenant_id'),
            clientId: $this->credential('client_id'),
            clientSecret: $this->credential('client_secret'),
        );
        $client->updateManagedDevice($externalSource->external_id, $payload);

        Log::channel('sync-adapters')->info(sprintf(
            '%s push: updated Intune managed device %s (%s)',
            $this->name(),
            $externalSource->external_id,
            $this->effectiveNotesTarget(),
        ));
    }
}
