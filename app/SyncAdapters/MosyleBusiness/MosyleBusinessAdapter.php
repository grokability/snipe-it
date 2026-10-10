<?php

namespace App\SyncAdapters\MosyleBusiness;

use App\Models\Asset;
use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\PushableAdapter;
use App\SyncAdapters\SyncAdapter;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Mosyle Business adapter. Pulls device inventory from a Mosyle
 * Business v1 tenant and normalizes it into HostInventoryRecord
 * objects. Also pushes Snipe-IT-authoritative asset_tag and composed
 * notes back via Mosyle's dispatcher-style /devices endpoint.
 *
 * Sibling to MosyleAdapter (Manager v2). The two share almost all of
 * their device-field names, so normalize() is nearly identical, but
 * the auth model, endpoint URL, response wrapping, OS enum, and push
 * payload shape differ enough that they live in separate adapter +
 * client pairs. See the MosyleBusinessClient docblock for the full
 * compare.
 *
 * Key difference admins care about: Business exposes a `notes` push
 * field. This adapter wires that up through the composed-notes
 * framework.
 */
class MosyleBusinessAdapter extends SyncAdapter implements PushableAdapter
{
    public static function typeLabel(): string
    {
        return 'Mosyle Business';
    }

    public static function typeSlug(): string
    {
        return 'mosyle_business';
    }

    public static function docsUrl(): ?string
    {
        return 'https://school.mosyle.com/solutions/macos/privilege-management';
    }

    public function baseUrlPlaceholder(): ?string
    {
        return 'https://businessapi.mosyle.com/v1';
    }

    public function settingsSchema(): array
    {
        return [
            [
                'key' => 'access_token',
                'label' => trans('admin/settings/sync_adapters.label_access_token'),
                'secret' => true,
                'help' => trans('admin/settings/sync_adapters.mosyle_business_access_token_help'),
            ],
            [
                'key' => 'email',
                'label' => trans('general.email'),
                'help' => trans('admin/settings/sync_adapters.mosyle_business_email_help'),
            ],
            [
                'key' => 'password',
                'label' => trans('general.password'),
                'secret' => true,
                'help' => trans('admin/settings/sync_adapters.mosyle_business_password_help'),
            ],
        ];
    }

    /**
     * Business and Manager return almost identical device fields, so
     * the same extras set applies to both. Lang keys are reused from
     * the Manager adapter's mosyle_extra_* namespace to keep the
     * translator community from carrying 60 duplicate strings.
     */
    public function extraFields(): array
    {
        return [
            // Hardware / inventory
            'mosyle_battery' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_battery'],
            'mosyle_total_disk' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_total_disk'],
            'mosyle_available_disk' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_available_disk'],
            'mosyle_bluetooth_mac' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_bluetooth_mac'],
            'mosyle_ethernet_mac' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_ethernet_mac'],
            'mosyle_device_type' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_device_type'],
            'mosyle_build_version' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_build_version'],
            // Cellular
            'mosyle_carrier' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_carrier'],
            'mosyle_imei' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_imei'],
            'mosyle_meid' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_meid'],
            // Security / posture
            'mosyle_activation_lock_enabled' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_activation_lock_enabled', 'type' => 'boolean'],
            'mosyle_device_locator_enabled' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_device_locator_enabled', 'type' => 'boolean'],
            'mosyle_cloud_backup_enabled' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_cloud_backup_enabled', 'type' => 'boolean'],
            'mosyle_last_cloud_backup_date' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_last_cloud_backup_date'],
            'mosyle_sip_enabled' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_sip_enabled', 'type' => 'boolean'],
            'mosyle_device_attestation_status' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_device_attestation_status'],
            // MDM / lifecycle
            'mosyle_enrollment_type' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_enrollment_type'],
            'mosyle_status' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_status'],
            'mosyle_management_status' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_management_status'],
            'mosyle_os_update_status' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_os_update_status'],
            'mosyle_date_last_beat' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_date_last_beat'],
            'mosyle_date_last_push' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_date_last_push'],
            'mosyle_user_id' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_user_id'],
            'mosyle_supervised' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_supervised', 'type' => 'boolean'],
            // User / scoping
            'mosyle_user_type' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_user_type'],
            'mosyle_location' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_location'],
            'mosyle_tags' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_tags'],
            // Network
            'mosyle_last_ssid' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_last_ssid'],
            // Lost-mode fields (only populated when device is in lost mode)
            'mosyle_lost_mode_status' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_lost_mode_status'],
            'mosyle_latitude' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_latitude'],
            'mosyle_longitude' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_longitude'],
            'mosyle_altitude' => ['label_key' => 'admin/settings/sync_adapters.mosyle_extra_altitude'],
        ];
    }

    public function pull(): iterable
    {
        $client = $this->makeClient();

        foreach ($client->devices() as $device) {
            yield $this->normalize($device);
        }
    }

    /**
     * Convert a Mosyle Business device payload into the normalized
     * record shape. Field names match the Business v1 /devices list
     * response. `username` not `usename`, `last_lan_ip` for the
     * device IP, same as Manager v2.
     *
     * @param  array<string, mixed>  $device
     */
    private function normalize(array $device): HostInventoryRecord
    {
        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: (string) Arr::get($device, 'deviceudid'),
            hostname: Arr::get($device, 'device_name'),
            hardwareSerial: Arr::get($device, 'serial_number'),
            hardwareModel: Arr::get($device, 'device_model_name') ?? Arr::get($device, 'device_model'),
            manufacturer: 'Apple',
            primaryMac: Arr::get($device, 'wifi_mac_address'),
            primaryIp: Arr::get($device, 'last_lan_ip'),
            os: Arr::get($device, 'os'),
            osVersion: Arr::get($device, 'osversion'),
            lastSeen: $this->parseTimestamp(Arr::get($device, 'date_info')),
            assetTag: Arr::get($device, 'asset_tag'),
            assignedUserEmail: Arr::get($device, 'useremail'),
            assignedUserName: Arr::get($device, 'username'),
            extra: [
                // Hardware / inventory
                'mosyle_battery' => Arr::get($device, 'battery'),
                'mosyle_total_disk' => Arr::get($device, 'total_disk'),
                'mosyle_available_disk' => Arr::get($device, 'available_disk'),
                'mosyle_bluetooth_mac' => Arr::get($device, 'bluetooth_mac_address'),
                'mosyle_ethernet_mac' => Arr::get($device, 'ethernet_mac_address'),
                'mosyle_device_type' => Arr::get($device, 'device_type'),
                'mosyle_build_version' => Arr::get($device, 'BuildVersion'),
                // Cellular
                'mosyle_carrier' => Arr::get($device, 'carrier'),
                'mosyle_imei' => Arr::get($device, 'imei'),
                'mosyle_meid' => Arr::get($device, 'meid'),
                // Security / posture
                'mosyle_activation_lock_enabled' => Arr::get($device, 'isActivationLockEnabled'),
                'mosyle_device_locator_enabled' => Arr::get($device, 'isDeviceLocatorServiceEnabled'),
                'mosyle_cloud_backup_enabled' => Arr::get($device, 'isCloudBackupEnabled'),
                'mosyle_last_cloud_backup_date' => Arr::get($device, 'LastCloudBackupDate'),
                'mosyle_sip_enabled' => Arr::get($device, 'SystemIntegrityProtectionEnabled'),
                'mosyle_device_attestation_status' => Arr::get($device, 'DeviceAttestationStatus'),
                // MDM / lifecycle
                'mosyle_enrollment_type' => Arr::get($device, 'enrollment_type'),
                'mosyle_status' => Arr::get($device, 'status'),
                'mosyle_management_status' => Arr::get($device, 'ManagementStatus'),
                'mosyle_os_update_status' => Arr::get($device, 'OSUpdateStatus'),
                'mosyle_date_last_beat' => Arr::get($device, 'date_last_beat'),
                'mosyle_date_last_push' => Arr::get($device, 'date_last_push'),
                'mosyle_user_id' => Arr::get($device, 'userid'),
                'mosyle_supervised' => Arr::get($device, 'is_supervised'),
                // User / scoping
                'mosyle_user_type' => Arr::get($device, 'usertype'),
                'mosyle_location' => Arr::get($device, 'location'),
                'mosyle_tags' => Arr::get($device, 'tags'),
                // Network
                'mosyle_last_ssid' => Arr::get($device, 'last_ssid'),
                // Lost-mode
                'mosyle_lost_mode_status' => Arr::get($device, 'lostmode_status'),
                'mosyle_latitude' => Arr::get($device, 'latitude'),
                'mosyle_longitude' => Arr::get($device, 'longitude'),
                'mosyle_altitude' => Arr::get($device, 'altitude'),
            ],
        );
    }

    /**
     * Mosyle Business returns timestamps as Unix epoch seconds (same
     * as Manager v2). Numeric values in the plausible Unix-time range
     * get routed through createFromTimestamp. ISO strings fall back
     * to Carbon::parse. Any failure logs and returns null rather than
     * aborting a sync run on one bad device.
     */
    private function parseTimestamp(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value) && $value >= 1_000_000_000 && $value <= 100_000_000_000) {
                return Carbon::createFromTimestampUTC((int) $value);
            }

            return Carbon::parse($value);
        } catch (\Throwable $e) {
            Log::channel('sync-adapters')->warning(
                $this->name().': skipped unparseable Mosyle Business timestamp ('.var_export($value, true).'): '.$e->getMessage()
            );

            return null;
        }
    }

    /**
     * Business writes use the same credential set as reads. No tier
     * / license gate, so canPush is always true for a configured
     * instance.
     */
    public function canPush(): bool
    {
        return true;
    }

    /**
     * Business DOES support a notes push field (Manager does not).
     * The dispatcher endpoint accepts notes as a top-level string
     * on operation=update_device.
     */
    public function notesFieldTarget(): ?string
    {
        return 'notes';
    }

    /**
     * Push Snipe-IT asset_tag (and optionally composed notes) back
     * to Mosyle Business. Both land in a single update_device call
     * because the dispatcher accepts multiple writable fields in one
     * payload.
     *
     * @param  array<int, string>  $changedFields
     */
    public function push(Asset $asset, array $changedFields = []): bool
    {
        $externalSource = $this->pushPrologue($asset, $changedFields);
        if ($externalSource === null) {
            return false;
        }

        $serial = $asset->serial;
        if ($serial === null || $serial === '') {
            $serial = $externalSource->external_id;
        }

        $fields = [];
        $pushedLabels = [];

        if (in_array('asset_tag', $this->pushDirectedFields(), true)) {
            $value = $this->assetValueForSourceField($asset, 'asset_tag');
            if ($value !== null && $value !== '') {
                $fields['asset_tag'] = (string) $value;
                $pushedLabels[] = 'asset_tag';
            }
        }

        $composedNotes = $this->composeNotesForPush($asset);
        if ($composedNotes !== null) {
            $fields[$composedNotes['target']] = $composedNotes['value'];
            $pushedLabels[] = 'notes';
        }

        if ($fields === []) {
            return false;
        }

        if ($this->isPushDryRun()) {
            Log::channel('sync-adapters')->info(
                $this->name().' push [dry-run]: would update Mosyle Business device serial='.$serial.' fields ['.implode(', ', $pushedLabels).']'
            );

            return true;
        }

        $this->makeClient()->updateDeviceBySerial($serial, $fields);

        Log::channel('sync-adapters')->info(
            $this->name().' push: updated Mosyle Business device serial='.$serial.' fields ['.implode(', ', $pushedLabels).']'
        );

        return true;
    }

    private function makeClient(): MosyleBusinessClient
    {
        return new MosyleBusinessClient(
            baseUrl: $this->url(),
            accessToken: $this->credential('access_token'),
            email: $this->credential('email'),
            password: $this->credential('password'),
        );
    }
}
