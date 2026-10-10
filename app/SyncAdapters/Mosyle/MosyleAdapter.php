<?php

namespace App\SyncAdapters\Mosyle;

use App\Models\Asset;
use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\PushableAdapter;
use App\SyncAdapters\SyncAdapter;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Mosyle adapter. Pulls device inventory from a Mosyle Manager v2
 * tenant via the Mosyle API and normalizes it into HostInventoryRecord
 * objects. Also pushes Snipe-IT-authoritative asset_tag back via
 * Mosyle's /devices elements endpoint.
 *
 * Business v1 (businessapi.mosyle.com/v1) uses a different endpoint
 * shape than Manager v2 and is not covered by this adapter today. The
 * base URL field accepts a Business URL but the device endpoints
 * expect Manager-shaped payloads, so Business tenants will see the
 * login succeed and device calls fail. See issue #19790.
 */
class MosyleAdapter extends SyncAdapter implements PushableAdapter
{
    public static function typeLabel(): string
    {
        // Mosyle Manager and Mosyle Business are different products
        // with different API shapes. This adapter targets Manager v2
        // only. Labeling it as "Mosyle Manager" in the adapter picker
        // keeps Business customers from configuring this expecting it
        // to work against businessapi.mosyle.com/v1.
        return 'Mosyle Manager';
    }

    public static function typeSlug(): string
    {
        return 'mosyle';
    }

    public static function docsUrl(): ?string
    {
        return 'https://school.mosyle.com/solutions/macos/privilege-management';
    }

    public function baseUrlPlaceholder(): ?string
    {
        return 'https://managerapi.mosyle.com/v2';
    }

    public function settingsSchema(): array
    {
        return [
            [
                'key' => 'access_token',
                'label' => trans('admin/settings/sync_adapters.label_access_token'),
                'secret' => true,
                'help' => trans('admin/settings/sync_adapters.mosyle_access_token_help'),
            ],
            [
                'key' => 'email',
                'label' => trans('general.email'),
                'help' => trans('admin/settings/sync_adapters.mosyle_email_help'),
            ],
            [
                'key' => 'password',
                'label' => trans('general.password'),
                'secret' => true,
                'help' => trans('admin/settings/sync_adapters.mosyle_password_help'),
            ],
        ];
    }

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
            // Cellular (basic). Dual-SIM imeiOne/Two, carrier labels,
            // phone numbers etc. are intentionally left off per #19790
            // triage. Add on request if a carrier-managed-iPhone-fleet
            // customer surfaces.
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
            // Lost-mode fields. Only populated on devices currently in
            // lost mode. Expect these to stay null on healthy devices.
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
     * Convert a Mosyle device payload into the normalized record shape.
     * Mosyle's `deviceudid` is stable per enrolled device and is what
     * we key asset_external_sources on.
     *
     * Field names here match the Mosyle Manager v2 /listdevices response
     * shape (issue #19790): `username` not `usename`, `last_lan_ip` for
     * the device IP (`ip_address` is not a documented field).
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
            hardwareModel: Arr::get($device, 'device_model_name'),
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
                // Lost-mode (only populated when the device is in lost mode)
                'mosyle_lost_mode_status' => Arr::get($device, 'lostmode_status'),
                'mosyle_latitude' => Arr::get($device, 'latitude'),
                'mosyle_longitude' => Arr::get($device, 'longitude'),
                'mosyle_altitude' => Arr::get($device, 'altitude'),
            ],
        );
    }

    /**
     * Mosyle Manager v2 mixes timestamp formats across fields and
     * across tenants. Observed shapes:
     *   - ISO 8601 string: "2026-01-15T10:00:00.000Z"
     *   - Unix epoch seconds as integer or string: 1791483579
     *   - null or empty string on devices that have never beaten in
     *
     * Numeric values in the plausible Unix-time range (1e9 to 1e11,
     * roughly 2001 to 5138) are treated as epoch seconds. Anything
     * else goes through Carbon::parse(). Unparseable values log and
     * return null rather than throwing, so one device with a bad
     * date_info doesn't abort an entire sync run.
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
                $this->name().': skipped unparseable Mosyle timestamp ('.var_export($value, true).'): '.$e->getMessage()
            );

            return null;
        }
    }

    /**
     * Mosyle writes use the same credential set as reads (access token
     * + login) and the same JWT token flow. No tier / license gate, so
     * canPush is always true for a configured instance.
     */
    public function canPush(): bool
    {
        return true;
    }

    /**
     * Mosyle Manager v2's push payload does not include a notes field.
     * The documented writable fields on /devices elements are asset_tag,
     * device_name, lock-screen message, and custom tags. Returning null
     * here keeps the composed-notes push framework from calling us at
     * push time. The settings-page UI section is gated separately via
     * supportsComposedNotesPush() below.
     */
    public function notesFieldTarget(): ?string
    {
        return null;
    }

    /**
     * Mosyle Manager v2's push payload has no field that admin-composed
     * notes could land in, so hide the composed-notes section on the
     * adapter settings page entirely. Prevents admins from filling in a
     * template that would get silently dropped at push time.
     */
    public function supportsComposedNotesPush(): bool
    {
        return false;
    }

    /**
     * Push Snipe-IT asset_tag back to Mosyle. Mosyle's write API is
     * POST /devices with an `elements` array keyed by `serialnumber`.
     * Mosyle also accepts UDID, but serial is what we cached from pull
     * and is more stable across re-enrollment.
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
            // Mosyle keys writes by serial, not the UDID we stored as
            // external_id. Fall back to external_id when the asset has
            // no serial.
            $serial = $externalSource->external_id;
        }

        if (! in_array('asset_tag', $this->pushDirectedFields(), true)) {
            return false;
        }

        $value = $this->assetValueForSourceField($asset, 'asset_tag');
        if ($value === null || $value === '') {
            return false;
        }

        if ($this->isPushDryRun()) {
            Log::channel('sync-adapters')->info(sprintf(
                '%s push [dry-run]: would set Mosyle device serial=%s asset_tag=%s',
                $this->name(),
                $serial,
                $value,
            ));

            return true;
        }

        $this->makeClient()->updateDeviceAssetTagBySerial($serial, (string) $value);

        Log::channel('sync-adapters')->info(sprintf(
            '%s push: updated Mosyle device serial=%s fields [asset_tag]',
            $this->name(),
            $serial,
        ));

        return true;
    }

    private function makeClient(): MosyleClient
    {
        return new MosyleClient(
            baseUrl: $this->url(),
            accessToken: $this->credential('access_token'),
            email: $this->credential('email'),
            password: $this->credential('password'),
        );
    }
}
