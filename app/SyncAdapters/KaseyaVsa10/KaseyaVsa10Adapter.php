<?php

namespace App\SyncAdapters\KaseyaVsa10;

use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\Support\ConfigurableAdapter;
use Carbon\Carbon;
use Illuminate\Support\Arr;

/**
 * Kaseya VSA 10 (formerly Kaseya X, product code "vsax") adapter.
 * Pulls managed endpoints from the /api/v3/assets endpoint using HTTP
 * Basic auth with a token-id / token-secret pair issued from the VSA
 * admin console. Keyed on VSA's stable `Identifier` GUID.
 *
 * Hardware inventory in VSA 10 lives in a nested AssetInfo array of
 * category blocks (System, BIOS, Operating System, ...). Each block
 * has a CategoryName plus a CategoryData map whose keys are
 * human-readable strings ("Serial Number" with a space, "Number of
 * Cores", etc). normalize() walks the array by category and extracts
 * the fields Snipe-IT cares about.
 *
 * Base URL is customer-specific: each VSA tenant has its own server
 * hostname (no shared regional endpoints like Workspace ONE or
 * NinjaOne). Admins paste the full https URL on the settings page.
 *
 * Group scoping uses VSA's Organization as the natural tenant
 * boundary. Sites and Groups are internal RMM groupings that can be
 * mapped to custom fields via extraFields() if admins want them.
 *
 * Push: not implemented in the initial pass. VSA exposes a writable
 * Description field on /devices and a /customfields/assign endpoint;
 * both are candidates for a later push implementation once we have
 * a real tenant to validate the update-endpoint shape.
 */
class KaseyaVsa10Adapter extends ConfigurableAdapter
{
    public static function typeLabel(): string
    {
        return 'Kaseya VSA 10';
    }

    public function baseUrlPlaceholder(): ?string
    {
        return 'https://your-tenant.vsax.net';
    }

    public function credentialSchema(): array
    {
        return [
            [
                'key' => 'token_id',
                'label' => 'Token ID',
                'secret' => true,
                'help' => trans('admin/settings/sync_adapters.kaseya_vsa10_token_id_help'),
            ],
            [
                'key' => 'token_secret',
                'label' => 'Token Secret',
                'secret' => true,
                'help' => trans('admin/settings/sync_adapters.kaseya_vsa10_token_secret_help'),
            ],
        ];
    }

    public function extraFields(): array
    {
        $extras = [
            'kaseya_vsa10_group' => ['label_key' => 'admin/settings/sync_adapters.extra_group'],
            'kaseya_vsa10_computer_type' => ['label_key' => 'admin/settings/sync_adapters.extra_computer_type'],
            'kaseya_vsa10_client_version' => ['label_key' => 'admin/settings/sync_adapters.extra_agent_version'],
            'kaseya_vsa10_domain' => ['label_key' => 'admin/settings/sync_adapters.extra_domain'],
            'kaseya_vsa10_cpu' => ['label_key' => 'admin/settings/sync_adapters.extra_cpu'],
            'kaseya_vsa10_tags' => ['label_key' => 'admin/settings/sync_adapters.extra_tags'],
        ];

        // Merge in any tenant-defined VSA custom fields the admin
        // captured via the "Refresh custom fields" button. Type is
        // translated to Snipe-IT's extras type vocabulary so booleans
        // land on checkbox custom fields and everything else stays
        // free-text. admin_defined restricts the target pool to
        // custom-only in the mapping UI (native:asset_tag / notes
        // don't make sense for admin-labeled tenant data).
        foreach ($this->cachedVendorCustomFields() as $field) {
            $key = self::vendorCustomFieldKey($field['name']);
            if ($key === '') {
                continue;
            }
            $extras[$key] = [
                'label' => 'Kaseya: '.$field['name'],
                'type' => self::translateVendorType($field['type']),
                'admin_defined' => true,
            ];
        }

        return $extras;
    }

    /**
     * Convert a VSA custom field Name to the extra-key we store in
     * the mapping table. Prefix + slug of the vendor name keeps the
     * key namespaced away from built-in extras and stable across
     * refreshes (renaming a field in VSA breaks the mapping but that
     * is expected).
     */
    private static function vendorCustomFieldKey(string $name): string
    {
        $slug = strtolower(preg_replace('/[^A-Za-z0-9]+/', '_', trim($name)));
        $slug = trim($slug, '_');

        return $slug === '' ? '' : 'kaseya_vsa10_cf_'.$slug;
    }

    /**
     * Translate a VSA field Type ("Text", "Number", "Date",
     * "Boolean", "Dropdown") to the Snipe-IT extras type vocabulary
     * ('text', 'boolean'). Unknown types fall through to 'text' so
     * the value still lands in a free-text custom field.
     */
    private static function translateVendorType(string $vendorType): string
    {
        return match ($vendorType) {
            'Boolean', 'Checkbox' => 'boolean',
            default => 'text',
        };
    }

    public function supportsVendorCustomFields(): bool
    {
        return true;
    }

    /**
     * Refresh the cached list of device-applicable custom fields.
     * Returns definitions as normalized {id, name, type} entries so
     * the controller can persist them without leaking VSA's
     * PascalCase key names into cache storage.
     *
     * @return array<int, array{id: string, name: string, type: string}>
     */
    public function fetchVendorCustomFields(): array
    {
        $client = new KaseyaVsa10Client(
            baseUrl: $this->url(),
            tokenId: $this->credential('token_id'),
            tokenSecret: $this->credential('token_secret'),
        );

        $out = [];
        foreach ($client->deviceCustomFieldDefinitions() as $field) {
            $name = (string) ($field['Name'] ?? '');
            if ($name === '') {
                continue;
            }
            $out[] = [
                'id' => (string) ($field['Id'] ?? ''),
                'name' => $name,
                'type' => (string) ($field['Type'] ?? 'Text'),
            ];
        }

        return $out;
    }

    public function supportsGroupScoping(): bool
    {
        return true;
    }

    public function vendorGroupLabel(): string
    {
        return 'Kaseya Organization';
    }

    public function fetchGroups(): array
    {
        // Organizations live at /api/v3/organizations. Not fetched
        // here yet. The settings UI degrades to a free-text field so
        // admins can still type OrganizationId manually.
        //
        // TODO(vsa10-organizations): add fetchOrganizations() on the
        // client and wire it here once we have a tenant to test
        // against.
        return [];
    }

    public function pull(): iterable
    {
        $client = new KaseyaVsa10Client(
            baseUrl: $this->url(),
            tokenId: $this->credential('token_id'),
            tokenSecret: $this->credential('token_secret'),
        );

        // Only enrich records with per-device custom-field values
        // when the admin has both refreshed the custom-field list AND
        // mapped at least one to a Snipe-IT target. Skipping the
        // /devices/{id}/customfields call when nothing is mapped
        // saves ~one API call per asset (VSA rate limit is
        // 3600/hour) and matches admin intent (no mapping = no
        // reason to fetch).
        $enrichWithCustomFields = $this->hasMappedVendorCustomFields();

        foreach ($client->assets() as $row) {
            $record = $this->normalize($row);
            if ($enrichWithCustomFields) {
                $record = $this->enrichRecordWithCustomFields($record, $client);
            }
            yield $record;
        }
    }

    /**
     * Whether at least one cached VSA custom field has a non-skip
     * mapping stored. Determines whether pull() spends an extra API
     * call per asset fetching /devices/{id}/customfields.
     */
    private function hasMappedVendorCustomFields(): bool
    {
        foreach ($this->cachedVendorCustomFields() as $field) {
            $key = self::vendorCustomFieldKey($field['name']);
            if ($key === '') {
                continue;
            }
            $target = $this->mappingFor($key);
            if ($target !== '' && $target !== 'skip') {
                return true;
            }
        }

        return false;
    }

    /**
     * Fetch the per-device custom-field values for one asset and
     * merge them into a fresh HostInventoryRecord. Values come back
     * from VSA as {Id, Name, Value, Type}. We key the merged extras
     * by the same vendorCustomFieldKey we advertise in extraFields()
     * so the mapping table lines up. Network failures on the
     * customfields call are non-fatal: we log and return the
     * un-enriched record so a transient customfields outage doesn't
     * poison the whole sync.
     */
    private function enrichRecordWithCustomFields(HostInventoryRecord $record, KaseyaVsa10Client $client): HostInventoryRecord
    {
        try {
            $values = $client->deviceCustomFieldValues($record->sourceId);
        } catch (\Throwable $e) {
            \Log::channel('sync-adapters')->warning(sprintf(
                '%s customfields fetch failed for device %s: %s',
                $this->name(),
                $record->sourceId,
                $e->getMessage(),
            ));

            return $record;
        }

        $extra = $record->extra;
        foreach ($values as $entry) {
            $key = self::vendorCustomFieldKey($entry['Name'] ?? '');
            if ($key === '') {
                continue;
            }
            $extra[$key] = $entry['Value'] ?? null;
        }

        return new HostInventoryRecord(
            sourceKey: $record->sourceKey,
            sourceId: $record->sourceId,
            hostname: $record->hostname,
            hardwareSerial: $record->hardwareSerial,
            hardwareModel: $record->hardwareModel,
            manufacturer: $record->manufacturer,
            primaryMac: $record->primaryMac,
            primaryIp: $record->primaryIp,
            os: $record->os,
            osVersion: $record->osVersion,
            lastSeen: $record->lastSeen,
            assetTag: $record->assetTag,
            assignedUserEmail: $record->assignedUserEmail,
            assignedUserName: $record->assignedUserName,
            vendorGroupId: $record->vendorGroupId,
            extra: $extra,
        );
    }

    /**
     * Convert a VSA 10 asset row into the normalized record shape.
     * `Identifier` is a stable GUID per registered endpoint and is
     * what we key asset_external_sources on.
     *
     * Hardware inventory is nested under AssetInfo[] with per-category
     * blocks. Keys inside CategoryData contain spaces ("Serial Number",
     * "Number of Cores"), so we go through Arr::get with the exact
     * verbatim key string.
     *
     * @param  array<string, mixed>  $row
     */
    private function normalize(array $row): HostInventoryRecord
    {
        $system = $this->assetCategory($row, 'System');
        $bios = $this->assetCategory($row, 'BIOS');
        $os = $this->assetCategory($row, 'Operating System');

        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: (string) Arr::get($row, 'Identifier'),
            hostname: Arr::get($row, 'Name'),
            hardwareSerial: Arr::get($bios, 'Serial Number'),
            hardwareModel: Arr::get($system, 'Model'),
            manufacturer: Arr::get($system, 'Manufacturer'),
            primaryMac: Arr::get($row, 'LocalIpAddresses.0.PhysicalAddress'),
            primaryIp: Arr::get($row, 'PublicIpAddress')
                ?? Arr::get($row, 'LocalIpAddresses.0.IpV4'),
            os: Arr::get($os, 'Name') ?? Arr::get($row, 'Type'),
            osVersion: Arr::get($os, 'Version'),
            lastSeen: $this->parseTimestamp(Arr::get($row, 'LastSeenOnline')),
            assignedUserEmail: null,
            assignedUserName: null,
            vendorGroupId: Arr::has($row, 'OrganizationId')
                ? (string) Arr::get($row, 'OrganizationId')
                : null,
            extra: [
                'kaseya_vsa10_group' => Arr::get($row, 'GroupName'),
                'kaseya_vsa10_computer_type' => Arr::get($row, 'Type'),
                'kaseya_vsa10_client_version' => Arr::get($row, 'ClientVersion'),
                'kaseya_vsa10_domain' => Arr::get($system, 'Domain'),
                'kaseya_vsa10_cpu' => Arr::get($system, 'CPU'),
                'kaseya_vsa10_tags' => Arr::get($row, 'Tags'),
            ],
        );
    }

    /**
     * Locate the CategoryData for a named AssetInfo category on an
     * asset row. Returns an empty array when the category isn't
     * present, so downstream Arr::get calls just resolve to null.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function assetCategory(array $row, string $categoryName): array
    {
        foreach ($row['AssetInfo'] ?? [] as $block) {
            if (($block['CategoryName'] ?? null) === $categoryName) {
                return $block['CategoryData'] ?? [];
            }
        }

        return [];
    }

    /**
     * Parse a VSA 10 timestamp. LastSeenOnline is ISO 8601 with a
     * trailing Z. Bail on null / empty so we don't record 1970 for
     * assets that haven't checked in.
     */
    private function parseTimestamp(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value);
    }
}
