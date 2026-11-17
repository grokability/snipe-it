<?php

namespace App\SyncAdapters\Unifi;

use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\SyncAdapter;
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
class UnifiAdapter extends SyncAdapter
{
    public static function typeLabel(): string
    {
        return 'UniFi';
    }

    public static function typeSlug(): string
    {
        return 'unifi';
    }

    public static function docsUrl(): ?string
    {
        return 'https://developer.ui.com/network/v10.4.57/gettingstarted';
    }

    public function baseUrlPlaceholder(): ?string
    {
        // A local UniFi console / Network Application
        // reachable from this Snipe-IT install, or `https://api.ui.com`
        // for the Site Manager cloud API (the latter is what hosted
        // Snipe-IT installs want when the UniFi consoles are behind NAT).
        return 'https://your-controller:8443 or https://api.ui.com';
    }

    public function settingsSchema(): array
    {
        // Site ID renders as a select backed by cachedSites(). Admins
        // hit "Refresh Sites" (gated on api_key + base URL being set)
        // to populate it from the vendor. Before that, the only option
        // is whatever value is already stored, so a mid-upgrade admin
        // who had "default" saved under the old help text still sees
        // their stale value and can swap once they refresh.
        $siteOptions = [];
        foreach ($this->cachedSites() as $site) {
            $siteOptions[$site['id']] = $site['label'];
        }

        // Read site_id directly through SyncAdapterConfig rather than
        // credential() to avoid recursing: credential() calls
        // settingsSchema() to look up the field, and we're inside it.
        $storedSite = \App\Models\SyncAdapterConfig::get($this->instance->id, 'site_id') ?? '';
        if ($storedSite !== '' && ! array_key_exists($storedSite, $siteOptions)) {
            $siteOptions[$storedSite] = $storedSite;
        }

        return [
            [
                'key' => 'api_key',
                'label' => trans('admin/settings/sync_adapters.label_api_key'),
                'secret' => true,
                'help' => trans('admin/settings/sync_adapters.unifi_api_key_help'),
            ],
            [
                'key' => 'site_id',
                'label' => trans('admin/settings/sync_adapters.label_site_id'),
                'type' => 'select',
                'options' => $siteOptions,
                'help' => trans('admin/settings/sync_adapters.unifi_site_id_help'),
                // Inline refresh button next to the select. Sends the
                // in-form URL + api_key to the endpoint so the admin
                // can populate the list before saving anything.
                'refresh_route_name' => 'settings.adapters.refresh_sites',
                'refresh_response_key' => 'sites',
                'depends_on' => ['url' => 'url', 'api_key' => 'api_key'],
            ],
        ];
    }

    public function supportsSiteScoping(): bool
    {
        return true;
    }

    public function vendorSiteLabel(): string
    {
        return 'Site';
    }

    public function fetchSites(array $overrides = []): array
    {
        // Prefer values the admin just typed into the form over the
        // stored copy. Lets a first-time setup list sites before the
        // adapter is saved for real.
        $url = $overrides['url'] ?? null;
        if ($url === null || $url === '') {
            $url = $this->url();
        }
        $apiKey = $overrides['api_key'] ?? null;
        if ($apiKey === null || $apiKey === '') {
            $apiKey = $this->credential('api_key');
        }

        $client = new UnifiClient(
            baseUrl: $url,
            apiKey: $apiKey,
        );

        $sites = [];
        foreach ($client->sites() as $site) {
            $id = $site['id'] ?? null;
            if ($id === null || $id === '') {
                continue;
            }
            $name = $site['name'] ?? $site['displayName'] ?? $id;
            $sites[] = ['id' => (string) $id, 'label' => (string) $name];
        }

        return $sites;
    }

    public function extraFields(): array
    {
        return [
            'unifi_model_key' => ['label_key' => 'admin/settings/sync_adapters.extra_model_code'],
            'unifi_state' => ['label_key' => 'admin/settings/sync_adapters.extra_device_state'],
            'unifi_uplink_mac' => ['label_key' => 'admin/settings/sync_adapters.extra_uplink_mac'],
            'unifi_adopted' => ['label_key' => 'admin/settings/sync_adapters.extra_adopted', 'type' => 'boolean'],
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
     * SyncAdapter treats null serials as acceptable so the
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
