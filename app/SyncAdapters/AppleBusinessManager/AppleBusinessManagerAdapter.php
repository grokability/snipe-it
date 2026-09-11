<?php

namespace App\SyncAdapters\AppleBusinessManager;

use App\Models\AssetModel;
use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\Support\ConfigurableAdapter;
use Carbon\Carbon;
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
    /**
     * Apple's fixed productFamily enum -> friendly display name.
     * Drives the product-family filter's option list.
     *
     * @var array<string, string>
     */
    private const PRODUCT_FAMILIES = [
        'Mac' => 'Mac',
        'iPhone' => 'iPhone',
        'iPad' => 'iPad',
        'AppleTV' => 'Apple TV',
        'Watch' => 'Apple Watch',
        'Vision' => 'Apple Vision',
    ];

    /**
     * Per-category-selector config keys -> friendly display name.
     * Distinct from PRODUCT_FAMILIES because Mac's productFamily
     * collapses laptops and desktops into one bucket, but admins
     * usually want to route them to different Snipe-IT categories
     * ("Laptops" vs "Desktops"), so the "Mac" family gets split
     * into two selectors driven by deviceModel prefix.
     * categoryConfigKeyForFamily() encodes the mapping in the other
     * direction.
     *
     * @var array<string, string>
     */
    private const CATEGORY_SELECTORS = [
        'category_id_mac_laptop' => 'Mac Laptop',
        'category_id_mac_desktop' => 'Mac Desktop',
        'category_id_iphone' => 'iPhone',
        'category_id_ipad' => 'iPad',
        'category_id_appletv' => 'Apple TV',
        'category_id_watch' => 'Apple Watch',
        'category_id_vision' => 'Apple Vision',
    ];

    public static function typeLabel(): string
    {
        return 'Apple Business Manager';
    }

    /**
     * ABM/ASM base URLs are host-fixed per mode (business or school),
     * so the admin never types a URL. Mode selection in the
     * credential schema controls host + scope inside
     * AppleBusinessManagerClient::apiBaseUrl.
     */
    public function usesConfigurableUrl(): bool
    {
        return false;
    }

    public function credentialSchema(): array
    {
        $schema = [
            [
                'key' => 'mode',
                'label' => 'Portal',
                'type' => 'select',
                'options' => [
                    'business' => 'Apple Business Manager',
                    'school' => 'Apple School Manager',
                ],
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
                'type' => 'textarea',
                'secret' => true,
                'help' => trans('admin/settings/sync_adapters.abm_private_key_help'),
                'placeholder' => "-----BEGIN PRIVATE KEY-----\n1234567890\n-----END PRIVATE KEY-----",
            ],
            [
                'key' => 'product_family_filter',
                'label' => 'Product Families',
                'type' => 'multiselect',
                'options' => self::PRODUCT_FAMILIES,
                'default' => array_keys(self::PRODUCT_FAMILIES),
                'required' => false,
                'help' => trans('admin/settings/sync_adapters.abm_product_family_filter_help'),
            ],
            [
                'key' => 'pull_model_images',
                'label' => 'Pull model images from appledb.dev',
                'type' => 'checkbox',
                'required' => false,
                'help' => trans('admin/settings/sync_adapters.abm_pull_model_images_help'),
            ],
        ];

        // Per-family category overrides. Apple's productFamily is a
        // fixed enum, so admins can route iPads to "Tablets", Watches
        // to "Wearables", etc. Mac is split into laptop + desktop
        // because Apple lumps them together and admins usually want
        // them in separate Snipe-IT categories. Empty selection falls
        // back to default_category_id. Label + help share one
        // :family-templated translation each so localizers translate
        // the strings once rather than seven times.
        foreach (self::CATEGORY_SELECTORS as $key => $displayName) {
            $schema[] = [
                'key' => $key,
                'label' => trans('admin/settings/sync_adapters.abm_category_family_label', ['family' => $displayName]),
                'type' => 'category',
                'required' => false,
                'help' => trans('admin/settings/sync_adapters.abm_category_family_help', ['family' => $displayName]),
            ];
        }

        return $schema;
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
     * Route each record to a per-productFamily category when the
     * admin configured one. Apple's productFamily is a fixed
     * six-value enum (Mac, iPhone, iPad, AppleTV, Watch, Vision), so
     * the mapping is exhaustive by design. Any family the admin
     * hasn't overridden falls through to defaultCategoryId() via
     * the framework's null-return convention.
     */
    public function categoryIdForRecord(HostInventoryRecord $record): ?int
    {
        $configKey = self::categoryConfigKeyForRecord($record);
        if ($configKey === null) {
            return null;
        }

        try {
            $stored = $this->credential($configKey);
        } catch (\Throwable) {
            return null;
        }

        return $stored === '' ? null : (int) $stored;
    }

    /**
     * Which per-family credential key applies to this record. Mac is
     * split into laptop / desktop based on the marketing name prefix:
     * "MacBook*" is a laptop, anything starting with iMac, Mac mini,
     * Mac Studio, or Mac Pro is a desktop. Marketing name is used
     * because productType lost its form-factor tag on Apple Silicon
     * (M-series desktops all report bare "Mac14,x"/"Mac15,x" with no
     * iMac/Macmini/MacPro prefix).
     *
     * Returns null when the family is unrecognized OR for Macs with
     * an unparseable marketing name, so the framework falls back to
     * defaultCategoryId() rather than saving under an arbitrary
     * bucket.
     */
    private static function categoryConfigKeyForRecord(HostInventoryRecord $record): ?string
    {
        $family = $record->extra['abm_product_family'] ?? null;
        if (! is_string($family)) {
            return null;
        }

        if ($family === 'Mac') {
            $deviceModel = $record->extra['abm_model_marketing_name'] ?? '';
            if (! is_string($deviceModel) || $deviceModel === '') {
                return null;
            }
            if (str_starts_with($deviceModel, 'MacBook')) {
                return 'category_id_mac_laptop';
            }
            foreach (['iMac', 'Mac mini', 'Mac Studio', 'Mac Pro'] as $desktopPrefix) {
                if (str_starts_with($deviceModel, $desktopPrefix)) {
                    return 'category_id_mac_desktop';
                }
            }

            return null;
        }

        return match ($family) {
            'iPhone' => 'category_id_iphone',
            'iPad' => 'category_id_ipad',
            'AppleTV' => 'category_id_appletv',
            'Watch' => 'category_id_watch',
            'Vision' => 'category_id_vision',
            default => null,
        };
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

        // Model-image enrichment fetches from appledb.dev, so it's
        // opt-in and off by default. Per-pull cache keyed by
        // hardwareModel avoids re-fetching the same image when a
        // tenant has many devices of the same model.
        $pullImages = $this->shouldPullModelImages();
        $imagesHandled = [];

        foreach ($client->devices() as $device) {
            $family = strtolower((string) ($device['attributes']['productFamily'] ?? ''));
            if ($allowedFamilies !== [] && ! in_array($family, $allowedFamilies, true)) {
                continue;
            }
            $record = $this->normalize($device, $deviceToServer);
            if ($enrichWithAppleCare) {
                $record = $this->enrichRecordWithAppleCare($record, $client);
            }
            if ($pullImages) {
                $imagesHandled = $this->ensureModelImage($record, $imagesHandled);
            }
            yield $record;
        }
    }

    /**
     * When the "Pull model images" toggle is on, populate the
     * AssetModel image for the record's hardwareModel from
     * appledb.dev. Idempotent and safe to call for every record:
     *
     *   - If no productType is on the record, skip (nothing to key
     *     appledb.dev on).
     *   - If the model exists AND it already carries an image, skip
     *     (respect admin curation). The category's image is a display
     *     fallback in AssetModel::getImageUrl, not a signal to skip:
     *     per-model images still populate over a category icon so
     *     admins get Apple's marketing shots per SKU.
     *   - Otherwise fetch the image, write it to storage, and set
     *     model.image. The framework's later resolveModelIdByName
     *     find the model we (or a prior sync) created.
     *
     * Cache is keyed by hardwareModel so the same model in a 500-
     * device pull triggers one fetch, not 500.
     *
     * @param  array<string, bool>  $imagesHandled  modelName => true. Any key present means "already tried this model in this pull, skip".
     * @return array<string, bool> Updated cache with this record's modelName marked.
     */
    private function ensureModelImage(HostInventoryRecord $record, array $imagesHandled): array
    {
        // Whichever field the admin routed to native:model becomes
        // the AssetModel's name after sync. Mirror that pick here so
        // our image lookup targets the same model row the framework
        // will create or update.
        $modelName = $this->mappingFor('abm_model_marketing_name') === 'native:model'
            ? ($record->extra['abm_model_marketing_name'] ?? $record->hardwareModel)
            : $record->hardwareModel;

        if ($modelName === null || $modelName === '') {
            \Log::channel('sync-adapters')->info(sprintf(
                '%s image skip for device %s: no model name resolvable (hardwareModel and abm_model_marketing_name both blank)',
                $this->name(),
                $record->sourceId,
            ));

            return $imagesHandled;
        }

        if (array_key_exists($modelName, $imagesHandled)) {
            return $imagesHandled;
        }
        $imagesHandled[$modelName] = true;

        $productType = $record->extra['abm_product_type'] ?? null;
        if (! is_string($productType) || $productType === '') {
            \Log::channel('sync-adapters')->info(sprintf(
                '%s image skip for model "%s": ABM did not return a productType, nothing to look up on appledb.dev',
                $this->name(),
                $modelName,
            ));

            return $imagesHandled;
        }

        $existing = AssetModel::query()
            ->where('name', $modelName)
            ->first();

        if ($existing !== null && ! empty($existing->image)) {
            \Log::channel('sync-adapters')->info(sprintf(
                '%s image skip for model "%s": model already has an image (%s)',
                $this->name(),
                $modelName,
                $existing->image,
            ));

            return $imagesHandled;
        }

        $color = $record->extra['abm_color'] ?? null;
        $filename = AppleDBImageFetcher::fetch($productType, is_string($color) ? $color : null);
        if ($filename === null) {
            \Log::channel('sync-adapters')->info(sprintf(
                '%s image skip for model "%s" (productType=%s): appledb.dev returned no usable image',
                $this->name(),
                $modelName,
                $productType,
            ));

            return $imagesHandled;
        }

        if ($existing === null) {
            // First-pass timing: the framework hasn't created the
            // AssetModel row yet (SyncHostFromAdapter builds it after
            // pull() yields), so there's nothing to update. The image
            // is already downloaded and cached under public/uploads/
            // models/ by the fetcher, so the next sync will find the
            // model row and only need to set model.image (no
            // re-download).
            \Log::channel('sync-adapters')->info(sprintf(
                '%s image staged for model "%s" (productType=%s, file=%s): AssetModel row not yet created, image will be assigned on the next sync',
                $this->name(),
                $modelName,
                $productType,
                $filename,
            ));

            return $imagesHandled;
        }

        $existing->image = $filename;
        $existing->save();

        \Log::channel('sync-adapters')->info(sprintf(
            '%s image assigned to model "%s" (productType=%s, file=%s)',
            $this->name(),
            $modelName,
            $productType,
            $filename,
        ));

        return $imagesHandled;
    }

    /**
     * Read the "Pull model images" checkbox. Persists as '1' or '0'
     * via the base class's schema handler, so a plain string compare
     * is enough here.
     */
    private function shouldPullModelImages(): bool
    {
        try {
            return $this->credential('pull_model_images') === '1';
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Parse the admin-configured product-family multiselect into a
     * lower-cased array. Empty / unset returns an empty array, which
     * pull() reads as "allow every family." Persisted as JSON by
     * the base class's multiselect handler.
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

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_map('strtolower', array_filter($decoded, 'is_string')));
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
