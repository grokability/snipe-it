<?php

namespace App\SyncAdapters\Action1;

use App\Models\SyncAdapterConfig;
use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\SyncAdapter;
use Carbon\Carbon;
use Illuminate\Support\Arr;

/**
 * Action1 adapter. Pulls managed endpoints from an Action1 organization
 * through the public REST API (/api/3.0) and normalizes them into
 * HostInventoryRecord objects.
 *
 * Action1 hosts customers in five regions on dedicated hostnames. The
 * admin picks a region from a dropdown and the adapter derives the full
 * base URL. There is no free-text URL field, so a typo cannot route
 * the sync at a loopback host, a cloud-metadata endpoint, or a
 * mistyped subdomain. See regionBaseUrls() for the authoritative list.
 *
 * Auth: OAuth2 client-credentials (client_id + client_secret). Every
 * API call is scoped to a single Action1 organization, identified by
 * its organization_id GUID (visible in the Action1 console URL after
 * `/console/`).
 *
 * Push is not implemented. Action1's PATCH surface exposes `name`
 * (hostname) and `comment` (free-form notes), and `name` would overwrite
 * the discovery hostname with whatever Snipe-IT stored. Snipe's push
 * flow targets fields that admins actively curate in Snipe-IT, which
 * does not fit the discovery-first Action1 shape yet. Revisit if there
 * is demand for pushing composed notes to `comment`.
 */
class Action1Adapter extends SyncAdapter
{
    public static function typeLabel(): string
    {
        return 'Action1';
    }

    public static function typeSlug(): string
    {
        return 'action1';
    }

    public static function docsUrl(): ?string
    {
        return 'https://www.action1.com/api-documentation/how-action1-api-works/';
    }

    /**
     * Action1's five regional REST API hostnames, keyed on the region
     * label admins see in the Action1 console. Canonical list comes
     * from Action1Corp/PSAction1's Action1.Hosts.ps1.
     *
     * @return array<string, string>
     */
    public static function regionBaseUrls(): array
    {
        return [
            'north_america' => 'https://app.action1.com/api/3.0',
            'north_america_2' => 'https://app.na-2.action1.com/api/3.0',
            'europe' => 'https://app.eu.action1.com/api/3.0',
            'australia' => 'https://app.au.action1.com/api/3.0',
            'united_kingdom' => 'https://app.uk.action1.com/api/3.0',
        ];
    }

    /**
     * Admins pick a region slug from the credential form, not a URL.
     * Translated labels live in sync_adapters.php so localizers get a
     * single string per region rather than hard-coded English in the
     * schema.
     *
     * @return array<string, string>
     */
    public function regionOptions(): array
    {
        return [
            'north_america' => trans('admin/settings/sync_adapters.action1_region_north_america'),
            'north_america_2' => trans('admin/settings/sync_adapters.action1_region_north_america_2'),
            'europe' => trans('admin/settings/sync_adapters.action1_region_europe'),
            'australia' => trans('admin/settings/sync_adapters.action1_region_australia'),
            'united_kingdom' => trans('admin/settings/sync_adapters.action1_region_united_kingdom'),
        ];
    }

    /**
     * No free-text Base URL field. The region dropdown is the only way
     * to pick where to hit, and the five entries cover every live
     * Action1 region. Keeps the attack surface small and admins never
     * have to look the URL up.
     */
    public function usesConfigurableUrl(): bool
    {
        return false;
    }

    public function settingsSchema(): array
    {
        return [
            [
                'key' => 'region',
                'label' => trans('admin/settings/sync_adapters.action1_label_region'),
                'type' => 'select',
                'options' => $this->regionOptions(),
                'default' => 'north_america',
                'help' => trans('admin/settings/sync_adapters.action1_region_help'),
            ],
            [
                'key' => 'org_id',
                'label' => trans('admin/settings/sync_adapters.action1_label_org_id'),
                'help' => trans('admin/settings/sync_adapters.action1_org_id_help'),
            ],
            [
                'key' => 'client_id',
                'label' => trans('admin/settings/sync_adapters.label_client_id'),
                'help' => trans('admin/settings/sync_adapters.action1_client_id_help'),
            ],
            [
                'key' => 'client_secret',
                'label' => trans('admin/settings/sync_adapters.label_client_secret'),
                'secret' => true,
                'help' => trans('admin/settings/sync_adapters.action1_client_secret_help'),
            ],
        ];
    }

    /**
     * Extras this adapter extracts from well-known Action1 endpoint
     * fields. Admins route each to a Snipe-IT destination (native or
     * custom field) through the mapping table.
     */
    public function presetExtraFields(): array
    {
        return [
            'action1_status' => ['label_key' => 'admin/settings/sync_adapters.action1_extra_status'],
            'action1_reboot_required' => ['label_key' => 'admin/settings/sync_adapters.action1_extra_reboot_required'],
            'action1_comment' => ['label_key' => 'admin/settings/sync_adapters.action1_extra_comment'],
        ];
    }

    public function supportsAdminDefinedExtras(): bool
    {
        return true;
    }

    public function pull(): iterable
    {
        $client = new Action1Client(
            baseUrl: $this->resolvedBaseUrl(),
            clientId: $this->credential('client_id'),
            clientSecret: $this->credential('client_secret'),
        );

        $orgId = (string) $this->credential('org_id');
        foreach ($client->managedEndpoints($orgId) as $endpoint) {
            yield $this->normalize($endpoint);
        }
    }

    /**
     * Resolve the stored region slug to the full base URL. Unknown
     * region values fall back to the North America default so a stale
     * row from before a region was renamed still syncs rather than
     * crashing the pull.
     */
    private function resolvedBaseUrl(): string
    {
        $region = SyncAdapterConfig::get($this->instance->id, 'region');
        $urls = self::regionBaseUrls();

        return $urls[$region] ?? $urls['north_america'];
    }

    /**
     * Convert an Action1 managed-endpoint payload into the normalized
     * record shape. Field names verified against Action1Corp/PSAction1's
     * Get-Action1Endpoints example output: id, name, serial,
     * manufacturer, OS (uppercase), user, last_seen, status,
     * reboot_required. `comment` is the admin-editable notes field
     * exposed by Action1's PATCH endpoint.
     *
     * @param  array<string, mixed>  $endpoint
     */
    private function normalize(array $endpoint): HostInventoryRecord
    {
        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: (string) Arr::get($endpoint, 'id'),
            hostname: Arr::get($endpoint, 'name'),
            hardwareSerial: Arr::get($endpoint, 'serial'),
            hardwareModel: Arr::get($endpoint, 'model'),
            manufacturer: Arr::get($endpoint, 'manufacturer'),
            primaryMac: null,
            primaryIp: null,
            os: Arr::get($endpoint, 'OS'),
            osVersion: Arr::get($endpoint, 'os_version'),
            lastSeen: $this->parseTimestamp(Arr::get($endpoint, 'last_seen')),
            assignedUserName: Arr::get($endpoint, 'user'),
            extra: [
                'action1_status' => Arr::get($endpoint, 'status'),
                'action1_reboot_required' => Arr::get($endpoint, 'reboot_required'),
                'action1_comment' => Arr::get($endpoint, 'comment'),
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
