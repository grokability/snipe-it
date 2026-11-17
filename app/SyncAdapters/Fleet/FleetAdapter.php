<?php

namespace App\SyncAdapters\Fleet;

use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\SyncAdapter;
use App\SyncAdapters\Traits\UsesAppleDbEnrichment;
use Carbon\Carbon;
use Illuminate\Support\Arr;

/**
 * Fleet adapter. Pulls host inventory from a Fleet (FleetDM) instance
 * and normalizes it into HostInventoryRecord objects.
 *
 * Push (Snipe-IT -> Fleet) is not implemented because Fleet has no
 * first-class per-host writable metadata field that maps to Snipe-IT
 * concepts like asset_tag or notes. Fleet labels are group membership
 * (targeting devices for policies / reports / software), not a
 * per-host key-value store, so encoding an asset_tag as a label was a
 * semantic mismatch. Revisit if Fleet exposes per-host Custom
 * Attributes on a future release.
 */
class FleetAdapter extends SyncAdapter
{
    use UsesAppleDbEnrichment;

    /**
     * Fleet platform values treated as Apple hardware. Non-Apple
     * hosts have hardware_model strings that will never match an
     * appledb.dev entry, so gating here saves a wasted network call
     * per non-Apple host on every sync.
     */
    private const APPLE_PLATFORMS = ['darwin', 'ios', 'ipados'];

    public static function typeLabel(): string
    {
        return 'Fleet';
    }

    public static function typeSlug(): string
    {
        return 'fleet';
    }

    public static function docsUrl(): ?string
    {
        return 'https://fleetdm.com/docs/rest-api/rest-api';
    }

    public function settingsSchema(): array
    {
        return [
            [
                'key' => 'token',
                'label' => trans('admin/settings/sync_adapters.label_api_token'),
                'secret' => true,
                'help' => trans('admin/settings/sync_adapters.fleet_token_help'),
            ],
            $this->appleDbSettingsField(),
        ];
    }

    public function extraFields(): array
    {
        return [
            ...$this->appleDbExtraFieldsDeclaration(),
            'fleet_team' => ['label_key' => 'admin/settings/sync_adapters.extra_team'],
            'fleet_labels' => ['label_key' => 'admin/settings/sync_adapters.extra_labels'],
            'fleet_uuid' => ['label_key' => 'admin/settings/sync_adapters.extra_uuid'],
            'fleet_status' => ['label_key' => 'admin/settings/sync_adapters.extra_status'],
            'fleet_model_marketing_name' => ['label_key' => 'admin/settings/sync_adapters.extra_model_marketing_name'],
            'fleet_byod' => [
                'label_key' => 'admin/settings/sync_adapters.extra_byod',
                'type' => 'boolean',
            ],
        ];
    }

    /**
     * Fleet accepts label writes on any Admin-role API token. No
     * tier gate: label endpoints are on Free (unlike Teams, which
     * 402s). Push availability is purely a permissions question,
     * enforced by Fleet on the individual request.
     */
    public function canPush(): bool
    {
        return true;
    }

    public function supportsGroupScoping(): bool
    {
        // Fleet Teams is a Fleet Premium feature. When the cached
        // license tier (populated by afterSaveConfig() below) is
        // "free" we hide the group-mapping UI entirely so admins
        // don't try to Refresh and hit a 402. Any other value
        // (premium, unknown, or absent) shows it. Unknown covers
        // fresh installs before the first save and pre-existing
        // instances upgraded from earlier versions.
        return \App\Models\SyncAdapterConfig::get($this->instance->id, 'fleet_license_tier') !== 'free';
    }

    /**
     * After the admin saves credentials, probe the Fleet /config
     * endpoint to learn the license tier and cache it. Called only
     * when a URL + token are present since the endpoint requires auth.
     * Silent on failure so a misconfigured / offline Fleet doesn't
     * block the save.
     */
    protected function afterSaveConfig(): void
    {
        $url = $this->url();
        if ($url === '') {
            return;
        }

        try {
            $token = $this->credential('token');
        } catch (\Throwable) {
            return;
        }

        if ($token === '') {
            return;
        }

        $tier = (new FleetClient(baseUrl: $url, token: $token))->licenseTier();

        if ($tier !== null) {
            \App\Models\SyncAdapterConfig::put(
                $this->instance->id,
                'fleet_license_tier',
                $tier,
            );
        }
    }

    public function vendorGroupLabel(): string
    {
        return trans('admin/settings/sync_adapters.vendor_group_fleet_team');
    }

    public function fetchGroups(): array
    {
        $client = new FleetClient(baseUrl: $this->url(), token: $this->credential('token'));

        return array_map(
            fn (array $team) => [
                'id' => (string) ($team['id'] ?? ''),
                'label' => (string) ($team['name'] ?? $team['id'] ?? '?'),
            ],
            $client->teams(),
        );
    }

    public function pull(): iterable
    {
        $client = new FleetClient(baseUrl: $this->url(), token: $this->credential('token'));

        // Fleet's list endpoint returns a slimmed-down host summary
        // that omits device_mapping and end_users. When the admin has
        // configured user matching, hydrate each host with the detail
        // endpoint so the assigned-user extractors have something to
        // work with. Pays an N+1 API cost per sync, only when needed.
        $needsDetail = $this->userMatchStrategy() !== 'none';

        $enrichmentEnabled = $this->appleDbEnrichmentEnabled();
        $enrichmentHandled = [];

        foreach ($client->hosts() as $host) {
            if ($needsDetail && isset($host['id'])) {
                $detail = $client->hostDetail((int) $host['id']);
                if ($detail !== null) {
                    $host = $detail;
                }
            }

            $record = $this->normalize($host);

            if ($enrichmentEnabled && $this->isAppleHost($host)) {
                $enrichmentHandled = $this->enrichFromAppleDb($record, $enrichmentHandled);
            }

            yield $record;
        }
    }

    /**
     * Resolve the Fleet record's AssetModel name + AppleDB-shape
     * productType and delegate to the shared enrichment helper.
     *
     * Fleet's hardware_model is osquery's raw sysctl value which is
     * already the AppleDB identifier shape ("MacBookPro18,3") on
     * Apple devices. hardware_marketing_name is the human-readable
     * name, used as the AssetModel name when the admin routes
     * fleet_model_marketing_name to native:model.
     *
     * @param  array<string, bool>  $handled
     * @return array<string, bool>
     */
    private function enrichFromAppleDb(HostInventoryRecord $record, array $handled): array
    {
        $modelName = $this->mappingFor('fleet_model_marketing_name') === 'native:model'
            ? ($record->extra['fleet_model_marketing_name'] ?? $record->hardwareModel)
            : $record->hardwareModel;

        if (! is_string($modelName) || $modelName === '') {
            return $handled;
        }

        $productType = $record->hardwareModel;
        if (! is_string($productType) || $productType === '') {
            return $handled;
        }

        return $this->enrichModelFromAppleDb($modelName, $productType, null, $handled);
    }

    /**
     * @param  array<string, mixed>  $host
     */
    private function isAppleHost(array $host): bool
    {
        $platform = Arr::get($host, 'platform');

        return is_string($platform) && in_array($platform, self::APPLE_PLATFORMS, true);
    }

    /**
     * Convert a Fleet host payload into the normalized record shape.
     * Fleet's `id` field is stable per host enrollment and is what we
     * key asset_external_sources on. The sourceKey is the instance slug so
     * hosts from different Fleet instances stay distinct.
     *
     * @param  array<string, mixed>  $host
     */
    private function normalize(array $host): HostInventoryRecord
    {
        // Only Apple hosts have hardware_model values that resolve on
        // AppleDB. Non-Apple hosts carry strings like "ThinkPad X1
        // Carbon" that the service would always return null for.
        //
        // Pass the AssetModel name the framework will use (mirrors
        // the mapping ensureModelImage uses later in pull) so the
        // trait can short-circuit the AppleDB info call when an
        // existing row already has every enrichable field set.
        $modelName = $this->mappingFor('fleet_model_marketing_name') === 'native:model'
            ? (Arr::get($host, 'hardware_marketing_name') ?? Arr::get($host, 'hardware_model'))
            : Arr::get($host, 'hardware_model');
        $isAppleHost = $this->isAppleHost($host);
        $canonical = $isAppleHost
            ? $this->canonicalIdentityFor(Arr::get($host, 'hardware_model'), $modelName)
            : ['canonicalMarketingName' => null, 'canonicalModelNumber' => null];
        $appleDbExtras = $isAppleHost
            ? $this->appleDbExtrasFor(Arr::get($host, 'hardware_model'), $modelName)
            : ['appledb_marketing_name' => null, 'appledb_model_number' => null, 'appledb_release_date' => null, 'appledb_discontinued_date' => null];

        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: (string) Arr::get($host, 'id'),
            hostname: Arr::get($host, 'hostname'),
            hardwareSerial: Arr::get($host, 'hardware_serial'),
            hardwareModel: Arr::get($host, 'hardware_model'),
            manufacturer: Arr::get($host, 'hardware_vendor'),
            primaryMac: Arr::get($host, 'primary_mac'),
            primaryIp: Arr::get($host, 'primary_ip'),
            os: Arr::get($host, 'platform'),
            osVersion: Arr::get($host, 'os_version'),
            lastSeen: $this->parseTimestamp(Arr::get($host, 'seen_time')),
            assignedUserEmail: $this->extractUserEmail($host),
            assignedUserName: $this->extractUserName($host),
            vendorGroupId: Arr::has($host, 'team_id') ? (string) Arr::get($host, 'team_id') : null,
            extra: [
                ...$appleDbExtras,
                'fleet_team' => Arr::get($host, 'team_name'),
                'fleet_labels' => Arr::get($host, 'labels'),
                'fleet_uuid' => Arr::get($host, 'uuid'),
                'fleet_status' => Arr::get($host, 'status'),
                'fleet_model_marketing_name' => Arr::get($host, 'hardware_marketing_name'),
                // Fleet exposes BYOD via mdm.enrollment_status = "On
                // (personal)" for Apple ADUE and Android BYOD hosts.
                // Any other enrollment status (including omitted for
                // non-MDM hosts) is treated as not-BYOD.
                'fleet_byod' => Arr::get($host, 'mdm.enrollment_status') === 'On (personal)',
            ],
            canonicalMarketingName: $canonical['canonicalMarketingName'],
            canonicalModelNumber: $canonical['canonicalModelNumber'],
        );
    }

    /**
     * Fleet doesn't have one canonical "primary user email" field.
     * The Fleet Premium IdP / SCIM sync populates end_users[] with
     * idp_info[].email and other_emails[]. Some Fleet builds expose
     * a top-level primary_user_email on the detailed host endpoint.
     * Walk the known locations in preference order and return the
     * first one that yields something email-shaped.
     *
     * @param  array<string, mixed>  $host
     */
    private function extractUserEmail(array $host): ?string
    {
        $candidates = [
            // device_mapping is the Fleet Free feature that surfaces
            // Google Chrome sync email associations. Only present on
            // the detailed host endpoint with ?device_mapping=true.
            Arr::get($host, 'device_mapping.0.email'),
            Arr::get($host, 'primary_user_email'),
            Arr::get($host, 'end_users.0.idp_info.0.email'),
            Arr::get($host, 'end_users.0.other_emails.0.email'),
            Arr::get($host, 'end_users.0.other_emails.0'),
            Arr::get($host, 'end_users.0.email'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && str_contains($candidate, '@')) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Fleet returns a `users[]` array of every local UNIX account on
     * the host, including system daemons (`_installassistant`, `root`,
     * `nobody`, macOS's underscore-prefixed service accounts, etc.).
     * Filter those out and return the first remaining username. When
     * the host has exactly one real user account, that's almost always
     * the "primary user" for asset-management purposes. When multiple
     * real users exist we still pick the first, which is imperfect but
     * matches how vendors like Kandji surface primary user too.
     *
     * @param  array<string, mixed>  $host
     */
    private function extractUserName(array $host): ?string
    {
        $users = Arr::get($host, 'users');
        if (! is_array($users)) {
            return null;
        }

        foreach ($users as $user) {
            $username = Arr::get($user, 'username');
            if (! is_string($username) || $username === '') {
                continue;
            }

            // Skip macOS system accounts (all start with underscore),
            // Linux daemon accounts (root, daemon, bin, sys, sync, etc.),
            // and Windows built-ins that Fleet reports.
            if ($this->looksLikeSystemAccount($username)) {
                continue;
            }

            return $username;
        }

        return null;
    }

    /**
     * Heuristic system-account filter. Underscore-prefix catches every
     * macOS service account. The explicit deny-list catches the well-
     * known Linux and Windows accounts that Fleet reports alongside
     * real users.
     */
    private function looksLikeSystemAccount(string $username): bool
    {
        if (str_starts_with($username, '_')) {
            return true;
        }

        return in_array($username, [
            'root', 'daemon', 'bin', 'sys', 'sync', 'games', 'man', 'lp',
            'mail', 'news', 'uucp', 'proxy', 'www-data', 'backup', 'list',
            'irc', 'gnats', 'nobody', 'systemd-network', 'systemd-resolve',
            'messagebus', 'sshd', 'shutdown', 'halt', 'operator',
            'DefaultAccount', 'Guest', 'WDAGUtilityAccount', 'SYSTEM',
        ], true);
    }

    private function parseTimestamp(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value);
    }
}
