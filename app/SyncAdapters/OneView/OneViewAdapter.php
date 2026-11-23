<?php

namespace App\SyncAdapters\OneView;

use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\SyncAdapter;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * HPE OneView adapter. Pulls server-hardware inventory from a OneView
 * appliance and normalizes it into HostInventoryRecord objects. Pull-
 * only (for now). OneView has writable fields (assetTag, custom labels)
 * for when/if we decide to add that.
 *
 * Field conventions worth knowing before reading normalize():
 *
 *   - OneView's `name` is the iLO management DNS name, not the OS
 *     hostname (iLO is HPE's out-of-band management stuff). We don't
 *     use it as the asset's name because admins reasonably expect the
 *     Name column to be the production hostname. Asset name is instead
 *     built from shortModel + serialNumber, and the iLO DNS name goes
 *     into the extras section as a management-DNS field admins can map.
 *
 *   - `mpIpAddress` is the iLO IP, NOT the production NIC IP. We don't want to surprise admins with a
 *     management-plane IP in the "IP address" slot. We leave primaryIp
 *     null and route mpIpAddress through extras instead.
 *
 *   - `processorCoreCount`, `processorSpeedMhz`, and friends come
 *     back as 0 / null on powered-off servers that OneView hasn't
 *     re-profiled since boot. normalize() skips those fields when
 *     they're zero / null rather than overwriting a previously known-
 *     good value with 0.
 *
 * HPE OneView API reference: https://techhub.hpe.com/eginfolib/servers/docs/oneview/api/
 */
class OneViewAdapter extends SyncAdapter
{
    public static function typeLabel(): string
    {
        return 'HPE OneView';
    }

    public static function typeSlug(): string
    {
        return 'oneview';
    }

    public static function docsUrl(): ?string
    {
        return 'https://techhub.hpe.com/eginfolib/servers/docs/oneview/api/';
    }

    public function baseUrlPlaceholder(): ?string
    {
        return 'https://oneview.example.com';
    }

    public function settingsSchema(): array
    {
        return [
            [
                'key' => 'username',
                'label' => trans('admin/settings/sync_adapters.oneview_label_username'),
                'help' => trans('admin/settings/sync_adapters.oneview_username_help'),
            ],
            [
                'key' => 'password',
                'label' => trans('admin/settings/sync_adapters.oneview_label_password'),
                'secret' => true,
                'help' => trans('admin/settings/sync_adapters.oneview_password_help'),
            ],
        ];
    }

    public function presetExtraFields(): array
    {
        return [
            'oneview_management_ip' => ['label_key' => 'admin/settings/sync_adapters.oneview_extra_management_ip'],
            'oneview_management_dns' => ['label_key' => 'admin/settings/sync_adapters.oneview_extra_management_dns'],
            'oneview_ilo_model' => ['label_key' => 'admin/settings/sync_adapters.oneview_extra_ilo_model'],
            'oneview_ilo_firmware' => ['label_key' => 'admin/settings/sync_adapters.oneview_extra_ilo_firmware'],
            'oneview_rom_version' => ['label_key' => 'admin/settings/sync_adapters.oneview_extra_rom_version'],
            'oneview_power_state' => ['label_key' => 'admin/settings/sync_adapters.oneview_extra_power_state'],
            'oneview_status' => ['label_key' => 'admin/settings/sync_adapters.oneview_extra_status'],
            'oneview_state' => ['label_key' => 'admin/settings/sync_adapters.oneview_extra_state'],
            'oneview_cpu_type' => ['label_key' => 'admin/settings/sync_adapters.oneview_extra_cpu_type'],
            'oneview_cpu_count' => ['label_key' => 'admin/settings/sync_adapters.oneview_extra_cpu_count'],
            'oneview_cpu_cores' => ['label_key' => 'admin/settings/sync_adapters.oneview_extra_cpu_cores'],
            'oneview_cpu_speed_mhz' => ['label_key' => 'admin/settings/sync_adapters.oneview_extra_cpu_speed_mhz'],
            'oneview_memory_mb' => ['label_key' => 'admin/settings/sync_adapters.oneview_extra_memory_mb'],
            'oneview_form_factor' => ['label_key' => 'admin/settings/sync_adapters.oneview_extra_form_factor'],
            'oneview_uuid' => ['label_key' => 'admin/settings/sync_adapters.oneview_extra_uuid'],
        ];
    }

    public function pull(): iterable
    {
        $client = $this->buildClient();

        foreach ($client->serverHardware() as $member) {
            yield $this->normalize($member);
        }
    }

    private function buildClient(): OneViewClient
    {
        return new OneViewClient(
            baseUrl: $this->url(),
            username: $this->credential('username'),
            password: $this->credential('password'),
        );
    }

    /**
     * Convert one OneView server-hardware member into the normalized
     * record shape. See the class docblock for the convention calls
     * on name / primaryIp / the powered-off cores guard.
     *
     * @param  array<string, mixed>  $member
     */
    private function normalize(array $member): HostInventoryRecord
    {
        $uuid = (string) Arr::get($member, 'uuid');
        $serial = self::nullIfEmpty(Arr::get($member, 'serialNumber'));
        $shortModel = self::nullIfEmpty(Arr::get($member, 'shortModel'));
        $fullModel = self::nullIfEmpty(Arr::get($member, 'model'));

        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: $uuid,
            hostname: self::buildAssetName($shortModel, $serial, $uuid),
            hardwareSerial: $serial,
            hardwareModel: $fullModel,
            // OneView's server-hardware endpoint returns HPE-manufactured
            // servers exclusively (it's HPE's own management for
            // ProLiant / Synergy / Apollo hardware), so we can hardcode
            // the manufacturer without a vendor field.
            manufacturer: 'HPE',
            primaryMac: self::firstEthernetMac(Arr::get($member, 'portMap.deviceSlots')),
            // Deliberately null. mpIpAddress is iLO (management stuff)
            // not the production NIC. The management
            // IP is routed through extras below so admins who want to
            // surface it can map it to a custom field without us
            // forcing that interpretation on everyone.
            primaryIp: null,
            lastSeen: self::parseTimestamp(Arr::get($member, 'modified')),
            assetTag: self::nullIfEmpty(Arr::get($member, 'assetTag')),
            extra: $this->buildExtras($member),
        );
    }

    /**
     * Prefer "DL320 Gen12 SN0000000001" (shortModel + serial) for the
     * asset's displayed name, since that's what an HPE admin recognizes
     * at a glance from a rack label. Fall back through sensible
     * degradations when either source is missing so this never returns
     * null (the sync pipeline tolerates null hostnames but it's a worse
     * admin experience than seeing the UUID).
     */
    private static function buildAssetName(?string $shortModel, ?string $serial, string $uuid): string
    {
        if ($shortModel !== null && $serial !== null) {
            return $shortModel . ' ' . $serial;
        }
        if ($serial !== null) {
            return $serial;
        }
        if ($shortModel !== null) {
            return $shortModel . ' ' . $uuid;
        }

        return $uuid;
    }

    /**
     * Walk portMap.deviceSlots[*].physicalPorts[*] for the first
     * Ethernet port whose MAC looks real. "Looks real" = non-empty,
     * not the all-zeroes placeholder that OneView writes for
     * unconfigured slots. Returns null when no usable MAC exists.
     *
     * OneView frequently reports the same MAC on both port 1 and
     * port 2 of a dual-port card when neither cable is wired, since
     * the appliance assigns slot-level MACs ahead of actual link
     * negotiation. First usable one wins.
     *
     * @param  mixed  $deviceSlots
     */
    private static function firstEthernetMac($deviceSlots): ?string
    {
        if (!is_array($deviceSlots)) {
            return null;
        }

        foreach ($deviceSlots as $slot) {
            $ports = Arr::get($slot, 'physicalPorts');
            if (!is_array($ports)) {
                continue;
            }
            foreach ($ports as $port) {
                if (Arr::get($port, 'type') !== 'Ethernet') {
                    continue;
                }
                $mac = Arr::get($port, 'mac');
                if (!is_string($mac) || $mac === '' || $mac === '00:00:00:00:00:00') {
                    continue;
                }

                return $mac;
            }
        }

        return null;
    }

    /**
     * Build the vendor-specific extras bag. Numeric inventory fields
     * (cores, speed, memory) get the zero-guard so powered-off servers
     * that OneView hasn't re-profiled since boot don't overwrite
     * previously known-good values with 0.
     *
     * @param  array<string, mixed>  $member
     * @return array<string, mixed>
     */
    private function buildExtras(array $member): array
    {
        $extras = [
            'oneview_management_ip' => self::nullIfEmpty(Arr::get($member, 'mpIpAddress')),
            'oneview_management_dns' => self::nullIfEmpty(Arr::get($member, 'mpDnsName')),
            'oneview_ilo_model' => self::nullIfEmpty(Arr::get($member, 'mpModel')),
            'oneview_ilo_firmware' => self::nullIfEmpty(Arr::get($member, 'mpFirmwareVersion')),
            'oneview_rom_version' => self::nullIfEmpty(Arr::get($member, 'romVersion')),
            'oneview_power_state' => self::nullIfEmpty(Arr::get($member, 'powerState')),
            'oneview_status' => self::nullIfEmpty(Arr::get($member, 'status')),
            'oneview_state' => self::nullIfEmpty(Arr::get($member, 'state')),
            'oneview_cpu_type' => self::nullIfEmpty(Arr::get($member, 'processorType')),
            'oneview_form_factor' => self::nullIfEmpty(Arr::get($member, 'formFactor')),
            'oneview_uuid' => self::nullIfEmpty(Arr::get($member, 'uuid')),
        ];

        $extras['oneview_cpu_count'] = self::positiveIntOrNull(Arr::get($member, 'processorCount'));
        $extras['oneview_cpu_cores'] = self::positiveIntOrNull(Arr::get($member, 'processorCoreCount'));
        $extras['oneview_cpu_speed_mhz'] = self::positiveIntOrNull(Arr::get($member, 'processorSpeedMhz'));
        $extras['oneview_memory_mb'] = self::positiveIntOrNull(Arr::get($member, 'memoryMb'));

        return $extras;
    }

    private static function nullIfEmpty(mixed $value): ?string
    {
        if (!is_string($value) && !is_numeric($value)) {
            return null;
        }
        $str = (string) $value;

        return $str === '' ? null : $str;
    }

    /**
     * Zero-guard for the inventory fields OneView reports as 0 or null
     * on powered-off / unprofiled servers. Returns null for anything
     * that isn't a positive integer so the sync pipeline skips the
     * write entirely instead of overwriting a previously-known-good
     * value with 0. See class docblock.
     */
    private static function positiveIntOrNull(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_numeric($value)) {
            return null;
        }
        $int = (int) $value;

        return $int > 0 ? $int : null;
    }

    private static function parseTimestamp(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            Log::channel('sync-adapters')->warning(
                'oneview: skipped unparseable modified timestamp (' . var_export($value, true) . '): ' . $e->getMessage()
            );

            return null;
        }
    }
}
