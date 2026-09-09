<?php

namespace App\SyncAdapters\AppleBusinessManager;

use App\Models\SyncAdapterConfig;
use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\Support\ConfigurableAdapter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * Apple Business Manager (ABM) / Apple School Manager (ASM) adapter.
 * Pulls the master device roster from Apple's org-devices API using
 * JWT client-assertion auth (ES256, signed with an EC P-256 private
 * key downloaded from Apple's admin console).
 *
 * ABM is a purchase-registration portal, not a runtime inventory.
 * Devices show up here as soon as they're bought via an
 * Apple-integrated reseller, before enrollment, before OS install.
 * That means hostname, OS version, last-seen, and MAC address are
 * null in every record: those become populated later once the device
 * enrolls in an MDM (Jamf, Kandji, Mosyle, Intune, etc). The point of
 * the ABM adapter is to pre-populate Snipe-IT with the authoritative
 * "we own this serial" list before an MDM enrolls it, or to pick up
 * devices the MDM never sees (spares, decommissioned, in-transit).
 *
 * Groups: ABM has Organizations/Sites concepts, but the device record
 * doesn't expose an org-scoped foreign key today. Group scoping is
 * off. If admins want per-Snipe-IT-company routing, they run one
 * adapter instance per company for now.
 *
 * Push: not implemented. Apple's write endpoints (device metadata,
 * MDM server assignment) are outside Snipe-IT's authoritative scope.
 */
class AppleBusinessManagerAdapter extends ConfigurableAdapter
{
    public static function typeLabel(): string
    {
        return 'Apple Business Manager';
    }

    public function baseUrlPlaceholder(): ?string
    {
        // ABM/ASM base URLs are host-fixed per mode (business or
        // school), so we don't expose a URL field. The mode selection
        // in the credential schema controls host + scope.
        return null;
    }

    public function credentialSchema(): array
    {
        return [
            [
                'key' => 'mode',
                'label' => 'Portal',
                'help' => trans('admin/settings/sync_adapters.abm_mode_help'),
            ],
            [
                'key' => 'client_id',
                'label' => 'Client ID',
                'help' => trans('admin/settings/sync_adapters.abm_client_id_help'),
            ],
            [
                'key' => 'key_id',
                'label' => 'Key ID',
                'help' => trans('admin/settings/sync_adapters.abm_key_id_help'),
            ],
            [
                'key' => 'private_key',
                'label' => 'Private Key (PEM)',
                'secret' => true,
                'help' => trans('admin/settings/sync_adapters.abm_private_key_help'),
            ],
            [
                'key' => 'product_family_filter',
                'label' => 'Product Families',
                'required' => false,
                'help' => trans('admin/settings/sync_adapters.abm_product_family_filter_help'),
            ],
        ];
    }

    public function extraFields(): array
    {
        return [
            'abm_product_family' => ['label_key' => 'admin/settings/sync_adapters.extra_product_family'],
            'abm_model_marketing_name' => ['label_key' => 'admin/settings/sync_adapters.extra_model_marketing_name'],
            'abm_product_type' => ['label_key' => 'admin/settings/sync_adapters.extra_product_type'],
            'abm_part_number' => ['label_key' => 'admin/settings/sync_adapters.extra_part_number'],
            'abm_color' => ['label_key' => 'admin/settings/sync_adapters.extra_color'],
            'abm_order_number' => ['label_key' => 'admin/settings/sync_adapters.extra_order_number'],
            'abm_order_date' => ['label_key' => 'admin/settings/sync_adapters.extra_order_date'],
            'abm_purchase_source_type' => ['label_key' => 'admin/settings/sync_adapters.extra_purchase_source_type'],
            'abm_purchase_source_id' => ['label_key' => 'admin/settings/sync_adapters.extra_purchase_source_id'],
            'abm_mdm_server' => ['label_key' => 'admin/settings/sync_adapters.extra_mdm_server'],
            'abm_applecare_agreement_number' => ['label_key' => 'admin/settings/sync_adapters.extra_applecare_agreement_number'],
            'abm_applecare_status' => ['label_key' => 'admin/settings/sync_adapters.extra_applecare_status'],
            'abm_applecare_payment_type' => ['label_key' => 'admin/settings/sync_adapters.extra_applecare_payment_type'],
            'abm_applecare_start_date' => ['label_key' => 'admin/settings/sync_adapters.extra_applecare_start_date'],
            'abm_applecare_end_date' => ['label_key' => 'admin/settings/sync_adapters.extra_applecare_end_date'],
            'abm_applecare_description' => ['label_key' => 'admin/settings/sync_adapters.extra_applecare_description'],
            'abm_applecare_is_canceled' => ['label_key' => 'admin/settings/sync_adapters.extra_applecare_is_canceled', 'type' => 'boolean'],
            'abm_applecare_is_renewable' => ['label_key' => 'admin/settings/sync_adapters.extra_applecare_is_renewable', 'type' => 'boolean'],
        ];
    }

    /**
     * Overrides the base saveConfig to give the product-family filter
     * clear-on-blank semantics. Base persistCredentialsFromSchema()
     * intentionally preserves blank values so admins can update the
     * URL without re-entering every secret, but the filter is a
     * different kind of setting: blanking it must switch back to
     * "sync every family," not preserve the previous list.
     */
    public function saveConfig(Request $request): void
    {
        parent::saveConfig($request);

        SyncAdapterConfig::put(
            $this->instance->id,
            'product_family_filter',
            (string) $request->input($this->instance->slug.'_product_family_filter', ''),
        );
    }

    public function pull(): iterable
    {
        $client = new AppleBusinessManagerClient(
            clientId: $this->credential('client_id'),
            keyId: $this->credential('key_id'),
            privateKeyPem: $this->credential('private_key'),
            mode: $this->resolvedMode(),
        );

        // Build the device-to-MDM-server map up front so normalize()
        // can attach each device's assigned server name without a
        // per-device API call. Best-effort: failures leave the map
        // empty and every asset just gets a null abm_mdm_server.
        $deviceToServer = [];
        try {
            $deviceToServer = $client->deviceToMdmServerMap();
        } catch (\Throwable $e) {
            \Log::channel('sync-adapters')->warning(sprintf(
                '%s mdm-server map fetch failed: %s',
                $this->name(),
                $e->getMessage(),
            ));
        }

        // Only fetch AppleCare coverage per device when at least one
        // applecare extra field is mapped. Fetching AppleCare adds an
        // API call per device, so admins who don't care about
        // warranty tracking skip the cost entirely.
        $enrichWithAppleCare = $this->hasMappedAppleCareFields();

        // Allowed product families (lower-cased for comparison).
        // Empty means "all families".
        $allowedFamilies = $this->allowedProductFamilies();

        foreach ($client->devices() as $device) {
            $family = strtolower((string) ($device['attributes']['productFamily'] ?? ''));
            if ($allowedFamilies !== [] && ! in_array($family, $allowedFamilies, true)) {
                continue;
            }
            $record = $this->normalize($device, $deviceToServer);
            if ($enrichWithAppleCare) {
                $record = $this->enrichRecordWithAppleCare($record, $client);
            }
            yield $record;
        }
    }

    /**
     * Parse the admin-configured product-family filter (comma-
     * separated list) into a lower-cased array. Empty / unset
     * returns an empty array, which pull() reads as "allow every
     * family." Unknown family names still flow through, since the vendor
     * naming may evolve, and we'd rather let admins opt into a new
     * family without waiting for a Snipe-IT release than block them.
     *
     * @return array<int, string>
     */
    private function allowedProductFamilies(): array
    {
        try {
            $raw = $this->credential('product_family_filter');
        } catch (\Throwable) {
            return [];
        }

        if ($raw === '') {
            return [];
        }

        $parts = preg_split('/[,\s]+/', strtolower($raw)) ?: [];

        return array_values(array_filter(array_map('trim', $parts), fn ($p) => $p !== ''));
    }

    /**
     * Whether at least one AppleCare-shaped extra has a non-skip
     * mapping stored. Gates the per-device AppleCare fetch in pull()
     * so admins who don't map any AppleCare field don't pay for the
     * enrichment.
     */
    private function hasMappedAppleCareFields(): bool
    {
        foreach (array_keys($this->extraFields()) as $key) {
            if (! str_starts_with($key, 'abm_applecare_')) {
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
     * Fetch AppleCare coverage for one asset, pick the most relevant
     * plan, and merge its fields into the record's extras. On empty
     * or failed responses we just return the un-enriched record so
     * the sync doesn't stall on transient AppleCare-endpoint issues.
     */
    private function enrichRecordWithAppleCare(HostInventoryRecord $record, AppleBusinessManagerClient $client): HostInventoryRecord
    {
        try {
            $coverages = $client->deviceAppleCareCoverage($record->sourceId);
        } catch (\Throwable $e) {
            \Log::channel('sync-adapters')->warning(sprintf(
                '%s applecare fetch failed for device %s: %s',
                $this->name(),
                $record->sourceId,
                $e->getMessage(),
            ));

            return $record;
        }

        $best = self::pickBestCoverage($coverages);
        if ($best === null) {
            return $record;
        }

        $extra = $record->extra;
        $extra['abm_applecare_agreement_number'] = $best['agreementNumber'] ?? null;
        $extra['abm_applecare_status'] = self::titleCase($best['status'] ?? null);
        $extra['abm_applecare_payment_type'] = self::titleCase($best['paymentType'] ?? null);
        $extra['abm_applecare_start_date'] = self::parseOrderDate($best['startDateTime'] ?? null);
        $extra['abm_applecare_end_date'] = self::parseOrderDate($best['endDateTime'] ?? null);
        $extra['abm_applecare_description'] = $best['description'] ?? null;
        $extra['abm_applecare_is_canceled'] = $best['isCanceled'] ?? null;
        $extra['abm_applecare_is_renewable'] = $best['isRenewable'] ?? null;

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
     * Pick the most relevant AppleCare coverage plan for a device.
     * A device can carry multiple overlapping plans (renewals,
     * add-ons, prior canceled ones). Ranking mirrors axm2snipe's
     * approach so an org migrating from that tool sees the same
     * coverage on each asset:
     *   1. ACTIVE status beats INACTIVE
     *   2. PAID_UP_FRONT payment type beats subscription / none
     *   3. Later endDateTime wins the final tiebreaker
     *
     * Returns the winning attributes array, or null when the list is
     * empty / every entry lacks attributes.
     *
     * @param  array<int, array<string, mixed>>  $coverages
     * @return array<string, mixed>|null
     */
    private static function pickBestCoverage(array $coverages): ?array
    {
        $best = null;
        foreach ($coverages as $coverage) {
            $attrs = $coverage['attributes'] ?? null;
            if (! is_array($attrs)) {
                continue;
            }
            if ($best === null) {
                $best = $attrs;

                continue;
            }
            if (self::coverageIsBetter($attrs, $best)) {
                $best = $attrs;
            }
        }

        return $best;
    }

    /**
     * Ordering predicate for two AppleCare coverage attribute maps.
     * See pickBestCoverage() for the ranking rules.
     *
     * @param  array<string, mixed>  $candidate
     * @param  array<string, mixed>  $current
     */
    private static function coverageIsBetter(array $candidate, array $current): bool
    {
        $candidateActive = ($candidate['status'] ?? '') === 'ACTIVE';
        $currentActive = ($current['status'] ?? '') === 'ACTIVE';
        if ($candidateActive !== $currentActive) {
            return $candidateActive;
        }

        $candidatePaid = ($candidate['paymentType'] ?? '') === 'PAID_UP_FRONT';
        $currentPaid = ($current['paymentType'] ?? '') === 'PAID_UP_FRONT';
        if ($candidatePaid !== $currentPaid) {
            return $candidatePaid;
        }

        $candidateEnd = $candidate['endDateTime'] ?? null;
        $currentEnd = $current['endDateTime'] ?? null;
        if (! is_string($candidateEnd) || ! is_string($currentEnd)) {
            return false;
        }

        return Carbon::parse($candidateEnd)->greaterThan(Carbon::parse($currentEnd));
    }

    /**
     * Convert an ABM device row into the normalized record shape.
     * ABM's device `id` is a stable GUID and is what we key
     * asset_external_sources on. Manufacturer is hard-coded to
     * "Apple" since every ABM device is by definition Apple hardware
     * and the payload doesn't carry the string.
     *
     * @param  array<string, mixed>  $device
     * @param  array<string, string>  $deviceToServer
     */
    private function normalize(array $device, array $deviceToServer): HostInventoryRecord
    {
        $attrs = $device['attributes'] ?? [];
        $id = (string) ($device['id'] ?? '');

        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: $id,
            // ABM is pre-provisioning, so hostname doesn't exist here.
            // Downstream shell-asset creation names the row after the
            // synthetic sourceKey + sourceId until admins rename it
            // or an MDM adapter joins in and provides a real hostname.
            hostname: null,
            hardwareSerial: Arr::get($attrs, 'serialNumber'),
            // ABM provides three model-adjacent strings, exposed as
            // extras below so admins can pick which one flows to
            // native:model via the mapping UI:
            //   - deviceModel: marketing name ("MacBook Pro
            //     (14-inch, M1 Pro/Max, 2021)"), which matches Fleet's
            //     hardware_marketing_name for cross-adapter
            //     convergence
            //   - productType: hardware identifier ("MacBookPro18,3")
            //   - partNumber: Apple's SKU ("MK1E3LL/A")
            // Default hardwareModel is partNumber because it's
            // always populated and unique per SKU. Admins mapping
            // abm_marketing_name to native:model gets the friendly
            // Fleet-compatible shape instead.
            hardwareModel: Arr::get($attrs, 'partNumber'),
            manufacturer: 'Apple',
            primaryMac: null,
            primaryIp: null,
            os: null,
            osVersion: null,
            lastSeen: null,
            assignedUserEmail: null,
            assignedUserName: null,
            vendorGroupId: null,
            extra: [
                'abm_product_family' => Arr::get($attrs, 'productFamily'),
                'abm_model_marketing_name' => Arr::get($attrs, 'deviceModel'),
                'abm_product_type' => Arr::get($attrs, 'productType'),
                'abm_part_number' => Arr::get($attrs, 'partNumber'),
                'abm_color' => self::titleCase(Arr::get($attrs, 'color')),
                'abm_order_number' => Arr::get($attrs, 'orderNumber'),
                'abm_order_date' => self::parseOrderDate(Arr::get($attrs, 'orderDateTime')),
                'abm_purchase_source_type' => Arr::get($attrs, 'purchaseSourceType'),
                'abm_purchase_source_id' => Arr::get($attrs, 'purchaseSourceId'),
                'abm_mdm_server' => $deviceToServer[$id] ?? null,
            ],
        );
    }

    /**
     * Which mode we're operating in: 'business' or 'school'. Stored
     * plain-text as a credential value so credentialSchema() renders
     * a normal text input. Anything other than 'school' resolves to
     * 'business' (the safer default that keeps admins from typoing
     * their way into the wrong host).
     */
    private function resolvedMode(): string
    {
        try {
            $stored = strtolower(trim($this->credential('mode')));
        } catch (\Throwable) {
            return 'business';
        }

        return $stored === 'school' ? 'school' : 'business';
    }

    /**
     * Title-case a color string ("SPACEBLACK" -> "Spaceblack") so
     * downstream custom fields aren't stuck with the vendor's
     * upper-case constants. Passes through null unchanged so
     * empty-guards on custom-field writes still fire.
     */
    private static function titleCase(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        return ucwords(strtolower($value));
    }

    /**
     * ABM emits orderDateTime as ISO 8601 with fractional seconds and
     * a trailing Z. Format to YYYY-MM-DD for admins who route this
     * to a date-typed custom field.
     */
    private static function parseOrderDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
