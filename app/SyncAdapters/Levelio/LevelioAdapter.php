<?php

namespace App\SyncAdapters\Levelio;

use App\Models\Asset;
use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\PushableSyncAdapter;
use App\SyncAdapters\Traits\UsesAppleDbEnrichment;
use Carbon\Carbon;
use Illuminate\Support\Arr;

/**
 * Level.io adapter. Pulls managed-device inventory from a Level.io
 * tenant via the v2 REST API and normalizes it into
 * HostInventoryRecord objects. Also pushes Snipe-IT-authoritative
 * values back to Level.io when the admin configures a composed notes
 * template.
 *
 * Auth is a long-lived API key issued from the Level.io dashboard
 * (Settings -> API Keys). The key goes on the Authorization header
 * verbatim, no Bearer prefix.
 *
 * Push surface is small. Level.io's PATCH /v2/devices/{id} only
 * accepts `name`, `notes`, and `group_id`. Snipe-IT's composed notes
 * template is routed to `notes`. Pushing `name` would rename the
 * device from Level's view (devices self-report hostname), so that
 * field stays read-only here.
 *
 * Level.io API reference: https://developers.level.io/
 */
class LevelioAdapter extends PushableSyncAdapter
{
    use UsesAppleDbEnrichment;

    public static function typeLabel(): string
    {
        return 'Level.io';
    }

    public static function typeSlug(): string
    {
        return 'levelio';
    }

    public static function docsUrl(): ?string
    {
        return 'https://developers.level.io/';
    }

    public function baseUrlPlaceholder(): ?string
    {
        return 'https://api.level.io';
    }

    public function baseUrlHelp(): ?string
    {
        return trans('admin/settings/sync_adapters.levelio_base_url_help');
    }

    public function settingsSchema(): array
    {
        return [
            [
                'key' => 'api_key',
                'label' => trans('admin/settings/sync_adapters.label_api_key'),
                'secret' => true,
                'help' => trans('admin/settings/sync_adapters.levelio_api_key_help'),
            ],
            $this->appleDbSettingsField(),
        ];
    }

    public function extraFields(): array
    {
        return [
            ...$this->appleDbExtraFieldsDeclaration(),
            'levelio_online' => ['label_key' => 'admin/settings/sync_adapters.levelio_extra_online', 'type' => 'boolean'],
            'levelio_role' => ['label_key' => 'admin/settings/sync_adapters.levelio_extra_role'],
            'levelio_tags' => ['label_key' => 'admin/settings/sync_adapters.levelio_extra_tags'],
            'levelio_security_score' => ['label_key' => 'admin/settings/sync_adapters.levelio_extra_security_score'],
            'levelio_security_risk' => ['label_key' => 'admin/settings/sync_adapters.levelio_extra_security_risk'],
            'levelio_cpu_model' => ['label_key' => 'admin/settings/sync_adapters.levelio_extra_cpu_model'],
            'levelio_cpu_cores' => ['label_key' => 'admin/settings/sync_adapters.levelio_extra_cpu_cores'],
            'levelio_total_memory_gb' => ['label_key' => 'admin/settings/sync_adapters.levelio_extra_total_memory_gb'],
            'levelio_disk_total_gb' => ['label_key' => 'admin/settings/sync_adapters.levelio_extra_disk_total_gb'],
            'levelio_disk_free_gb' => ['label_key' => 'admin/settings/sync_adapters.levelio_extra_disk_free_gb'],
            'levelio_notes' => ['label_key' => 'admin/settings/sync_adapters.levelio_extra_notes'],
        ];
    }

    public function supportsGroupScoping(): bool
    {
        return true;
    }

    public function vendorGroupLabel(): string
    {
        return trans('admin/settings/sync_adapters.vendor_group_levelio_group');
    }

    public function fetchGroups(): array
    {
        $client = $this->client();

        return array_map(
            fn (array $group) => [
                'id' => (string) ($group['id'] ?? ''),
                'label' => (string) ($group['name'] ?? $group['id'] ?? '?'),
            ],
            $client->groups(),
        );
    }

    public function pull(): iterable
    {
        $client = $this->client();
        $enrichmentEnabled = $this->appleDbEnrichmentEnabled();
        $enrichmentHandled = [];

        foreach ($client->devices() as $device) {
            $record = $this->normalize($device);
            if ($enrichmentEnabled) {
                $enrichmentHandled = $this->enrichFromAppleDb($record, $enrichmentHandled);
            }
            yield $record;
        }
    }

    /**
     * Level.io's device payload exposes the marketing-shape model
     * string on the top-level `model` field, but no AppleDB-shape
     * hardware identifier. Enrichment therefore falls back to the
     * name-only AppleDB lookup path, which the trait handles when the
     * caller passes a null productType.
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

        return $this->enrichModelFromAppleDb($modelName, null, null, $handled);
    }

    /**
     * Convert a Level.io device payload into the normalized record
     * shape. Level.io's `id` is stable per enrolled device and is
     * what we key asset_external_sources on.
     *
     * Falls back to motherboard.manufacturer / motherboard.model
     * when the top-level `manufacturer` / `model` fields are empty.
     * Level sources both from DMI on Windows and from
     * I/O Registry on macOS, but one side is sometimes blank
     * depending on how the Level agent harvested the host.
     *
     * @param  array<string, mixed>  $device
     */
    private function normalize(array $device): HostInventoryRecord
    {
        $modelName = Arr::get($device, 'model') ?: Arr::get($device, 'motherboard.model');
        $manufacturer = Arr::get($device, 'manufacturer') ?: Arr::get($device, 'motherboard.manufacturer');
        $canonical = $this->canonicalIdentityFor(null, $modelName);
        $appleDbExtras = $this->appleDbExtrasFor(null, $modelName);

        $osName = Arr::get($device, 'operating_system.full_operating_system')
            ?: Arr::get($device, 'platform');
        // `minor_version` carries the fully-qualified version string
        // ("10.0.19045.5247"), while `major_version` is the shorter
        // "10.0" form. Prefer minor and fall back only when the
        // vendor response does not include it.
        $osVersion = Arr::get($device, 'operating_system.minor_version')
            ?: Arr::get($device, 'operating_system.major_version');

        $interface = $this->primaryInterface(Arr::get($device, 'network_interfaces', []));
        $primaryMac = $interface['mac_address'] ?? null;
        $primaryIp = $this->firstIp(Arr::get($interface, 'ip_addresses', []));

        $tags = Arr::get($device, 'tags', []);
        $tagNames = is_array($tags)
            ? array_values(array_filter(array_map(
                fn ($tag) => is_array($tag) ? ($tag['name'] ?? null) : (is_string($tag) ? $tag : null),
                $tags,
            )))
            : [];

        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: (string) Arr::get($device, 'id'),
            hostname: Arr::get($device, 'hostname') ?: Arr::get($device, 'nickname'),
            hardwareSerial: Arr::get($device, 'serial_number'),
            hardwareModel: $modelName,
            manufacturer: $manufacturer,
            primaryMac: $primaryMac,
            primaryIp: $primaryIp,
            os: $osName,
            osVersion: $osVersion,
            lastSeen: $this->parseTimestamp(Arr::get($device, 'last_seen_at')),
            assetTag: null,
            assignedUserEmail: null,
            assignedUserName: Arr::get($device, 'last_logged_in_user'),
            vendorGroupId: Arr::has($device, 'group_id') ? (string) Arr::get($device, 'group_id') : null,
            extra: [
                ...$appleDbExtras,
                'levelio_online' => Arr::get($device, 'online'),
                'levelio_role' => Arr::get($device, 'role'),
                'levelio_tags' => $tagNames !== [] ? implode(', ', $tagNames) : null,
                'levelio_security_score' => Arr::get($device, 'security.score'),
                'levelio_security_risk' => Arr::get($device, 'security.risk'),
                'levelio_cpu_model' => Arr::get($device, 'cpus.0.model'),
                'levelio_cpu_cores' => Arr::get($device, 'cpu_cores') ?? Arr::get($device, 'cpus.0.cores'),
                'levelio_total_memory_gb' => $this->bytesToGb(Arr::get($device, 'total_memory')),
                'levelio_disk_total_gb' => $this->bytesToGb($this->sumBytes(Arr::get($device, 'disks', []), 'size')),
                'levelio_disk_free_gb' => $this->bytesToGb($this->sumBytes(Arr::get($device, 'disk_partitions', []), 'free_space')),
                'levelio_notes' => Arr::get($device, 'notes'),
            ],
            canonicalMarketingName: $canonical['canonicalMarketingName'],
            canonicalModelNumber: $canonical['canonicalModelNumber'],
        );
    }

    /**
     * Pick the interface that's most likely to be the primary one. Level
     * returns every NIC the host reports, including virtual, loopback,
     * and VPN adapters. First preference: an interface with a non-empty
     * mac_address AND an IPv4 address, since those two together exclude
     * most virtual adapters. Falls back to the first interface with any
     * mac_address so we at least have the hardware-level identifier even
     * when the host has no live link.
     *
     * @param  array<int, array<string, mixed>>|mixed  $interfaces
     * @return array<string, mixed>
     */
    private function primaryInterface(mixed $interfaces): array
    {
        if (! is_array($interfaces)) {
            return [];
        }

        foreach ($interfaces as $interface) {
            if (! is_array($interface)) {
                continue;
            }
            $mac = $interface['mac_address'] ?? null;
            $ips = $interface['ip_addresses'] ?? [];
            if (is_string($mac) && $mac !== '' && is_array($ips) && $this->firstIp($ips) !== null) {
                return $interface;
            }
        }

        foreach ($interfaces as $interface) {
            if (! is_array($interface)) {
                continue;
            }
            $mac = $interface['mac_address'] ?? null;
            if (is_string($mac) && $mac !== '') {
                return $interface;
            }
        }

        return [];
    }

    /**
     * Extract the first usable IP from Level's ip_addresses array.
     * The array can contain plain strings or objects shaped
     * {address: ..., family: v4|v6}, depending on how the agent
     * reported the interface. Prefer v4 so dashboards don't show a
     * link-local v6 when a routable v4 is available.
     *
     * @param  array<int, mixed>  $ips
     */
    private function firstIp(array $ips): ?string
    {
        $fallback = null;
        foreach ($ips as $ip) {
            if (is_string($ip) && $ip !== '') {
                if ($fallback === null) {
                    $fallback = $ip;
                }
                if (str_contains($ip, '.')) {
                    return $ip;
                }

                continue;
            }
            if (is_array($ip)) {
                $address = $ip['address'] ?? null;
                $family = $ip['family'] ?? null;
                if (is_string($address) && $address !== '') {
                    if ($family === 'v4' || str_contains($address, '.')) {
                        return $address;
                    }
                    $fallback ??= $address;
                }
            }
        }

        return $fallback;
    }

    /**
     * Convert a byte count to a one-decimal GB string, or null on
     * null / zero input. Admins mapping the memory / disk extras to
     * custom fields expect human-readable GB rather than raw bytes.
     * Uses 1024-based GB (gibibyte) to match how OS vendors advertise
     * installed memory and disk capacity.
     */
    private function bytesToGb(mixed $bytes): ?string
    {
        if (! is_numeric($bytes) || (int) $bytes <= 0) {
            return null;
        }

        return number_format((int) $bytes / 1024 ** 3, 1, '.', '');
    }

    /**
     * Sum a given byte-size field across a Level.io hardware array
     * (`disks[]`, `memory[]`). Entries missing the field or carrying
     * non-numeric values skip rather than poison the sum.
     *
     * @param  array<int, mixed>  $items
     */
    private function sumBytes(array $items, string $sizeField): int
    {
        $total = 0;
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $value = $item[$sizeField] ?? null;
            if (is_numeric($value)) {
                $total += (int) $value;
            }
        }

        return $total;
    }

    private function parseTimestamp(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value);
    }

    /**
     * Level.io's device PATCH endpoint accepts any valid API key, so
     * push is always available on a configured instance (no per-tier
     * push gate like Fleet's Teams edition).
     */
    public function canPush(): bool
    {
        return true;
    }

    /**
     * Level.io exposes a freeform `notes` field on each device. The
     * composed notes template rendered by composeNotesForPush() is
     * routed here so a Snipe-IT asset's tag + status + assigned user
     * surfaces on the Level device page.
     */
    public function notesFieldTarget(): ?string
    {
        return 'notes';
    }

    public function supportsComposedNotesPush(): bool
    {
        return true;
    }

    /**
     * Delegates to the base template, which handles the dry-run
     * branch, splices composed notes into the payload at notesFieldTarget,
     * and writes the success log line. Level.io's narrow PATCH surface
     * (`name` / `notes` / `group_id`) means the per-field payload is
     * always empty and composed notes carries the push.
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
        return [[], []];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function dispatchPush(\App\Models\AssetExternalSource $externalSource, array $payload): void
    {
        $this->client()->updateDevice($externalSource->external_id, $payload);
    }

    private function client(): LevelioClient
    {
        return new LevelioClient(
            baseUrl: $this->url(),
            apiKey: $this->credential('api_key'),
        );
    }
}
