<?php

namespace App\SyncAdapters\NinjaOne;

use App\Models\Asset;
use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\PushableAdapter;
use App\SyncAdapters\Support\ConfigurableAdapter;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * NinjaOne (formerly NinjaRMM) adapter. Pulls managed devices from the
 * REST API v2 (/v2/devices-detailed) via an OAuth 2.0 client-credentials
 * application, keyed on NinjaOne's stable device id.
 *
 * NinjaOne is region-hosted: `app.ninjarmm.com` (US), `eu.ninjarmm.com`
 * (EU), `oc.ninjarmm.com` (AU/NZ), `ca.ninjarmm.com` (CA). Admins pick
 * the region-appropriate host in the Base URL field so the OAuth token
 * endpoint and the devices endpoint stay on the same host (NinjaOne
 * doesn't split the two the way Microsoft Graph does).
 *
 * client_id is non-secret per OAuth 2.0 convention and stored plain
 * text so admins can audit which OAuth application a Snipe-IT instance
 * is talking to. client_secret is encrypted at rest.
 *
 * Push story: NinjaOne has no first-class asset_tag field, but the
 * admin can create per-device Custom Fields under Administration ->
 * Devices -> Custom Fields. Push writes to whichever custom field name
 * the admin puts in the "Asset Tag Custom Field Name" schema slot. If
 * empty, push silently no-ops (nothing to write to).
 */
class NinjaOneAdapter extends ConfigurableAdapter implements PushableAdapter
{
    public static function typeLabel(): string
    {
        return 'NinjaOne';
    }

    public function baseUrlPlaceholder(): ?string
    {
        return 'https://app.ninjarmm.com';
    }

    public function credentialSchema(): array
    {
        return [
            [
                'key' => 'client_id',
                'label' => 'Client ID',
                'help' => trans('admin/settings/sync_adapters.ninjaone_client_id_help'),
            ],
            [
                'key' => 'client_secret',
                'label' => 'Client Secret',
                'secret' => true,
                'help' => trans('admin/settings/sync_adapters.ninjaone_client_secret_help'),
            ],
            [
                'key' => 'asset_tag_custom_field',
                'label' => 'Asset Tag Custom Field Name',
                'required' => false,
                'help' => trans('admin/settings/sync_adapters.ninjaone_asset_tag_field_help'),
            ],
        ];
    }

    public function pull(): iterable
    {
        $client = new NinjaOneClient(
            baseUrl: $this->url(),
            clientId: $this->credential('client_id'),
            clientSecret: $this->credential('client_secret'),
        );

        foreach ($client->devices() as $device) {
            yield $this->normalize($device);
        }
    }

    /**
     * Convert a NinjaOne device payload into the normalized record
     * shape. NinjaOne's `id` is a stable long integer per registered
     * agent. We key asset_external_sources on it as a string.
     *
     * Hostname preference walks systemName -> dnsName -> netbiosName
     * because Ninja fills them opportunistically depending on OS and
     * agent version. Serial preference walks the SMBIOS system serial
     * first, then falls back to the BIOS serial (VM hosts often only
     * populate one or the other).
     *
     * @param  array<string, mixed>  $device
     */
    private function normalize(array $device): HostInventoryRecord
    {
        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: (string) Arr::get($device, 'id'),
            hostname: Arr::get($device, 'systemName')
                ?? Arr::get($device, 'dnsName')
                ?? Arr::get($device, 'netbiosName'),
            hardwareSerial: Arr::get($device, 'system.serialNumber')
                ?? Arr::get($device, 'system.biosSerialNumber')
                ?? Arr::get($device, 'serialNumber'),
            hardwareModel: Arr::get($device, 'system.model')
                ?? Arr::get($device, 'model'),
            manufacturer: Arr::get($device, 'system.manufacturer')
                ?? Arr::get($device, 'manufacturer'),
            primaryMac: null, // Ninja returns per-adapter MACs via a separate endpoint; not worth an extra call per device today
            primaryIp: Arr::get($device, 'publicIP'),
            os: Arr::get($device, 'os.name') ?? Arr::get($device, 'nodeClass'),
            osVersion: Arr::get($device, 'os.buildNumber')
                ?? Arr::get($device, 'os.releaseId'),
            lastSeen: $this->parseTimestamp(Arr::get($device, 'lastContact')),
            assignedUserEmail: null,
            assignedUserName: Arr::get($device, 'userTitle'),
            vendorGroupId: (string) (Arr::get($device, 'organizationId') ?? ''),
            extra: [
                'ninjaone_node_class' => Arr::get($device, 'nodeClass'),
                'ninjaone_organization_id' => Arr::get($device, 'organizationId'),
                'ninjaone_location_id' => Arr::get($device, 'locationId'),
                'ninjaone_offline' => Arr::get($device, 'offline'),
                'ninjaone_chassis_type' => Arr::get($device, 'chassisType'),
            ],
        );
    }

    /**
     * NinjaOne emits lastContact as a Unix epoch (seconds, sometimes
     * fractional). Bail on absent / zero values so we don't record
     * "device last seen in 1970".
     */
    private function parseTimestamp(mixed $value): ?Carbon
    {
        if ($value === null || $value === '' || $value === 0) {
            return null;
        }

        return Carbon::createFromTimestamp((float) $value);
    }

    /**
     * NinjaOne push is always available in principle. Per-write
     * validity depends on the admin having created the custom field
     * whose name is in the schema slot. Not a canPush gate because
     * "field not configured" is different from "vendor doesn't
     * support push at all".
     */
    public function canPush(): bool
    {
        return true;
    }

    /**
     * NinjaOne has no first-class freeform notes field. Composed
     * notes would require another admin-configured custom field
     * (same pattern as asset_tag) which we can add if there's demand.
     */
    public function notesFieldTarget(): ?string
    {
        return null;
    }

    /**
     * Push Snipe-IT asset_tag to a NinjaOne custom field. NinjaOne
     * doesn't expose a top-level asset_tag column, so the admin must
     * pre-create a custom field in the NinjaOne dashboard and put its
     * name in the "Asset Tag Custom Field Name" schema slot. If the
     * slot is blank, push silently no-ops (nothing to write to).
     * If the slot has a name but no such field exists in NinjaOne,
     * NinjaOne returns a 4xx and the error surfaces in the log.
     *
     * @param  array<int, string>  $changedFields
     */
    public function push(Asset $asset, array $changedFields = []): void
    {
        $externalSource = $this->pushPrologue($asset, $changedFields);
        if ($externalSource === null) {
            return;
        }

        $customFieldName = $this->credentialOrNull('asset_tag_custom_field');
        if ($customFieldName === null || $customFieldName === '') {
            Log::channel('sync-adapters')->info(sprintf(
                '%s push: skipped asset %d (no Ninja custom field name configured)',
                $this->name(),
                $asset->id,
            ));

            return;
        }

        $payload = $this->buildCustomFieldPayload($asset, $customFieldName);
        if ($payload === []) {
            return;
        }

        if ($this->isPushDryRun()) {
            Log::channel('sync-adapters')->info(sprintf(
                '%s push [dry-run]: would PATCH Ninja device %s custom-fields %s',
                $this->name(),
                $externalSource->external_id,
                json_encode($payload, JSON_UNESCAPED_SLASHES),
            ));

            return;
        }

        $client = new NinjaOneClient(
            baseUrl: $this->url(),
            clientId: $this->credential('client_id'),
            clientSecret: $this->credential('client_secret'),
        );
        $client->updateDeviceCustomFields($externalSource->external_id, $payload);

        Log::channel('sync-adapters')->info(sprintf(
            '%s push: updated Ninja device %s custom field "%s"',
            $this->name(),
            $externalSource->external_id,
            $customFieldName,
        ));
    }

    /**
     * Assemble the /custom-fields PATCH payload. Ninja accepts
     * multiple field name / value pairs in one request, so asset_tag
     * and the composed-notes target get merged when both are
     * configured. Admin sets the notes-target field via the composed-
     * notes fieldset override; nothing is defaulted because Ninja
     * has no built-in notes concept for us to guess.
     *
     * @return array<string, mixed>
     */
    private function buildCustomFieldPayload(Asset $asset, string $assetTagFieldName): array
    {
        $payload = [];

        $value = in_array('asset_tag', $this->pushDirectedFields(), true)
            ? $this->assetValueForSourceField($asset, 'asset_tag')
            : null;
        if ($value !== null && $value !== '') {
            $payload[$assetTagFieldName] = $value;
        }

        $composed = $this->composeNotesForPush($asset);
        if ($composed !== '') {
            $payload[$this->effectiveNotesTarget()] = $composed;
        }

        return $payload;
    }

    private function assetValueForSourceField(Asset $asset, string $field): mixed
    {
        return match ($field) {
            'asset_tag' => $asset->asset_tag,
            default => null,
        };
    }

    /**
     * credential() throws when the key is absent. For optional fields
     * (required=false in the schema) we want a nullable read, so wrap
     * with a catch and return null.
     */
    private function credentialOrNull(string $key): ?string
    {
        try {
            return $this->credential($key);
        } catch (\Throwable) {
            return null;
        }
    }
}
