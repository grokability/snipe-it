<?php

namespace App\SyncAdapters\WorkspaceOne;

use App\Models\Asset;
use App\Models\AssetExternalSource;
use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\PushableAdapter;
use App\SyncAdapters\Support\ConfigurableAdapter;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Omnissa Workspace ONE adapter (formerly VMware Workspace ONE UEM,
 * originally AirWatch). Pulls managed devices from the REST device
 * search endpoint via OAuth 2.0 client credentials. Also pushes
 * Snipe-IT asset_tag back via WS1's AssetNumber field on the
 * device-update endpoint.
 *
 * Modern Workspace ONE tenants use OAuth 2.0 client credentials
 * against a per-region auth endpoint (na.uemauth.vmwservices.com,
 * emea.uemauth.vmwservices.com, apac.uemauth.vmwservices.com, etc.).
 * The access token is carried as `Authorization: Bearer` on Dashboard
 * API calls plus an `aw-tenant-code` header identifying which tenant
 * to scope the call to. tenant_code and client_id are non-secret.
 * Only client_secret encrypts at rest.
 *
 * The Base URL field on the settings page controls the Dashboard API
 * host (e.g. asXXX.awmdm.com). The region-appropriate auth endpoint
 * is derived from the same host convention below.
 *
 * Legacy tenants on Basic-auth + admin credentials aren't covered by
 * this schema. If someone needs it, a WorkspaceOneBasicAuthAdapter
 * sibling with a different schema is the right shape rather than
 * making this schema conditional on auth mode.
 */
class WorkspaceOneAdapter extends ConfigurableAdapter implements PushableAdapter
{
    public static function typeLabel(): string
    {
        return 'Omnissa Workspace ONE';
    }

    public function baseUrlPlaceholder(): ?string
    {
        return 'https://as###.awmdm.com';
    }

    public function credentialSchema(): array
    {
        return [
            [
                'key' => 'tenant_code',
                'label' => 'Tenant Code',
                'help' => trans('admin/settings/general.sync_adapter_workspace_one_tenant_code_help'),
            ],
            [
                'key' => 'client_id',
                'label' => 'Client ID',
                'help' => trans('admin/settings/general.sync_adapter_workspace_one_client_id_help'),
            ],
            [
                'key' => 'client_secret',
                'label' => 'Client Secret',
                'secret' => true,
                'help' => trans('admin/settings/general.sync_adapter_workspace_one_client_secret_help'),
            ],
        ];
    }

    public function pull(): iterable
    {
        $apiBaseUrl = $this->url();

        $client = new WorkspaceOneClient(
            apiBaseUrl: $apiBaseUrl,
            authBaseUrl: $this->deriveAuthBaseUrl($apiBaseUrl),
            tenantCode: $this->credential('tenant_code'),
            clientId: $this->credential('client_id'),
            clientSecret: $this->credential('client_secret'),
        );

        foreach ($client->devices() as $device) {
            yield $this->normalize($device);
        }
    }

    /**
     * Derive the region-scoped OAuth token host from the API host.
     * Workspace ONE uses a per-region auth endpoint. asXXX.awmdm.com
     * → na.uemauth.vmwservices.com etc. The mapping is inferred from
     * the first two chars of the API host (as-, ds-, uat-).
     * Falls back to the NA endpoint when the base URL doesn't parse.
     */
    private function deriveAuthBaseUrl(string $apiBaseUrl): string
    {
        $host = parse_url($apiBaseUrl, PHP_URL_HOST);
        if (! is_string($host)) {
            return 'https://na.uemauth.vmwservices.com';
        }

        $prefix = strtolower(substr($host, 0, 3));

        return match ($prefix) {
            'apa' => 'https://apac.uemauth.vmwservices.com',
            'eme' => 'https://emea.uemauth.vmwservices.com',
            'uat' => 'https://uat.uemauth.vmwservices.com',
            default => 'https://na.uemauth.vmwservices.com',
        };
    }

    /**
     * Convert a Workspace ONE device payload into the normalized record
     * shape. Uuid is the stable identifier across renames + re-enrollments
     * so we key asset_external_sources on it. UserEmailAddress is the
     * OS-level assignment. UserName is the enrollment account and can
     * differ.
     *
     * @param  array<string, mixed>  $device
     */
    private function normalize(array $device): HostInventoryRecord
    {
        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: (string) (Arr::get($device, 'Uuid') ?? Arr::get($device, 'Id.Value')),
            hostname: Arr::get($device, 'DeviceFriendlyName')
                ?? Arr::get($device, 'HostName'),
            hardwareSerial: Arr::get($device, 'SerialNumber'),
            hardwareModel: Arr::get($device, 'Model'),
            manufacturer: Arr::get($device, 'DeviceManufacturer'),
            primaryMac: Arr::get($device, 'MacAddress'),
            primaryIp: Arr::get($device, 'DeviceNetworkInfo.0.IPAddress')
                ?? Arr::get($device, 'IpAddress'),
            os: Arr::get($device, 'Platform') ?? Arr::get($device, 'OperatingSystem'),
            osVersion: Arr::get($device, 'OperatingSystem'),
            lastSeen: $this->parseTimestamp(Arr::get($device, 'LastSeen')),
            assignedUserEmail: Arr::get($device, 'UserEmailAddress'),
            assignedUserName: Arr::get($device, 'UserName'),
            extra: [
                'workspace_one_enrollment_status' => Arr::get($device, 'EnrollmentStatus'),
                'workspace_one_compliance_status' => Arr::get($device, 'ComplianceStatus'),
                'workspace_one_ownership' => Arr::get($device, 'Ownership'),
                'workspace_one_organization_group' => Arr::get($device, 'LocationGroupName'),
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
     * WS1 writes need the OAuth application's role to include
     * device-write scope. Same credential used for pull works when
     * scoped correctly. No tier gate.
     */
    public function canPush(): bool
    {
        return true;
    }

    /**
     * Workspace ONE's device record has no first-class freeform
     * notes field admins can rely on. Custom Attributes exist but
     * need per-tenant admin setup, same shape as NinjaOne's custom
     * fields. Return null for now. A future extension could accept
     * a Custom Attribute name in the credential schema.
     */
    public function notesFieldTarget(): ?string
    {
        return null;
    }

    /**
     * Push Snipe-IT asset_tag to WS1's AssetNumber field via the
     * device-update endpoint. Keys the device by UUID (WS1's stable
     * identifier across enrollment renames), which we cached as
     * external_id during pull.
     *
     * @param  array<int, string>  $changedFields
     */
    public function push(Asset $asset, array $changedFields = []): void
    {
        // Don't early-exit on empty pushFields. Composed notes may
        // still be configured without any per-field direction=push.
        // Only skip when we have push fields AND the caller told us
        // none of them are in the changed set.
        $pushFields = $this->pushDirectedFields();
        if ($pushFields !== [] && $changedFields !== []
            && array_intersect($changedFields, $pushFields) === []) {
            return;
        }

        $externalSource = AssetExternalSource::query()
            ->where('asset_id', $asset->id)
            ->where('source', $this->name())
            ->first();

        if ($externalSource === null) {
            return;
        }

        $payload = [];
        foreach ($pushFields as $field) {
            $mapped = self::sourceFieldToWs1Field($field);
            if ($mapped === null) {
                continue;
            }

            $value = $this->assetValueForSourceField($asset, $field);
            if ($value === null) {
                continue;
            }

            $payload[$mapped] = $value;
        }

        // Composed notes go to a WS1 Custom Attribute via a separate
        // endpoint from the top-level device fields (AssetNumber uses
        // PUT /devices/{uuid}, Custom Attributes use POST
        // /devices/{uuid}/customattributes). Two API calls when both
        // are configured. Admin sets the Custom Attribute name via
        // the composed-notes fieldset override. Nothing default
        // because WS1 has no built-in notes concept for us to guess.
        $notesTemplate = $this->pushNotesTemplate();
        $notesTarget = $this->effectiveNotesTarget();
        $composed = ($notesTemplate !== '' && $notesTarget !== null)
            ? \App\SyncAdapters\Support\NotesComposer::compose($asset, $notesTemplate)
            : '';

        if ($payload === [] && $composed === '') {
            return;
        }

        if ($this->isPushDryRun()) {
            if ($payload !== []) {
                Log::channel('sync-adapters')->info(sprintf(
                    '%s push [dry-run]: would PUT Workspace ONE device %s with %s',
                    $this->name(),
                    $externalSource->external_id,
                    json_encode($payload, JSON_UNESCAPED_SLASHES),
                ));
            }
            if ($composed !== '') {
                Log::channel('sync-adapters')->info(sprintf(
                    '%s push [dry-run]: would set Workspace ONE Custom Attribute %s=%s on device %s',
                    $this->name(),
                    $notesTarget,
                    $composed,
                    $externalSource->external_id,
                ));
            }

            return;
        }

        $apiBaseUrl = $this->url();
        $client = new WorkspaceOneClient(
            apiBaseUrl: $apiBaseUrl,
            authBaseUrl: $this->deriveAuthBaseUrl($apiBaseUrl),
            tenantCode: $this->credential('tenant_code'),
            clientId: $this->credential('client_id'),
            clientSecret: $this->credential('client_secret'),
        );

        if ($payload !== []) {
            $client->updateDevice($externalSource->external_id, $payload);
            Log::channel('sync-adapters')->info(sprintf(
                '%s push: updated Workspace ONE device %s with fields [%s]',
                $this->name(),
                $externalSource->external_id,
                implode(', ', array_keys($payload)),
            ));
        }

        if ($composed !== '') {
            $client->updateDeviceCustomAttribute($externalSource->external_id, $notesTarget, $composed);
            Log::channel('sync-adapters')->info(sprintf(
                '%s push: set Workspace ONE Custom Attribute "%s" on device %s',
                $this->name(),
                $notesTarget,
                $externalSource->external_id,
            ));
        }
    }

    /**
     * Map a source-field name to Workspace ONE's device-record field
     * name. AssetNumber is WS1's native asset_tag equivalent (a
     * top-level string field on the device object).
     */
    private static function sourceFieldToWs1Field(string $field): ?string
    {
        return match ($field) {
            'asset_tag' => 'AssetNumber',
            default => null,
        };
    }

    private function assetValueForSourceField(Asset $asset, string $field): mixed
    {
        return match ($field) {
            'asset_tag' => $asset->asset_tag,
            default => null,
        };
    }
}
