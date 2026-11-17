<?php

namespace App\SyncAdapters\Jamf;

use App\Models\Asset;
use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\PushableSyncAdapter;
use App\SyncAdapters\Traits\UsesAppleDbEnrichment;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Jamf Pro adapter. Pulls computer inventory via the Jamf Pro API and
 * normalizes it into HostInventoryRecord objects. Authenticates via
 * OAuth 2.0 client credentials against Jamf Pro's /api/oauth/token
 * endpoint (Jamf Pro does not issue long-lived personal tokens, so
 * this is the only workable flow). Also pushes Snipe-IT-authoritative
 * fields (asset_tag today) back to Jamf via the
 * /api/v1/computers-inventory-detail/{id} PATCH endpoint using the
 * same exchanged bearer.
 */
class JamfAdapter extends PushableSyncAdapter
{
    use UsesAppleDbEnrichment;

    public static function typeLabel(): string
    {
        return 'Jamf Pro';
    }

    public static function typeSlug(): string
    {
        return 'jamf';
    }

    public static function docsUrl(): ?string
    {
        return 'https://developer.jamf.com/jamf-pro/reference';
    }

    public function baseUrlPlaceholder(): ?string
    {
        return 'https://your-subdomain.jamfcloud.com';
    }

    public function settingsSchema(): array
    {
        return [
            [
                'key' => 'client_id',
                'label' => trans('admin/settings/sync_adapters.label_client_id'),
                'help' => trans('admin/settings/sync_adapters.jamf_client_id_help'),
            ],
            [
                'key' => 'client_secret',
                'label' => trans('admin/settings/sync_adapters.label_client_secret'),
                'secret' => true,
                'help' => trans('admin/settings/sync_adapters.jamf_client_secret_help'),
            ],
            [
                'key' => 'include_mobile_devices',
                'label' => trans('admin/settings/sync_adapters.jamf_label_include_mobile_devices'),
                'type' => 'checkbox',
                'required' => false,
                'help' => trans('admin/settings/sync_adapters.jamf_include_mobile_devices_help'),
            ],
            [
                'key' => 'group_filter',
                'label' => trans('admin/settings/sync_adapters.jamf_group_filter_label'),
                'required' => false,
                'help' => trans('admin/settings/sync_adapters.jamf_group_filter_help'),
            ],
            $this->appleDbSettingsField(),
        ];
    }

    public function extraFields(): array
    {
        return [
            ...$this->appleDbExtraFieldsDeclaration(),
            'jamf_udid' => ['label_key' => 'admin/settings/sync_adapters.extra_udid'],
            'jamf_last_enrolled' => ['label_key' => 'admin/settings/sync_adapters.extra_last_enrolled', 'type' => 'date'],
            'jamf_model_identifier' => ['label_key' => 'admin/settings/sync_adapters.extra_model_identifier'],
            // Computer-only hardware fields. Values normalize to GB so
            // they line up with the other adapters (Addigy, Levelio,
            // Mosyle, AxM device_capacity) which all speak GB natively.
            // Conversion from Jamf's native MB divides by 1000 (decimal
            // GB, same convention Apple uses in marketed capacity).
            'jamf_ram_gb' => ['label_key' => 'admin/settings/sync_adapters.jamf_extra_ram_gb'],
            'jamf_storage_gb' => ['label_key' => 'admin/settings/sync_adapters.jamf_extra_storage_gb'],
            // Mobile-only fields. Blank on computer records so admins
            // can still map them without worrying about overwriting a
            // computer's custom field with an empty string.
            'jamf_mobile_device_type' => ['label_key' => 'admin/settings/sync_adapters.jamf_extra_mobile_device_type'],
            'jamf_mobile_managed' => ['label_key' => 'admin/settings/sync_adapters.jamf_extra_mobile_managed', 'type' => 'boolean'],
            'jamf_mobile_supervised' => ['label_key' => 'admin/settings/sync_adapters.jamf_extra_mobile_supervised', 'type' => 'boolean'],
            'jamf_mobile_storage_gb' => ['label_key' => 'admin/settings/sync_adapters.jamf_extra_mobile_storage_gb'],
        ];
    }

    public function supportsGroupScoping(): bool
    {
        return true;
    }

    public function vendorGroupLabel(): string
    {
        return trans('admin/settings/sync_adapters.vendor_group_jamf_site');
    }

    public function fetchGroups(): array
    {
        $client = new JamfClient(
            baseUrl: $this->url(),
            clientId: $this->credential('client_id'),
            clientSecret: $this->credential('client_secret'),
        );

        return array_map(
            fn (array $site) => [
                'id' => (string) ($site['id'] ?? ''),
                'label' => (string) ($site['name'] ?? $site['id'] ?? '?'),
            ],
            $client->sites(),
        );
    }

    public function pull(): iterable
    {
        $client = new JamfClient(
            baseUrl: $this->url(),
            clientId: $this->credential('client_id'),
            clientSecret: $this->credential('client_secret'),
        );

        $enrichmentEnabled = $this->appleDbEnrichmentEnabled();
        $enrichmentHandled = [];

        $filter = $this->resolveGroupFilter($client);

        foreach ($client->computers() as $computer) {
            if ($filter !== null && ! isset($filter['computerIds'][(int) Arr::get($computer, 'id')])) {
                continue;
            }
            $record = $this->normalizeComputer($computer);
            if ($enrichmentEnabled) {
                $enrichmentHandled = $this->enrichFromAppleDb($record, $enrichmentHandled);
            }
            yield $record;
        }

        if ($this->shouldPullMobileDevices()) {
            foreach ($client->mobileDevices() as $device) {
                if ($filter !== null && ! isset($filter['mobileIds'][(int) Arr::get($device, 'id')])) {
                    continue;
                }
                $record = $this->normalizeMobileDevice($device);
                if ($enrichmentEnabled) {
                    $enrichmentHandled = $this->enrichFromAppleDb($record, $enrichmentHandled);
                }
                yield $record;
            }
        }
    }

    /**
     * Resolve the admin-entered group_filter into the union of computer
     * and mobile-device IDs that belong to any of the named groups.
     * Entries can be group names (friendly) or raw IDs (short-circuit).
     * Names are resolved against the full group list once per sync.
     * Unresolvable names log a warning and get skipped, matching the
     * Intune scope-tag flow, so one bad entry does not kill the pull.
     *
     * Mobile device groups are only consulted when the include-mobile
     * toggle is also on, so Jamf tenants that don't have mobile devices
     * enabled never pay for the extra classic-API calls.
     *
     * Returns null when no filter is configured so the pull short-
     * circuits the membership gate entirely.
     *
     * @return array{computerIds: array<int, int>, mobileIds: array<int, int>}|null
     */
    private function resolveGroupFilter(JamfClient $client): ?array
    {
        $raw = '';
        try {
            $raw = (string) $this->credential('group_filter');
        } catch (\Throwable) {
            // Credential not set yet. Treat as unconfigured.
        }

        $entries = array_values(array_filter(array_map('trim', explode(',', $raw))));
        if ($entries === []) {
            return null;
        }

        $computerIds = [];
        $mobileIds = [];

        $this->collectMembersForFilter(
            $entries,
            $client->computerGroups(),
            fn (int $id) => $client->computerGroupMembers($id),
            $computerIds,
            'computer',
        );

        if ($this->shouldPullMobileDevices()) {
            $this->collectMembersForFilter(
                $entries,
                $client->mobileDeviceGroups(),
                fn (int $id) => $client->mobileDeviceGroupMembers($id),
                $mobileIds,
                'mobile',
            );
        }

        return [
            'computerIds' => array_flip($computerIds),
            'mobileIds' => array_flip($mobileIds),
        ];
    }

    /**
     * Shared resolution helper used for both computer groups and mobile
     * device groups. Builds a name-and-id index from the group list,
     * resolves each entry to a concrete group id, and asks $memberFetcher
     * for that group's members. Unresolvable entries log once per
     * namespace so an admin typo in a filter names "Law School Macs" is
     * visible in the sync-adapters log.
     *
     * @param  array<int, string>  $entries
     * @param  array<int, array<string, mixed>>  $groups
     * @param  callable(int): array<int, int>  $memberFetcher
     * @param  array<int, int>  &$into
     */
    private function collectMembersForFilter(
        array $entries,
        array $groups,
        callable $memberFetcher,
        array &$into,
        string $namespace,
    ): void {
        $nameToId = [];
        $idSet = [];
        foreach ($groups as $group) {
            $name = (string) ($group['name'] ?? '');
            $id = isset($group['id']) ? (int) $group['id'] : null;
            if ($name === '' || $id === null) {
                continue;
            }
            $nameToId[strtolower($name)] = $id;
            $idSet[$id] = true;
        }

        foreach ($entries as $entry) {
            $lower = strtolower($entry);
            $groupId = null;
            if (isset($nameToId[$lower])) {
                $groupId = $nameToId[$lower];
            } elseif (ctype_digit($entry) && isset($idSet[(int) $entry])) {
                $groupId = (int) $entry;
            }

            if ($groupId === null) {
                // Computer groups and mobile-device groups are separate
                // namespaces in Jamf. An entry that doesn't resolve in
                // this namespace may still resolve in the other, so log
                // debug rather than warn here. A true typo is caught
                // because it fails in both namespaces and the admin
                // notices zero devices are matching.
                Log::channel('sync-adapters')->debug(sprintf(
                    '%s: group_filter entry "%s" did not match any %s group. Skipping in this namespace.',
                    $this->name(),
                    $entry,
                    $namespace,
                ));

                continue;
            }

            foreach ($memberFetcher($groupId) as $memberId) {
                $into[] = $memberId;
            }
        }
    }

    /**
     * Pass the Jamf record to the shared enrichment flow. Both the
     * computer and mobile normalize paths put the AppleDB-shape
     * hardware identifier into jamf_model_identifier, so the lookup
     * is unified here. hardwareModel ends up as the AssetModel name
     * (Jamf's model string is already the marketing-ish shape
     * admins see in Snipe-IT today).
     *
     * @param  array<string, bool>  $handled
     * @return array<string, bool>
     */
    private function enrichFromAppleDb(HostInventoryRecord $record, array $handled): array
    {
        $modelName = $record->hardwareModel;
        if (! is_string($modelName) || $modelName === '') {
            return $handled;
        }

        $productType = $record->extra['jamf_model_identifier'] ?? null;
        if (! is_string($productType) || $productType === '') {
            return $handled;
        }

        return $this->enrichModelFromAppleDb($modelName, $productType, null, $handled);
    }

    /**
     * Opt-in toggle for syncing iOS / iPadOS / tvOS devices alongside
     * computers. Off by default so adding the mobile pull doesn't
     * surprise existing installs on their next scheduled run. Reads via
     * credential() because the base class's config storage treats
     * every schema entry (secret or not) through the same get/set
     * path.
     */
    private function shouldPullMobileDevices(): bool
    {
        try {
            return $this->credential('include_mobile_devices') === '1';
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Convert a Jamf computer payload into the normalized record shape.
     * Jamf's numeric `id` is stable per enrollment and is what we key
     * asset_external_sources on. Fields come from the GENERAL, HARDWARE, and
     * OPERATING_SYSTEM sections (the client only requests those).
     *
     * Computer sourceIds stay bare numeric to preserve back-compat with
     * every existing asset_external_sources.external_id row written by
     * this adapter before mobile support landed. The mobile path uses a
     * `mobile:` prefix instead to keep the two Jamf namespaces from
     * colliding (Jamf's computer id 42 and mobile id 42 are unrelated).
     *
     * @param  array<string, mixed>  $computer
     */
    private function normalizeComputer(array $computer): HostInventoryRecord
    {
        $productType = Arr::get($computer, 'hardware.modelIdentifier');
        $modelName = Arr::get($computer, 'hardware.model');
        $canonical = $this->canonicalIdentityFor($productType, $modelName);
        $appleDbExtras = $this->appleDbExtrasFor($productType, $modelName);

        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: (string) Arr::get($computer, 'id'),
            hostname: Arr::get($computer, 'general.name'),
            hardwareSerial: Arr::get($computer, 'hardware.serialNumber'),
            hardwareModel: Arr::get($computer, 'hardware.model'),
            manufacturer: Arr::get($computer, 'hardware.make'),
            primaryMac: Arr::get($computer, 'hardware.macAddress'),
            primaryIp: null,
            os: Arr::get($computer, 'operatingSystem.name'),
            osVersion: Arr::get($computer, 'operatingSystem.version'),
            lastSeen: $this->parseTimestamp(Arr::get($computer, 'general.lastContactTime')),
            assetTag: Arr::get($computer, 'general.assetTag'),
            assignedUserEmail: Arr::get($computer, 'userAndLocation.email'),
            assignedUserName: Arr::get($computer, 'userAndLocation.username'),
            vendorGroupId: Arr::has($computer, 'general.site.id') ? (string) Arr::get($computer, 'general.site.id') : null,
            extra: [
                ...$appleDbExtras,
                'jamf_udid' => Arr::get($computer, 'udid'),
                'jamf_last_enrolled' => Arr::get($computer, 'general.lastEnrolledDate'),
                'jamf_model_identifier' => Arr::get($computer, 'hardware.modelIdentifier'),
                'jamf_ram_gb' => self::megabytesToGb(Arr::get($computer, 'hardware.totalRamMegabytes')),
                'jamf_storage_gb' => self::megabytesToGb(self::primaryDiskMegabytes($computer)),
            ],
            canonicalMarketingName: $canonical['canonicalMarketingName'],
            canonicalModelNumber: $canonical['canonicalModelNumber'],
        );
    }

    /**
     * Extract the Mac's internal boot disk size from a Jamf STORAGE
     * section. Jamf returns an array of disks under storage.disks that
     * includes any external drive plugged in at inventory time, so
     * taking the first disk or summing the array would inflate the
     * value. Each disk's partitions carry a partitionType enum, and
     * exactly one partition across the whole machine carries
     * partitionType="BOOT" (the macOS boot volume), so the disk that
     * contains that partition is the internal SSD. Fall back to
     * disks[0] when Jamf doesn't tag any partition as BOOT (older
     * Jamf versions and some edge states), which is still correct in
     * practice because the first disk is disk0 on every modern Mac.
     *
     * Returns null when Jamf didn't return a storage section for this
     * computer (common on offline-at-inventory machines or on tenants
     * that haven't enabled STORAGE collection).
     *
     * @param  array<string, mixed>  $computer
     */
    /**
     * Normalize an MB value to GB for cross-adapter unit consistency.
     * Divides by 1000 (decimal), matching Apple's marketed-capacity
     * convention and the GB unit the other storage/memory extras
     * across sync adapters already use. Null/non-numeric input returns
     * null so empty storage sections stay empty rather than becoming 0
     * (which an admin filter would misread as a real zero-GB value).
     */
    private static function megabytesToGb(mixed $megabytes): int|float|null
    {
        if (! is_numeric($megabytes)) {
            return null;
        }
        $gb = $megabytes / 1000;

        return $gb == (int) $gb ? (int) $gb : round($gb, 2);
    }

    private static function primaryDiskMegabytes(array $computer): ?int
    {
        $disks = Arr::get($computer, 'storage.disks');
        if (! is_array($disks) || $disks === []) {
            return null;
        }

        foreach ($disks as $disk) {
            $partitions = Arr::get($disk, 'partitions', []);
            if (! is_array($partitions)) {
                continue;
            }
            foreach ($partitions as $partition) {
                if ((Arr::get($partition, 'partitionType') ?? '') === 'BOOT') {
                    $size = Arr::get($disk, 'sizeMegabytes');

                    return is_numeric($size) ? (int) $size : null;
                }
            }
        }

        $size = Arr::get($disks[0], 'sizeMegabytes');

        return is_numeric($size) ? (int) $size : null;
    }

    /**
     * Convert a /api/v2/mobile-devices list entry into the normalized
     * record shape. Mobile Jamf payloads are flatter than computer
     * ones: everything except assigned-user info lives at the top
     * level, so no section dot-paths. Manufacturer isn't carried
     * either (Jamf Pro's mobile fleet is Apple-only in practice), so
     * we hard-code 'Apple' to match what the computer path emits for
     * macs and let model-dedup land both on the same manufacturer.
     *
     * @param  array<string, mixed>  $device
     */
    private function normalizeMobileDevice(array $device): HostInventoryRecord
    {
        $sourceId = 'mobile:'.(string) Arr::get($device, 'id');
        $productType = Arr::get($device, 'modelIdentifier');
        $modelName = Arr::get($device, 'model');
        $canonical = $this->canonicalIdentityFor($productType, $modelName);
        $appleDbExtras = $this->appleDbExtrasFor($productType, $modelName);

        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: $sourceId,
            hostname: Arr::get($device, 'name'),
            hardwareSerial: Arr::get($device, 'serialNumber'),
            hardwareModel: Arr::get($device, 'model'),
            manufacturer: 'Apple',
            primaryMac: Arr::get($device, 'wifiMacAddress'),
            primaryIp: Arr::get($device, 'ipAddress'),
            os: Arr::get($device, 'osType'),
            osVersion: Arr::get($device, 'osVersion'),
            lastSeen: $this->parseTimestamp(Arr::get($device, 'lastInventoryUpdateTimestamp')),
            assetTag: Arr::get($device, 'assetTag'),
            assignedUserEmail: Arr::get($device, 'location.emailAddress'),
            assignedUserName: Arr::get($device, 'location.username'),
            vendorGroupId: Arr::has($device, 'site.id') ? (string) Arr::get($device, 'site.id') : null,
            extra: [
                ...$appleDbExtras,
                'jamf_udid' => Arr::get($device, 'udid'),
                'jamf_model_identifier' => Arr::get($device, 'modelIdentifier'),
                'jamf_mobile_device_type' => Arr::get($device, 'deviceType'),
                'jamf_mobile_managed' => Arr::get($device, 'managed'),
                'jamf_mobile_supervised' => Arr::get($device, 'supervised'),
                'jamf_mobile_storage_gb' => self::megabytesToGb(Arr::get($device, 'capacityMb')),
            ],
            canonicalMarketingName: $canonical['canonicalMarketingName'],
            canonicalModelNumber: $canonical['canonicalModelNumber'],
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
     * Jamf Pro writes are gated by the API Client's role privileges.
     * Both pull + push work with the same OAuth client when the role
     * grants the "Update Computers" privilege. No tier / license gate,
     * so canPush is always true for a configured instance.
     */
    public function canPush(): bool
    {
        return true;
    }

    /**
     * Jamf Pro's computer record has a freeform notes field at
     * general.notes on the inventory-detail JSON. Dotted path so
     * Arr::set can slot the composed value into the right nested
     * object.
     */
    public function notesFieldTarget(): ?string
    {
        return 'general.notes';
    }

    /**
     * Push Snipe-IT-authoritative fields to Jamf Pro via the newer
     * JSON detail endpoint. The payload uses Jamf's nested-object
     * shape (assetTag lives inside general per the ComputerGeneralUpdate
     * schema, not userAndLocation), so sourceFieldToJamfPath() returns
     * dotted paths that get assembled via Arr::set before the PATCH.
     *
     * @param  array<int, string>  $changedFields
     */
    public function push(Asset $asset, array $changedFields = []): bool
    {
        return $this->pushViaSinglePayload($asset, $changedFields);
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<int, string>}
     */
    protected function buildPushPayload(Asset $asset): array
    {
        $payload = [];
        $touched = [];
        foreach ($this->pushDirectedFields() as $field) {
            $path = self::sourceFieldToJamfPath($field);
            if ($path === null) {
                continue;
            }

            $value = $this->assetValueForSourceField($asset, $field);
            if ($value === null) {
                continue;
            }

            Arr::set($payload, $path, $value);
            $touched[] = $path;
        }

        return [$payload, $touched];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function dispatchPush(\App\Models\AssetExternalSource $externalSource, array $payload): void
    {
        // Mobile Jamf devices are pull-only in this adapter. The push
        // path only runs against /api/v1/computers-inventory-detail, so
        // short-circuit on the `mobile:` prefix the mobile normalizer
        // writes. Snipe-IT-authoritative fields on a mobile asset stay
        // local to Snipe-IT.
        if (str_starts_with($externalSource->external_id, 'mobile:')) {
            return;
        }

        $client = new JamfClient(
            baseUrl: $this->url(),
            clientId: $this->credential('client_id'),
            clientSecret: $this->credential('client_secret'),
        );
        $client->updateComputerDetail($externalSource->external_id, $payload);
    }

    /**
     * Map a source-field name to its dotted path in Jamf's
     * computers-inventory-detail JSON. Return null for fields Jamf
     * doesn't expose as writable (hostname is derived from the device
     * itself, not admin-settable).
     */
    private static function sourceFieldToJamfPath(string $field): ?string
    {
        return match ($field) {
            // Jamf's ComputerGeneralUpdate schema is where assetTag
            // lives. userAndLocation does NOT accept assetTag on the
            // PATCH side (confirmed against Jamf Pro 11.32 OpenAPI
            // schema, see issue #19851). The pull side already reads
            // from general.assetTag, so push matches.
            'asset_tag' => 'general.assetTag',
            default => null,
        };
    }
}
