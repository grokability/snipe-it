<?php

namespace App\SyncAdapters\Unifi;

use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\Support\ConfigurableAdapter;
use Carbon\Carbon;
use Illuminate\Support\Arr;

/**
 * UniFi adapter. Pulls network-hardware inventory (APs, switches,
 * gateways, cameras) from a UniFi controller and normalizes it into
 * HostInventoryRecord objects.
 *
 * Uses UniFi's modern Network Integration API with X-API-KEY bearer
 * auth (UniFi OS 4.0.6+ / Network Application 9.0.108+ / Site Manager
 * cloud). Older controllers on session-cookie auth are not supported.
 * Admins should upgrade UniFi rather than us carrying the legacy
 * cookie flow.
 *
 * Site scoping: the UniFi API is site-scoped. The Site ID field pins
 * this instance to one site. Multi-site controllers create one
 * sync-adapter instance per site (each with its own tab under
 * Settings -> Sync Adapters).
 */
class UnifiAdapter extends ConfigurableAdapter
{
    public static function typeLabel(): string
    {
        return 'UniFi';
    }

    public function baseUrlPlaceholder(): ?string
    {
        return 'https://your-controller:8443';
    }

    public function credentialSchema(): array
    {
        return [
            [
                'key' => 'api_key',
                'label' => 'API Key',
                'secret' => true,
                'help' => trans('admin/settings/general.sync_adapter_unifi_api_key_help'),
            ],
            [
                'key' => 'site_id',
                'label' => 'Site ID',
                'help' => trans('admin/settings/general.sync_adapter_unifi_site_id_help'),
            ],
        ];
    }

    public function extraFields(): array
    {
        return [
            'unifi_model_key' => 'UniFi Model Code',
            'unifi_state' => 'UniFi Device State',
            'unifi_uplink_mac' => 'UniFi Uplink MAC',
            'unifi_adopted' => ['label' => 'UniFi Adopted', 'type' => 'boolean'],
        ];
    }

    public function pull(): iterable
    {
        $client = new UnifiClient(
            baseUrl: $this->url(),
            apiKey: $this->credential('api_key'),
            siteId: $this->credential('site_id'),
        );

        foreach ($client->devices() as $device) {
            yield $this->normalize($device);
        }
    }

    /**
     * Convert a UniFi device payload into the normalized record shape.
     * UniFi's `id` (UUID) is stable per adopted device and is what we
     * key asset_external_sources on. Serial is populated on most
     * device types but occasionally null on virtual or legacy devices.
     * SyncHostFromAdapter treats null serials as acceptable so the
     * asset still lands even when the controller doesn't report one.
     *
     * @param  array<string, mixed>  $device
     */
    private function normalize(array $device): HostInventoryRecord
    {
        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: (string) Arr::get($device, 'id'),
            hostname: Arr::get($device, 'name'),
            hardwareSerial: Arr::get($device, 'serialNumber'),
            hardwareModel: Arr::get($device, 'model'),
            manufacturer: 'Ubiquiti',
            primaryMac: Arr::get($device, 'macAddress'),
            primaryIp: Arr::get($device, 'ipAddress'),
            os: 'UniFi',
            osVersion: Arr::get($device, 'firmwareVersion'),
            lastSeen: $this->parseTimestamp(Arr::get($device, 'lastSeen')),
            extra: [
                'unifi_model_key' => Arr::get($device, 'modelKey'),
                'unifi_state' => Arr::get($device, 'state'),
                'unifi_uplink_mac' => Arr::get($device, 'uplink.mac'),
                'unifi_adopted' => Arr::get($device, 'adopted'),
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
}
