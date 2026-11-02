<?php

namespace App\SyncAdapters\Intune;

use App\Models\Asset;
use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\PushableSyncAdapter;
use App\SyncAdapters\SyncAdapter;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Microsoft Intune adapter. Pulls managed devices from Microsoft Graph
 * (/v1.0/deviceManagement/managedDevices) via an OAuth 2.0
 * client-credentials app registration and normalizes them into
 * HostInventoryRecord objects. Also pushes composed notes back to
 * Intune's managed-device notes field via Graph beta, matching what
 * community integrations like Brady Widener's PowerShell script do.
 *
 * Sovereign clouds change both the Graph URL and the token endpoint
 * (US Gov uses graph.microsoft.us + login.microsoftonline.us). The
 * Base URL field on the settings page controls the Graph endpoint.
 * The login host is derived from it by swapping the second-level
 * label so a single URL config drives both.
 *
 * Non-secret credentials (tenant + client id) stay plain-text at rest
 * so admins can audit which Azure app registration a Snipe-IT instance
 * is talking to without a decrypt step. Only client_secret is encrypted.
 */
class IntuneAdapter extends PushableSyncAdapter
{
    public static function typeLabel(): string
    {
        return 'Microsoft Intune';
    }

    public static function typeSlug(): string
    {
        return 'intune';
    }

    public static function docsUrl(): ?string
    {
        return 'https://learn.microsoft.com/en-us/graph/api/resources/intune-graph-overview';
    }

    public function baseUrlPlaceholder(): ?string
    {
        return 'https://graph.microsoft.com';
    }

    public function settingsSchema(): array
    {
        return [
            [
                'key' => 'tenant_id',
                'label' => trans('admin/settings/sync_adapters.label_tenant_id'),
                'help' => trans('admin/settings/sync_adapters.intune_tenant_id_help'),
            ],
            [
                'key' => 'client_id',
                'label' => trans('admin/settings/sync_adapters.label_client_id'),
                'help' => trans('admin/settings/sync_adapters.intune_client_id_help'),
            ],
            [
                'key' => 'client_secret',
                'label' => trans('admin/settings/sync_adapters.label_client_secret'),
                'secret' => true,
                'help' => trans('admin/settings/sync_adapters.intune_client_secret_help'),
            ],
            [
                'key' => 'scope_tag_filter',
                'label' => trans('admin/settings/sync_adapters.intune_scope_tag_filter_label'),
                'required' => false,
                'help' => trans('admin/settings/sync_adapters.intune_scope_tag_filter_help'),
            ],
        ];
    }

    /**
     * Preset extras this adapter extracts from well-known Graph
     * managedDevice fields. Each key maps to an entry built in
     * normalize() from the corresponding Graph attribute. Admin-
     * defined extras (via the Custom Extras widget) merge on top via
     * the base class.
     *
     * Only fields that Graph's v1.0 LIST endpoint actually populates
     * are included here. Properties like `ethernetMacAddress`,
     * `physicalMemoryInBytes`, and the `hardwareInformation` TPM
     * fields return null on LIST (Microsoft requires per-device
     * $select to get real values) - the opt-in per-device
     * enrichment setting below fetches those separately.
     */
    public function presetExtraFields(): array
    {
        return [
            'intune_compliance_state' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_compliance_state'],
            'intune_enrolled_at' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_enrolled_at', 'type' => 'date'],
            'intune_management_agent' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_management_agent'],
            'intune_ownership' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_ownership'],
            'intune_wifi_mac' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_wifi_mac'],
            'intune_total_storage_bytes' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_total_storage_bytes'],
            'intune_free_storage_bytes' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_free_storage_bytes'],
            'intune_is_encrypted' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_is_encrypted', 'type' => 'boolean'],
            'intune_jail_broken' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_jail_broken'],
            'intune_is_supervised' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_is_supervised', 'type' => 'boolean'],
            'intune_azure_ad_device_id' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_azure_ad_device_id'],
            'intune_azure_ad_registered' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_azure_ad_registered', 'type' => 'boolean'],
            'intune_imei' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_imei'],
            'intune_meid' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_meid'],
            'intune_exchange_access_state' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_exchange_access_state'],
            // Additional v1.0 LIST-populated fields.
            'intune_user_display_name' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_user_display_name'],
            'intune_managed_device_name' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_managed_device_name'],
            'intune_partner_reported_threat_state' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_partner_reported_threat_state'],
            'intune_exchange_access_state_reason' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_exchange_access_state_reason'],
            'intune_device_category' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_device_category'],
            'intune_phone_number' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_phone_number'],
            'intune_subscriber_carrier' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_subscriber_carrier'],
            'intune_android_security_patch_level' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_android_security_patch_level'],
            'intune_enrollment_profile_name' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_enrollment_profile_name'],
            'intune_device_enrollment_type' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_device_enrollment_type'],
            'intune_management_certificate_expiration' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_management_certificate_expiration', 'type' => 'date'],
            'intune_compliance_grace_period_expiration' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_compliance_grace_period_expiration', 'type' => 'date'],
            'intune_device_registration_state' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_device_registration_state'],
            // Comma-joined roleScopeTagIds the device belongs to in
            // Intune. Admins who use scope tags for RBAC separation
            // can route this to a custom field to see which slice of
            // IT owns each asset.
            'intune_role_scope_tag_ids' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_role_scope_tag_ids'],
            // Per-device enrichment fields. Populated only when the
            // "Fetch per-device details" opt-in is on - Graph's LIST
            // endpoint returns null for these regardless of $select.
            'intune_ethernet_mac' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_ethernet_mac'],
            'intune_physical_memory_bytes' => ['label_key' => 'admin/settings/sync_adapters.intune_extra_physical_memory_bytes'],
        ];
    }

    public function supportsAdminDefinedExtras(): bool
    {
        return true;
    }

    public function perDeviceDetailsToggle(): ?array
    {
        return [
            'label' => trans('admin/settings/sync_adapters.intune_fetch_per_device_details_label'),
            'help' => trans('admin/settings/sync_adapters.intune_fetch_per_device_details_help'),
        ];
    }

    public function pull(): iterable
    {
        $graphBaseUrl = $this->url();

        $client = new IntuneClient(
            graphBaseUrl: $graphBaseUrl,
            loginBaseUrl: $this->deriveLoginBaseUrl($graphBaseUrl),
            tenantId: $this->credential('tenant_id'),
            clientId: $this->credential('client_id'),
            clientSecret: $this->credential('client_secret'),
        );

        $fetchPerDeviceDetails = $this->fetchesPerDeviceDetails();
        $scopeTagIds = $this->resolveScopeTagFilter($client);
        $scopeTagSet = is_array($scopeTagIds) ? array_flip($scopeTagIds) : null;

        foreach ($client->managedDevices() as $device) {
            // Scope-tag filtering is client-side (see #19849). Graph
            // does not expose a working server-side $filter on
            // roleScopeTagIds, and the field comes back empty on the
            // list surface even with $select, so each device needs a
            // per-device beta GET to resolve its tag membership.
            // Skipped entirely when no filter is configured.
            if ($scopeTagSet !== null && ! $this->deviceMatchesScopeTags($client, $device, $scopeTagSet)) {
                continue;
            }

            if ($fetchPerDeviceDetails) {
                $this->fillPerDeviceEnrichment($client, $device);
            }
            yield $this->normalize($device);
        }
    }

    /**
     * Resolve the device's scope-tag membership via a beta per-device
     * detail fetch and test it against the admin's filter set. Returns
     * true when at least one of the device's tags is in the admin's
     * list. On a failed detail call (4xx/5xx, timeout) we return false
     * so the device is excluded rather than silently sneaking through
     * the filter.
     *
     * @param  array<string, mixed>  $device
     * @param  array<string, int>  $scopeTagSet  Flipped id => arbitrary (for O(1) lookup)
     */
    private function deviceMatchesScopeTags(IntuneClient $client, array $device, array $scopeTagSet): bool
    {
        $deviceId = $device['id'] ?? null;
        if (! is_string($deviceId) || $deviceId === '') {
            return false;
        }

        $detail = $client->managedDeviceDetail($deviceId, ['id', 'roleScopeTagIds'], 'beta');
        if ($detail === null) {
            return false;
        }

        $deviceTagIds = $detail['roleScopeTagIds'] ?? [];
        if (! is_array($deviceTagIds)) {
            return false;
        }

        foreach ($deviceTagIds as $tagId) {
            if (is_string($tagId) && isset($scopeTagSet[$tagId])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve the admin-entered scope_tag_filter into the Graph tag ID
     * list the managedDevices filter wants. Entries can be either tag
     * displayNames (friendly) or raw IDs (short-circuit). We hit
     * roleScopeTags() once per sync, build a name -> id map, resolve
     * names, and pass through anything that looks like an id already.
     * Unresolvable names are logged and skipped so one bad entry does
     * not kill the whole pull.
     *
     * Returns null when no filter is configured so the client can skip
     * the $filter query param entirely.
     *
     * @return array<int, string>|null
     */
    private function resolveScopeTagFilter(IntuneClient $client): ?array
    {
        $raw = '';
        try {
            $raw = (string) $this->credential('scope_tag_filter');
        } catch (\Throwable) {
            // Credential not set yet. Treat as unconfigured.
        }

        $entries = array_values(array_filter(array_map('trim', explode(',', $raw))));
        if ($entries === []) {
            return null;
        }

        $tags = $client->roleScopeTags();
        $nameToId = [];
        $idSet = [];
        foreach ($tags as $tag) {
            $nameToId[strtolower($tag['displayName'])] = $tag['id'];
            $idSet[$tag['id']] = true;
        }

        $resolved = [];
        foreach ($entries as $entry) {
            $lower = strtolower($entry);
            if (isset($nameToId[$lower])) {
                $resolved[] = $nameToId[$lower];

                continue;
            }
            if (isset($idSet[$entry])) {
                $resolved[] = $entry;

                continue;
            }
            Log::channel('sync-adapters')->warning(sprintf(
                '%s: scope_tag_filter entry "%s" did not match any Intune role scope tag name or id. Skipping.',
                $this->name(),
                $entry,
            ));
        }

        return $resolved === [] ? null : array_values(array_unique($resolved));
    }

    /**
     * Per-device enrichment pass for fields Graph's LIST endpoint
     * returns null for. Mutates $device in-place with the fetched
     * ethernetMacAddress + physicalMemoryInBytes so normalize()
     * picks them up through the usual Arr::get path. One extra
     * Graph call per device - costly on large fleets, which is why
     * it's opt-in.
     *
     * @param  array<string, mixed>  &$device
     */
    private function fillPerDeviceEnrichment(IntuneClient $client, array &$device): void
    {
        $id = Arr::get($device, 'id');
        if (! is_string($id) || $id === '') {
            return;
        }

        $detail = $client->managedDeviceDetail($id, ['ethernetMacAddress', 'physicalMemoryInBytes']);
        if ($detail === null) {
            return;
        }

        $device['ethernetMacAddress'] = Arr::get($detail, 'ethernetMacAddress');
        $device['physicalMemoryInBytes'] = Arr::get($detail, 'physicalMemoryInBytes');
    }

    /**
     * Opt-in: fire a per-device Graph GET for each pulled device to
     * backfill ethernet MAC + physical memory. Microsoft's LIST
     * endpoint returns null for these, so admins who need them
     * accept the per-device cost. Default off - cheap pull wins
     * for large fleets by default.
     */
    public function fetchesPerDeviceDetails(): bool
    {
        return \App\Models\SyncAdapterConfig::get($this->instance->id, 'fetch_per_device_details') === '1';
    }

    /**
     * Derive the OAuth login host from the Graph host so sovereign
     * clouds (US Gov, China) work off a single URL config. Falls
     * back to the public Microsoft login endpoint when the base URL
     * doesn't parse cleanly.
     */
    private function deriveLoginBaseUrl(string $graphBaseUrl): string
    {
        $host = parse_url($graphBaseUrl, PHP_URL_HOST);
        if (! is_string($host)) {
            return 'https://login.microsoftonline.com';
        }

        $tld = substr($host, strrpos($host, '.') + 1);

        return match ($tld) {
            'us' => 'https://login.microsoftonline.us',
            'cn' => 'https://login.chinacloudapi.cn',
            default => 'https://login.microsoftonline.com',
        };
    }

    /**
     * Convert a Graph managedDevice payload into the normalized record
     * shape. Graph's stable id is `id` (a GUID), and we key
     * asset_external_sources on it. Ethernet MAC is preferred over
     * WiFi because it's more likely to be a hardware-inventory MAC
     * that survives OS reinstall. Empty-string MAC fields fall through
     * - Graph often returns "" rather than omitting the key for
     * devices without that interface (e.g. laptops without built-in
     * Ethernet), so a plain `??` would mistake empty for present.
     *
     * @param  array<string, mixed>  $device
     */
    private function normalize(array $device): HostInventoryRecord
    {
        $extra = [
            'intune_compliance_state' => Arr::get($device, 'complianceState'),
            'intune_enrolled_at' => Arr::get($device, 'enrolledDateTime'),
            'intune_management_agent' => Arr::get($device, 'managementAgent'),
            'intune_ownership' => Arr::get($device, 'managedDeviceOwnerType'),
            // Wi-Fi MAC lands here. Ethernet MAC + physical memory
            // return null on LIST per Microsoft's docs - populated
            // by the per-device enrichment pass instead if the admin
            // opted in. See fillPerDeviceEnrichment().
            'intune_wifi_mac' => self::blankToNull(Arr::get($device, 'wiFiMacAddress')),
            // Backfilled by fillPerDeviceEnrichment() when the opt-in
            // setting is on. Null otherwise - Graph's LIST never
            // populates these fields.
            'intune_ethernet_mac' => self::blankToNull(Arr::get($device, 'ethernetMacAddress')),
            'intune_physical_memory_bytes' => Arr::get($device, 'physicalMemoryInBytes'),
            // Hardware inventory. Graph reports storage in bytes -
            // admins who want GB can run a custom-field formula.
            'intune_total_storage_bytes' => Arr::get($device, 'totalStorageSpaceInBytes'),
            'intune_free_storage_bytes' => Arr::get($device, 'freeStorageSpaceInBytes'),
            'intune_is_encrypted' => Arr::get($device, 'isEncrypted'),
            'intune_jail_broken' => Arr::get($device, 'jailBroken'),
            'intune_is_supervised' => Arr::get($device, 'isSupervised'),
            // Azure AD identity. Useful for admins who cross-reference
            // Snipe-IT assets with Entra ID devices or conditional
            // access policies.
            'intune_azure_ad_device_id' => Arr::get($device, 'azureADDeviceId'),
            'intune_azure_ad_registered' => Arr::get($device, 'azureADRegistered'),
            // Cellular identifiers for iOS / Android devices.
            'intune_imei' => Arr::get($device, 'imei'),
            'intune_meid' => Arr::get($device, 'meid'),
            'intune_exchange_access_state' => Arr::get($device, 'exchangeAccessState'),
            // Additional v1.0 LIST-populated fields.
            'intune_user_display_name' => Arr::get($device, 'userDisplayName'),
            'intune_managed_device_name' => Arr::get($device, 'managedDeviceName'),
            'intune_partner_reported_threat_state' => Arr::get($device, 'partnerReportedThreatState'),
            'intune_exchange_access_state_reason' => Arr::get($device, 'exchangeAccessStateReason'),
            'intune_device_category' => Arr::get($device, 'deviceCategoryDisplayName'),
            'intune_phone_number' => Arr::get($device, 'phoneNumber'),
            'intune_subscriber_carrier' => Arr::get($device, 'subscriberCarrier'),
            'intune_android_security_patch_level' => Arr::get($device, 'androidSecurityPatchLevel'),
            'intune_enrollment_profile_name' => Arr::get($device, 'enrollmentProfileName'),
            'intune_device_enrollment_type' => Arr::get($device, 'deviceEnrollmentType'),
            'intune_management_certificate_expiration' => Arr::get($device, 'managementCertificateExpirationDate'),
            'intune_compliance_grace_period_expiration' => Arr::get($device, 'complianceGracePeriodExpirationDateTime'),
            'intune_device_registration_state' => Arr::get($device, 'deviceRegistrationState'),
            // Scope tags live on the device as an array of ID strings.
            // Comma-join for custom-field display convenience. See
            // scope_tag_filter in settingsSchema() for the usage hook.
            'intune_role_scope_tag_ids' => self::joinScopeTagIds(Arr::get($device, 'roleScopeTagIds')),
        ];
        $extra = $this->applyAdminExtras($extra, $device);

        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: (string) Arr::get($device, 'id'),
            hostname: Arr::get($device, 'deviceName'),
            hardwareSerial: Arr::get($device, 'serialNumber'),
            hardwareModel: Arr::get($device, 'model'),
            manufacturer: Arr::get($device, 'manufacturer'),
            primaryMac: self::blankToNull(Arr::get($device, 'ethernetMacAddress'))
                ?? self::blankToNull(Arr::get($device, 'wiFiMacAddress')),
            primaryIp: null,
            os: Arr::get($device, 'operatingSystem'),
            osVersion: Arr::get($device, 'osVersion'),
            lastSeen: $this->parseTimestamp(Arr::get($device, 'lastSyncDateTime')),
            assignedUserEmail: Arr::get($device, 'userPrincipalName')
                ?? Arr::get($device, 'emailAddress'),
            extra: $extra,
        );
    }

    /**
     * Graph often returns "" rather than null for interface MACs when
     * the device doesn't have that interface. Fold both shapes to
     * null so downstream null-guards (and the primaryMac Ethernet-
     * then-WiFi fallback) work uniformly.
     */
    private static function blankToNull(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        return $value;
    }

    /**
     * Flatten Graph's roleScopeTagIds array into a comma-joined string
     * for the extras bag so admins can route it to a text custom field
     * without juggling array types. Returns null when the device has
     * no scope tags.
     */
    private static function joinScopeTagIds(mixed $value): ?string
    {
        if (! is_array($value) || $value === []) {
            return null;
        }

        $parts = array_values(array_filter(array_map(
            fn ($v) => is_scalar($v) ? (string) $v : null,
            $value,
        )));

        return $parts === [] ? null : implode(',', $parts);
    }

    private function parseTimestamp(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value);
    }

    /**
     * Push always available for a configured Intune instance. The
     * Graph beta endpoint requires the app registration to have the
     * DeviceManagementManagedDevices.ReadWrite.All permission
     * (admin consent required, same as the read scope but broader).
     */
    public function canPush(): bool
    {
        return true;
    }

    /**
     * Intune managed devices carry a freeform `notes` field on the
     * Graph beta endpoint. That's the field Brady Widener's community
     * PowerShell script targets and is the most-asked-for Intune
     * push shape.
     */
    public function notesFieldTarget(): ?string
    {
        return 'notes';
    }

    /**
     * Push composed notes to Intune's managed-device notes field.
     * asset_tag isn't first-class-writable on Intune (Graph v1.0
     * doesn't expose it and the beta shape varies), so v1 supports
     * composed notes only. Admins who want asset_tag on Intune
     * embed it in the notes template.
     *
     * @param  array<int, string>  $changedFields
     */
    public function push(Asset $asset, array $changedFields = []): bool
    {
        $externalSource = $this->pushPrologue($asset, $changedFields);
        if ($externalSource === null) {
            return false;
        }

        $composedNotes = $this->composeNotesForPush($asset);
        if ($composedNotes === null) {
            return false;
        }

        $payload = [$composedNotes['target'] => $composedNotes['value']];

        if ($this->isPushDryRun()) {
            Log::channel('sync-adapters')->info(sprintf(
                '%s push [dry-run]: would PATCH Intune managed device %s with %s',
                $this->name(),
                $externalSource->external_id,
                json_encode($payload, JSON_UNESCAPED_SLASHES),
            ));

            return true;
        }

        $graphBaseUrl = $this->url();
        $client = new IntuneClient(
            graphBaseUrl: $graphBaseUrl,
            loginBaseUrl: $this->deriveLoginBaseUrl($graphBaseUrl),
            tenantId: $this->credential('tenant_id'),
            clientId: $this->credential('client_id'),
            clientSecret: $this->credential('client_secret'),
        );
        $client->updateManagedDevice($externalSource->external_id, $payload);

        Log::channel('sync-adapters')->info(sprintf(
            '%s push: updated Intune managed device %s (%s)',
            $this->name(),
            $externalSource->external_id,
            $composedNotes['target'],
        ));

        return true;
    }
}
