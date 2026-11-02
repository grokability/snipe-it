<?php

namespace App\SyncAdapters\Addigy;

use App\Models\Asset;
use App\Models\AssetExternalSource;
use App\Models\SyncAdapterConfig;
use App\Models\User;
use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\PushableSyncAdapter;
use App\SyncAdapters\Traits\UsesAppleDbEnrichment;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Addigy adapter. Pulls device inventory from Addigy API v2 and
 * normalizes it into HostInventoryRecord objects. Uses Addigy's single
 * `x-api-key` header (v2) rather than the retired v1 client-id +
 * client-secret pair.
 *
 * Push surface: Snipe-IT asset name -> Addigy device name via the Set
 * Device Name MDM command. End-user assignment (Snipe-IT assigned
 * user -> Addigy end-user tied to the device) is opt-in per instance
 * because it requires an Addigy end-user ID lookup per push.
 */
class AddigyAdapter extends PushableSyncAdapter
{
    use UsesAppleDbEnrichment;

    /**
     * Device-fact identifiers to request on every `POST /devices` call.
     * v2's response shape is a `facts` map keyed by identifier (each
     * value is `{type, value}`), so the client has to tell the server
     * up front which facts it wants. These names come from the v2
     * OpenAPI vocabulary (`AuditFilter.audit_field` example uses
     * `device_model_name`) plus the matching v1 field names where
     * Addigy preserved them. If a tenant renames or lacks a built-in
     * fact, its key is simply missing from the response map and
     * normalize() reads null.
     *
     * @var list<string>
     */
    private const DESIRED_FACTS = [
        'device_name',
        'serial_number',
        'device_model_name',
        // Addigy's "model identifier of the hardware" fact (e.g.
        // "MacBookPro18,3"). Needed for AppleDB enrichment lookup
        // since device_model_name only carries the marketing name.
        // Source: support.addigy.com Device Facts Overview.
        // This *might* be wrong.
        'product_name',
        'network_mac_address',
        'network_ip_address',
        'os_type',
        'os_version',
        'last_online_at',
        'asset_tag',
        'user_email',
        'policy_id',
        'is_supervised',
        'agent_version',
        // Mappable inventory
        'build_version',
        'udid',
        'has_mdm',
        'filevault_enabled',
        'firewall_enabled',
        'total_memory_gb',
        'disk_total_space_gb',
        'disk_free_space_gb',
        'warranty_expiration_date',
    ];

    public static function typeLabel(): string
    {
        return 'Addigy';
    }

    public static function typeSlug(): string
    {
        return 'addigy';
    }

    public static function docsUrl(): ?string
    {
        return 'https://api.addigy.com/api/v2/documentation/';
    }

    /**
     * Addigy v2's host is fixed at api.addigy.com, so the admin doesn't
     * type a URL. The client hardcodes the base URL.
     */
    public function usesConfigurableUrl(): bool
    {
        return false;
    }

    public function settingsSchema(): array
    {
        return [
            [
                'key' => 'api_key',
                'label' => trans('admin/settings/sync_adapters.label_api_key'),
                'secret' => true,
                'help' => trans('admin/settings/sync_adapters.addigy_api_key_help'),
            ],
            [
                'key' => 'push_end_user_assignment',
                'label' => trans('admin/settings/sync_adapters.addigy_push_end_user_assignment_label'),
                'type' => 'checkbox',
                'required' => false,
                'help' => trans('admin/settings/sync_adapters.addigy_push_end_user_assignment_help'),
            ],
            $this->appleDbSettingsField(),
        ];
    }

    public function extraFields(): array
    {
        return [
            ...$this->appleDbExtraFieldsDeclaration(),
            'addigy_policy_id' => ['label_key' => 'admin/settings/sync_adapters.extra_policy_id'],
            'addigy_supervised' => ['label_key' => 'admin/settings/sync_adapters.extra_supervised', 'type' => 'boolean'],
            'addigy_agent_version' => ['label_key' => 'admin/settings/sync_adapters.extra_agent_version'],
            'addigy_product_name' => ['label_key' => 'admin/settings/sync_adapters.extra_model_identifier'],
            // Mappable inventory + posture surface (parity with ABM /
            // Mosyle / Jamf extras plus Addigy-unique warranty data).
            'addigy_build_version' => ['label_key' => 'admin/settings/sync_adapters.addigy_extra_build_version'],
            'addigy_udid' => ['label_key' => 'admin/settings/sync_adapters.extra_udid'],
            'addigy_has_mdm' => ['label_key' => 'admin/settings/sync_adapters.addigy_extra_has_mdm', 'type' => 'boolean'],
            'addigy_filevault_enabled' => ['label_key' => 'admin/settings/sync_adapters.addigy_extra_filevault_enabled', 'type' => 'boolean'],
            'addigy_firewall_enabled' => ['label_key' => 'admin/settings/sync_adapters.addigy_extra_firewall_enabled', 'type' => 'boolean'],
            'addigy_total_memory_gb' => ['label_key' => 'admin/settings/sync_adapters.addigy_extra_total_memory_gb'],
            'addigy_disk_total_space_gb' => ['label_key' => 'admin/settings/sync_adapters.addigy_extra_disk_total_space_gb'],
            'addigy_disk_free_space_gb' => ['label_key' => 'admin/settings/sync_adapters.addigy_extra_disk_free_space_gb'],
            'addigy_warranty_expiration_date' => ['label_key' => 'admin/settings/sync_adapters.addigy_extra_warranty_expiration_date', 'type' => 'date'],
        ];
    }

    public function supportsGroupScoping(): bool
    {
        return true;
    }

    public function vendorGroupLabel(): string
    {
        return trans('admin/settings/sync_adapters.vendor_group_addigy_policy');
    }

    public function fetchGroups(): array
    {
        $client = $this->makeClient();

        return array_map(
            fn (array $policy) => [
                'id' => (string) ($policy['policyId'] ?? $policy['id'] ?? ''),
                'label' => (string) ($policy['name'] ?? $policy['policyId'] ?? '?'),
            ],
            $client->policies(),
        );
    }

    public function pull(): iterable
    {
        $client = $this->makeClient();

        $enrichmentEnabled = $this->appleDbEnrichmentEnabled();
        $enrichmentHandled = [];

        foreach ($client->devices(self::DESIRED_FACTS) as $device) {
            // Cache orgid off the first DeviceAudit we see so push can
            // target `/o/{orgid}/end-users/*` without the admin
            // supplying it. v2 has no "get my current org" endpoint,
            // but every DeviceAudit carries the field. The write
            // short-circuits when nothing changed so repeated pulls
            // don't thrash the config table.
            $this->cacheOrganizationId((string) ($device['orgid'] ?? ''));

            $record = $this->normalize($device);
            if ($enrichmentEnabled) {
                $enrichmentHandled = $this->enrichFromAppleDb($record, $enrichmentHandled);
            }
            yield $record;
        }
    }

    /**
     * Pass the Addigy record to the shared enrichment flow. The
     * product_name fact is Addigy's name for the AppleDB-shape
     * identifier (e.g. "MacBookPro18,3") per
     * support.addigy.com/hc/en-us/articles/5435826890259. The marketing
     * name (device_model_name) remains hardwareModel so admin-visible
     * AssetModel.name is unchanged by the enrichment opt-in.
     *
     * @param  array<string, bool>  $handled
     * @return array<string, bool>
     */
    private function enrichFromAppleDb(HostInventoryRecord $record, array $handled): array
    {
        $modelName = $record->hardwareModel;
        if (!is_string($modelName) || $modelName === '') {
            return $handled;
        }

        $productType = $record->extra['addigy_product_name'] ?? null;
        if (!is_string($productType) || $productType === '') {
            return $handled;
        }

        return $this->enrichModelFromAppleDb($modelName, $productType, null, $handled);
    }

    /**
     * Convert an Addigy v2 DeviceAudit payload into the normalized
     * record shape. `agentid` is stable per enrolled device and is
     * what we key asset_external_sources on. Everything else lives
     * in the `facts` map keyed by identifier.
     *
     * @param  array<string, mixed>  $device
     */
    private function normalize(array $device): HostInventoryRecord
    {
        $productType = self::fact($device, 'product_name');
        $modelName = self::fact($device, 'device_model_name');
        $canonical = $this->canonicalIdentityFor($productType, $modelName);
        $appleDbExtras = $this->appleDbExtrasFor($productType, $modelName);

        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: (string) Arr::get($device, 'agentid'),
            hostname: self::fact($device, 'device_name'),
            hardwareSerial: self::fact($device, 'serial_number'),
            hardwareModel: $modelName,
            manufacturer: 'Apple',
            primaryMac: self::fact($device, 'network_mac_address'),
            primaryIp: self::fact($device, 'network_ip_address'),
            os: self::fact($device, 'os_type'),
            osVersion: self::fact($device, 'os_version'),
            lastSeen: $this->parseTimestamp(self::fact($device, 'last_online_at')),
            // Addigy's "asset_tag" is the operator-assigned tag from
            // their inventory settings, distinct from the Snipe-IT one.
            assetTag: self::fact($device, 'asset_tag'),
            assignedUserEmail: self::fact($device, 'user_email'),
            vendorGroupId: ($policyId = self::fact($device, 'policy_id')) !== null ? (string) $policyId : null,
            extra: [
                ...$appleDbExtras,
                'addigy_policy_id' => self::fact($device, 'policy_id'),
                'addigy_supervised' => self::fact($device, 'is_supervised'),
                'addigy_agent_version' => self::fact($device, 'agent_version'),
                'addigy_product_name' => $productType,
                'addigy_build_version' => self::fact($device, 'build_version'),
                'addigy_udid' => self::fact($device, 'udid'),
                'addigy_has_mdm' => self::fact($device, 'has_mdm'),
                'addigy_filevault_enabled' => self::fact($device, 'filevault_enabled'),
                'addigy_firewall_enabled' => self::fact($device, 'firewall_enabled'),
                'addigy_total_memory_gb' => self::fact($device, 'total_memory_gb'),
                'addigy_disk_total_space_gb' => self::fact($device, 'disk_total_space_gb'),
                'addigy_disk_free_space_gb' => self::fact($device, 'disk_free_space_gb'),
                'addigy_warranty_expiration_date' => self::fact($device, 'warranty_expiration_date'),
            ],
            canonicalMarketingName: $canonical['canonicalMarketingName'],
            canonicalModelNumber: $canonical['canonicalModelNumber'],
        );
    }

    /**
     * Pull a single fact value out of a v2 DeviceAudit payload.
     * Returns null when the fact is missing, the server reported an
     * error_msg on it, or the value is explicitly null.
     */
    private static function fact(array $device, string $identifier): mixed
    {
        $fact = Arr::get($device, 'facts.'.$identifier);
        if (! is_array($fact)) {
            return null;
        }
        if (! empty($fact['error_msg'])) {
            return null;
        }

        return $fact['value'] ?? null;
    }

    private function parseTimestamp(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            // Addigy occasionally emits timestamps as Unix epoch
            // seconds (seen on last_online_at). Treat anything numeric
            // as epoch so Carbon::parse doesn't try to interpret it as
            // a year.
            return Carbon::createFromTimestamp((int) $value);
        }

        return Carbon::parse($value);
    }

    /**
     * Push is always available on a configured instance. Both
     * Set-Device-Name and end-user assignment use the same API key as
     * pull, so if pull works push works too.
     */
    public function canPush(): bool
    {
        return true;
    }

    /**
     * Addigy exposes no device-level freeform notes field writable via
     * the API, so composed-notes push has no target here. Returning
     * null disables the composed-notes section of the settings UI.
     */
    public function notesFieldTarget(): ?string
    {
        return null;
    }

    /**
     * Hostname is the only standard field Addigy accepts writes on
     * (via the Set Device Name MDM command). Narrowing the pushable
     * set hides push/both from the direction picker for every other
     * standard field so the admin can't opt into a push that would
     * silently drop at payload build time.
     */
    public function pushableFields(): array
    {
        return ['hostname'];
    }

    /**
     * Push Snipe-IT-authoritative fields to Addigy. Two separate
     * surfaces, each toggled independently:
     *
     *  1. Hostname -> Set Device Name MDM command, when `hostname` is
     *     push-directed in the mapping UI.
     *  2. Assigned user -> Addigy end-user device assignment, when
     *     the per-instance `push_end_user_assignment` flag is on and
     *     the asset has an assigned user with a resolvable email.
     *
     * The two don't share a payload (two different endpoints), so this
     * doesn't delegate to pushViaSinglePayload. Return true when at
     * least one surface actually fired.
     *
     * @param  array<int, string>  $changedFields
     */
    public function push(Asset $asset, array $changedFields = []): bool
    {
        $hostnameConfigured = $this->shouldPushHostname($changedFields);
        $endUserConfigured = $this->pushEndUserAssignmentEnabled() && $this->assignedUserChanged($changedFields);

        if (! $hostnameConfigured && ! $endUserConfigured) {
            Log::channel('sync-adapters')->info(
                $this->name().' push: skipped asset '.$asset->id
                .' (asset_tag='.($asset->asset_tag ?? '(none)').') - no push surface configured.',
            );

            return false;
        }

        // Direct external-source lookup. Base class pushPrologue bakes
        // in a pushDirectedFields check that assumes mapping-field
        // semantics, which end-user assignment sits outside, so we
        // read the external_source row ourselves.
        $externalSource = AssetExternalSource::query()
            ->where('asset_id', $asset->id)
            ->where('source', $this->name())
            ->first();

        if ($externalSource === null) {
            Log::channel('sync-adapters')->info(
                $this->name().' push: skipped asset '.$asset->id
                .' (asset_tag='.($asset->asset_tag ?? '(none)').') - no asset_external_sources row for this adapter instance.',
            );

            return false;
        }

        $client = $this->makeClient();
        $dispatched = false;

        if ($hostnameConfigured && $this->pushHostname($asset, $externalSource, $client)) {
            $dispatched = true;
        }

        if ($endUserConfigured && $this->pushEndUserAssignment($asset, $externalSource, $client)) {
            $dispatched = true;
        }

        return $dispatched;
    }

    private function shouldPushHostname(array $changedFields): bool
    {
        if (! in_array('hostname', $this->pushDirectedFields(), true)) {
            return false;
        }

        // Respect the pushPrologue intersection check: if the caller
        // handed us a specific changedFields set, hostname has to be
        // in it. Empty set means "push everything directed push" so
        // we still fire.
        return $changedFields === [] || in_array('hostname', $changedFields, true);
    }

    private function pushHostname(Asset $asset, AssetExternalSource $externalSource, AddigyClient $client): bool
    {
        $name = $asset->name;
        if ($name === null || $name === '') {
            return false;
        }

        if ($this->isPushDryRun()) {
            Log::channel('sync-adapters')->info(
                $this->name().' push [dry-run]: would set Addigy device '.$externalSource->external_id.' name='.$name,
            );

            return true;
        }

        $client->setDeviceName((string) $externalSource->external_id, (string) $name);

        Log::channel('sync-adapters')->info(
            $this->name().' push: set Addigy device '.$externalSource->external_id.' name='.$name,
        );

        return true;
    }

    private function pushEndUserAssignmentEnabled(): bool
    {
        return (bool) SyncAdapterConfig::get($this->instance->id, 'push_end_user_assignment');
    }

    /**
     * Snipe-IT doesn't surface "assigned user" in changedFields today
     * (it's not a mapping field). Treat an empty changedFields set as
     * "push everything configured" and let the end-user branch run.
     * When the caller sends a specific set, only run if `assigned_to`
     * is in it so a hostname-only save doesn't re-check end-user state.
     */
    private function assignedUserChanged(array $changedFields): bool
    {
        return $changedFields === [] || in_array('assigned_to', $changedFields, true);
    }

    private function pushEndUserAssignment(Asset $asset, AssetExternalSource $externalSource, AddigyClient $client): bool
    {
        $orgId = (string) (SyncAdapterConfig::get($this->instance->id, 'cached_orgid') ?? '');
        if ($orgId === '') {
            Log::channel('sync-adapters')->info(
                $this->name().' push: skipped end-user assignment for asset '.$asset->id
                .' - cached_orgid not yet captured. Run a pull first.',
            );

            return false;
        }

        if (! ($asset->assignedTo instanceof User) || empty($asset->assignedTo->email)) {
            Log::channel('sync-adapters')->info(
                $this->name().' push: skipped end-user assignment for asset '.$asset->id
                .' - no assigned user with an email on the Snipe-IT side.',
            );

            return false;
        }

        $email = (string) $asset->assignedTo->email;

        if ($this->isPushDryRun()) {
            Log::channel('sync-adapters')->info(
                $this->name().' push [dry-run]: would resolve Addigy end user by email='.$email
                .' and assign to device '.$externalSource->external_id,
            );

            return true;
        }

        $endUserId = $client->findEndUserIdByEmail($orgId, $email);
        if ($endUserId === null) {
            Log::channel('sync-adapters')->info(
                $this->name().' push: skipped end-user assignment for asset '.$asset->id
                .' - no unique Addigy end-user match for email='.$email,
            );

            return false;
        }

        $client->assignDeviceToEndUser($orgId, (string) $externalSource->external_id, $endUserId);

        Log::channel('sync-adapters')->info(
            $this->name().' push: assigned Addigy device '.$externalSource->external_id
            .' to end-user '.$endUserId.' (email='.$email.')',
        );

        return true;
    }

    private function cacheOrganizationId(string $orgId): void
    {
        if ($orgId === '') {
            return;
        }
        if (SyncAdapterConfig::get($this->instance->id, 'cached_orgid') === $orgId) {
            return;
        }
        SyncAdapterConfig::put($this->instance->id, 'cached_orgid', $orgId);
    }

    private function makeClient(): AddigyClient
    {
        return new AddigyClient(apiKey: $this->credential('api_key'));
    }
}
