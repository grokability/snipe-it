<?php

namespace App\SyncAdapters\AppleBusinessManager;

use App\Models\AssetModel;
use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\SyncAdapter;
use App\SyncAdapters\Traits\UsesAppleDbEnrichment;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

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
class AppleBusinessManagerAdapter extends SyncAdapter
{
    use UsesAppleDbEnrichment;

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

    public static function typeSlug(): string
    {
        return 'abm';
    }

    public static function docsUrl(): ?string
    {
        return 'https://developer.apple.com/documentation/apple-school-and-business-manager-api';
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

    public function settingsSchema(): array
    {
        $schema = [
            [
                'key' => 'mode',
                'label' => trans('admin/settings/sync_adapters.abm_label_portal'),
                'type' => 'select',
                'options' => [
                    'business' => trans('admin/settings/sync_adapters.abm_option_mode_business'),
                    'school' => trans('admin/settings/sync_adapters.abm_option_mode_school'),
                ],
                'help' => trans('admin/settings/sync_adapters.abm_mode_help'),
            ],
            [
                'key' => 'client_id',
                'label' => trans('admin/settings/sync_adapters.label_client_id'),
                'help' => trans('admin/settings/sync_adapters.abm_client_id_help'),
            ],
            [
                'key' => 'key_id',
                'label' => trans('admin/settings/sync_adapters.abm_label_key_id'),
                'help' => trans('admin/settings/sync_adapters.abm_key_id_help'),
            ],
            [
                'key' => 'private_key',
                'label' => trans('admin/settings/sync_adapters.abm_label_private_key'),
                'type' => 'textarea',
                'secret' => true,
                'help' => trans('admin/settings/sync_adapters.abm_private_key_help'),
                'placeholder' => "-----BEGIN PRIVATE KEY-----\n1234567890\n-----END PRIVATE KEY-----",
            ],
            [
                'key' => 'product_family_filter',
                'label' => trans('admin/settings/sync_adapters.abm_label_product_families'),
                'type' => 'multiselect',
                'options' => self::PRODUCT_FAMILIES,
                'default' => array_keys(self::PRODUCT_FAMILIES),
                'required' => false,
                'help' => trans('admin/settings/sync_adapters.abm_product_family_filter_help'),
            ],
            $this->appleDbSettingsField(),
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
                'section' => 'categories',
                'help' => trans('admin/settings/sync_adapters.abm_category_family_help', ['family' => $displayName]),
            ];
        }

        return $schema;
    }

    /**
     * Section titles for the credential schema. The category
     * selectors are the only grouped block here. Everything else
     * (auth + toggles) stays flat above them because there are only
     * a handful of top-level fields.
     */
    public function settingsSections(): array
    {
        return [
            'categories' => [
                'title' => trans('admin/settings/sync_adapters.abm_section_categories_title'),
            ],
        ];
    }

    /**
     * Parse the private key at save time so a malformed paste
     * (stripped dash, wrong key type, etc) fails now instead
     * of at the next pull with an OpenSSL error.
     */
    public function validationRules(): array
    {
        $rules = parent::validationRules();
        $slug = $this->instance->slug;
        $key = $slug.'_private_key';

        $rules[$key] = array_merge($rules[$key] ?? ['required', 'string'], [
            function (string $attribute, mixed $value, \Closure $fail): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                $parsed = @openssl_pkey_get_private($value);
                if ($parsed === false) {
                    $fail(trans('admin/settings/sync_adapters.abm_private_key_invalid'));

                    return;
                }

                $details = openssl_pkey_get_details($parsed);
                $type = $details['type'] ?? null;
                // OpenSSL and NIST spell the P-256 curve differently.
                $curve = $details['ec']['curve_name'] ?? null;
                $isP256Ec = $type === OPENSSL_KEYTYPE_EC && in_array($curve, ['prime256v1', 'secp256r1'], true);
                if (! is_array($details) || ! $isP256Ec) {
                    $fail(trans('admin/settings/sync_adapters.abm_private_key_unsupported'));
                }
            },
        ]);

        return $rules;
    }

    public function extraFields(): array
    {
        return [
            ...$this->appleDbExtraFieldsDeclaration(),
            'abm_product_family' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_product_family'],
            'abm_model_marketing_name' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_model_marketing_name'],
            'abm_product_type' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_product_type'],
            'abm_part_number' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_part_number'],
            'abm_color' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_color'],
            'abm_order_number' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_order_number'],
            'abm_order_date' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_order_date', 'type' => 'date'],
            'abm_purchase_source_type' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_purchase_source_type'],
            'abm_purchase_source_id' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_purchase_source_id'],
            'abm_mdm_server' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_mdm_server'],
            'abm_applecare_agreement_number' => ['label_key' => 'admin/settings/sync_adapters.extra_applecare_agreement_number'],
            'abm_applecare_status' => ['label_key' => 'admin/settings/sync_adapters.extra_applecare_status'],
            'abm_applecare_payment_type' => ['label_key' => 'admin/settings/sync_adapters.extra_applecare_payment_type'],
            'abm_applecare_start_date' => ['label_key' => 'admin/settings/sync_adapters.extra_applecare_start_date', 'type' => 'date'],
            'abm_applecare_end_date' => ['label_key' => 'admin/settings/sync_adapters.extra_applecare_end_date', 'type' => 'date'],
            'abm_applecare_description' => ['label_key' => 'admin/settings/sync_adapters.extra_applecare_description'],
            'abm_applecare_is_canceled' => ['label_key' => 'admin/settings/sync_adapters.extra_applecare_is_canceled', 'type' => 'boolean'],
            'abm_applecare_is_renewable' => ['label_key' => 'admin/settings/sync_adapters.extra_applecare_is_renewable', 'type' => 'boolean'],
            'abm_device_capacity' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_device_capacity'],
            'abm_wifi_mac' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_wifi_mac'],
            'abm_bluetooth_mac' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_bluetooth_mac'],
            // Desktop Macs with two Ethernet NICs (Mac Pro, iMac Pro)
            // return ethernetMacAddress as a two-element array. Split
            // positionally so admins can map each physical port to its
            // own Snipe-IT custom field. #19877.
            'abm_ethernet_mac_1' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_ethernet_mac_1'],
            'abm_ethernet_mac_2' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_ethernet_mac_2'],
            // Apple returns IMEIs as an array (dual-SIM iPhones carry
            // two, older devices one). Positional split so admins can
            // map each to its own custom field. #19754.
            'abm_imei_1' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_imei_1'],
            'abm_imei_2' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_imei_2'],
            'abm_meid' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_meid'],
            'abm_eid' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_eid'],
            'abm_added_to_org' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_added_to_org', 'type' => 'date'],
            'abm_released_from_org' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_released_from_org', 'type' => 'date'],
            'abm_org_status' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_org_status'],
            'abm_activation_lock_enabled' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_activation_lock_enabled', 'type' => 'boolean'],
            // Apple Business Manager's built-in MDM (formerly Apple
            // Business Essentials). Native slots (hostname, os,
            // osVersion, lastSeen, primaryMac, imei, meid) are
            // overridden from the MDM detail endpoint when we have it
            // since MDM is runtime truth. The extras below are the
            // MDM-only fields that don't correspond to native slots.
            'abm_mdm_file_vault_enabled' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_mdm_file_vault_enabled', 'type' => 'boolean'],
            'abm_mdm_firewall_enabled' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_mdm_firewall_enabled', 'type' => 'boolean'],
            'abm_mdm_device_lock_status' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_mdm_device_lock_status'],
            'abm_mdm_lost_mode_status' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_mdm_lost_mode_status'],
            'abm_mdm_device_erase_status' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_mdm_device_erase_status'],
            'abm_mdm_storage_total' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_mdm_storage_total'],
            'abm_mdm_storage_free' => ['label_key' => 'admin/settings/sync_adapters.abm_extra_mdm_storage_free'],
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
            Log::channel('sync-adapters')->warning(sprintf(
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

        // Same opt-in gate for Activation Lock. Same N+1 shape as
        // AppleCare (one extra API call per device), so we skip it
        // unless the admin has mapped the extra.
        $enrichWithActivationLock = $this->hasMappedActivationLockField();

        // Apple Business Manager's built-in MDM (formerly Apple
        // Business Essentials). The list endpoint is one call per sync
        // run and gives us hostname + os, so we always fetch it.
        // Build a serial -> mdmDevice basic-info map for the join.
        // Best-effort: failures leave the map empty and records stay
        // un-enriched.
        $serialToMdmDevice = [];
        try {
            foreach ($client->mdmDevices() as $mdmDevice) {
                $serial = (string) ($mdmDevice['id'] ?? '');
                if ($serial !== '') {
                    $serialToMdmDevice[$serial] = $mdmDevice;
                }
            }
        } catch (\Throwable $e) {
            Log::channel('sync-adapters')->warning(sprintf(
                '%s mdmDevices list fetch failed: %s',
                $this->name(),
                $e->getMessage(),
            ));
        }

        // Detail endpoint is one call per MDM-enrolled device, so it's
        // gated behind at least one MDM-detail extra being mapped.
        $enrichWithMdmDetail = $this->hasMappedMdmDeviceDetailFields();

        // Allowed product families (lower-cased for comparison).
        // Empty means "all families".
        $allowedFamilies = $this->allowedProductFamilies();

        // Model-image enrichment fetches from appledb.dev, so it's
        // opt-in and off by default. Per-pull cache keyed by
        // hardwareModel avoids re-fetching the same image when a
        // tenant has many devices of the same model.
        $pullImages = $this->appleDbEnrichmentEnabled();
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
            if ($enrichWithActivationLock) {
                $record = $this->enrichRecordWithActivationLock($record, $client);
            }
            $mdmDevice = $serialToMdmDevice[$record->hardwareSerial] ?? null;
            if ($mdmDevice !== null) {
                $record = $this->enrichRecordWithMdmDeviceBasics($record, $mdmDevice);
                if ($enrichWithMdmDetail) {
                    $record = $this->enrichRecordWithMdmDeviceDetail($record, $client);
                }
            }
            if ($pullImages) {
                $imagesHandled = $this->ensureModelImage($record, $imagesHandled);
            }
            yield $record;
        }
    }

    /**
     * Resolve the ABM-side model name + productType and delegate to
     * the shared enrichModelFromAppleDb helper. ABM's native:model
     * mapping routes `abm_model_marketing_name` into AssetModel::name
     * when the admin opts in, so the image lookup has to mirror that
     * so it targets the row the framework will actually create.
     *
     * @param  array<string, bool>  $imagesHandled
     * @return array<string, bool>
     */
    private function ensureModelImage(HostInventoryRecord $record, array $imagesHandled): array
    {
        $modelName = $this->mappingFor('abm_model_marketing_name') === 'native:model'
            ? ($record->extra['abm_model_marketing_name'] ?? $record->hardwareModel)
            : $record->hardwareModel;

        if ($modelName === null || $modelName === '') {
            Log::channel('sync-adapters')->info("{$this->name()} enrichment skip for device {$record->sourceId}: no model name resolvable (hardwareModel and abm_model_marketing_name both blank)");

            return $imagesHandled;
        }

        $productType = $record->extra['abm_product_type'] ?? null;
        if (! is_string($productType) || $productType === '') {
            Log::channel('sync-adapters')->info("{$this->name()} enrichment skip for model \"{$modelName}\": ABM did not return a productType, nothing to look up on appledb.dev");

            return $imagesHandled;
        }

        $color = $record->extra['abm_color'] ?? null;

        return $this->enrichModelFromAppleDb(
            $modelName,
            $productType,
            is_string($color) ? $color : null,
            $imagesHandled,
        );
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
     * Gate for the Activation Lock enrichment. True when the admin has
     * routed the single abm_activation_lock_enabled extra to a real
     * target. Same shape as hasMappedAppleCareFields but scoped to one
     * key.
     */
    private function hasMappedActivationLockField(): bool
    {
        $target = $this->mappingFor('abm_activation_lock_enabled');

        return $target !== '' && $target !== 'skip';
    }

    /**
     * Gate for the MDM device-detail enrichment. True when the admin
     * has mapped any of the detail-endpoint-only extras (FileVault,
     * firewall, lock/lost/erase status, storage). The list-endpoint
     * enrichment (hostname + os) always runs regardless since the list
     * fetch is one call total.
     */
    private function hasMappedMdmDeviceDetailFields(): bool
    {
        foreach (array_keys($this->extraFields()) as $key) {
            if (! str_starts_with($key, 'abm_mdm_')) {
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
            Log::channel('sync-adapters')->warning(sprintf(
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
     * Fetch Activation Lock status for one asset and merge into the
     * record's extras. Handy for asset checkin flows because AL has
     * to be cleared before a device can be redeployed to a new user.
     * On empty or failed responses just return the un-enriched record
     * so the sync doesn't stall on a transient AL endpoint issue.
     */
    private function enrichRecordWithActivationLock(HostInventoryRecord $record, AppleBusinessManagerClient $client): HostInventoryRecord
    {
        try {
            $status = $client->deviceActivationLockStatus($record->sourceId);
        } catch (\Throwable $e) {
            Log::channel('sync-adapters')->warning(sprintf(
                '%s activation lock fetch failed for device %s: %s',
                $this->name(),
                $record->sourceId,
                $e->getMessage(),
            ));

            return $record;
        }

        $enabled = Arr::get($status, 'attributes.activationLockEnabled');
        if (! is_bool($enabled)) {
            return $record;
        }

        $extra = $record->extra;
        $extra['abm_activation_lock_enabled'] = $enabled;

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
     * Merge the cheap MDM-list-endpoint fields onto the record.
     * Populates hostname (deviceName) and os (derived from
     * productFamily). Called only when we have a matching mdmDevice
     * for this record's serial.
     *
     * @param  array<string, mixed>  $mdmDevice
     */
    private function enrichRecordWithMdmDeviceBasics(HostInventoryRecord $record, array $mdmDevice): HostInventoryRecord
    {
        $attrs = $mdmDevice['attributes'] ?? [];
        $deviceName = Arr::get($attrs, 'deviceName');
        $family = (string) Arr::get($attrs, 'productFamily', '');

        return new HostInventoryRecord(
            sourceKey: $record->sourceKey,
            sourceId: $record->sourceId,
            hostname: is_string($deviceName) && $deviceName !== '' ? $deviceName : $record->hostname,
            hardwareSerial: $record->hardwareSerial,
            hardwareModel: $record->hardwareModel,
            manufacturer: $record->manufacturer,
            primaryMac: $record->primaryMac,
            primaryIp: $record->primaryIp,
            os: self::osFromProductFamily($family) ?? $record->os,
            osVersion: $record->osVersion,
            lastSeen: $record->lastSeen,
            assetTag: $record->assetTag,
            assignedUserEmail: $record->assignedUserEmail,
            assignedUserName: $record->assignedUserName,
            vendorGroupId: $record->vendorGroupId,
            extra: $record->extra,
        );
    }

    /**
     * Fetch and merge the per-device MDM detail endpoint fields.
     * Runtime data wins: MDM's osVersion, lastCheckInDateTime, MAC
     * addresses, imei, meid override whatever the orgDevices path
     * populated. FileVault, firewall, lock/lost/erase statuses, and
     * storage go into extras. On empty or failed responses return
     * the un-enriched record so a transient MDM detail-endpoint issue
     * doesn't stall the sync.
     */
    private function enrichRecordWithMdmDeviceDetail(HostInventoryRecord $record, AppleBusinessManagerClient $client): HostInventoryRecord
    {
        try {
            $detail = $client->mdmDeviceDetails($record->hardwareSerial);
        } catch (\Throwable $e) {
            Log::channel('sync-adapters')->warning(sprintf(
                '%s mdmDevice detail fetch failed for device %s: %s',
                $this->name(),
                $record->sourceId,
                $e->getMessage(),
            ));

            return $record;
        }

        $attrs = $detail['attributes'] ?? [];
        if (! is_array($attrs) || $attrs === []) {
            return $record;
        }

        return new HostInventoryRecord(
            sourceKey: $record->sourceKey,
            sourceId: $record->sourceId,
            hostname: $record->hostname,
            hardwareSerial: $record->hardwareSerial,
            hardwareModel: $record->hardwareModel,
            manufacturer: $record->manufacturer,
            primaryMac: self::pickMdmPrimaryMac($attrs, $record->primaryMac),
            primaryIp: $record->primaryIp,
            os: $record->os,
            osVersion: self::firstNonEmptyString(Arr::get($attrs, 'osVersion'), $record->osVersion),
            lastSeen: self::parseMdmTimestamp(Arr::get($attrs, 'lastCheckInDateTime'), $record->lastSeen),
            assetTag: $record->assetTag,
            assignedUserEmail: $record->assignedUserEmail,
            assignedUserName: $record->assignedUserName,
            vendorGroupId: $record->vendorGroupId,
            extra: self::mergeMdmDetailExtras($record->extra, $attrs),
        );
    }

    /**
     * Return $preferred if it is a non-empty string, else $fallback.
     * Extracted so enrichRecordWithMdmDeviceDetail doesn't blow its
     * NPath budget on inline ternaries.
     */
    private static function firstNonEmptyString(mixed $preferred, mixed $fallback): mixed
    {
        if (is_string($preferred) && $preferred !== '') {
            return $preferred;
        }

        return $fallback;
    }

    /**
     * Pick the primary MAC from the MDM detail attributes. Prefers
     * Wi-Fi, falls back to Ethernet (desktops without Wi-Fi), then
     * whatever the orgDevices path already set.
     *
     * @param  array<string, mixed>  $attrs
     */
    private static function pickMdmPrimaryMac(array $attrs, ?string $fallback): ?string
    {
        $wifi = self::normalizeMacAddress(Arr::get($attrs, 'wifiMacAddress'));
        if ($wifi !== null) {
            return $wifi;
        }

        // ethernetMacAddress can be a two-element array on Macs with two
        // physical ports. Prefer the first slot so the primaryMac field
        // on a non-wifi Mac still populates something sensible.
        $ethernet = self::extractMacAtIndex(Arr::get($attrs, 'ethernetMacAddress'), 0);
        if ($ethernet !== null) {
            return $ethernet;
        }

        return $fallback;
    }

    /**
     * Parse an MDM timestamp string to Carbon, falling back to the
     * caller-supplied $fallback when the value is missing or Carbon
     * can't parse it.
     */
    private static function parseMdmTimestamp(mixed $value, ?Carbon $fallback): ?Carbon
    {
        if (! is_string($value) || $value === '') {
            return $fallback;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return $fallback;
        }
    }

    /**
     * Merge MDM detail attributes into the record's existing extras.
     * The seven detail-only fields (FileVault, firewall, statuses,
     * storage) always overwrite. The shared extras (MACs, imei, meid)
     * only overwrite when MDM has a non-empty value, so the orgDevices
     * value stays as the fallback.
     *
     * @param  array<string, mixed>  $extra
     * @param  array<string, mixed>  $attrs
     * @return array<string, mixed>
     */
    private static function mergeMdmDetailExtras(array $extra, array $attrs): array
    {
        $extra['abm_mdm_file_vault_enabled'] = Arr::get($attrs, 'isFileVaultEnabled');
        $extra['abm_mdm_firewall_enabled'] = Arr::get($attrs, 'isFirewallEnabled');
        $extra['abm_mdm_device_lock_status'] = Arr::get($attrs, 'deviceLockStatus');
        $extra['abm_mdm_lost_mode_status'] = Arr::get($attrs, 'lostModeStatus');
        $extra['abm_mdm_device_erase_status'] = Arr::get($attrs, 'deviceEraseStatus');
        $extra['abm_mdm_storage_total'] = Arr::get($attrs, 'storageTotalCapacity');
        $extra['abm_mdm_storage_free'] = Arr::get($attrs, 'storageFreeCapacity');

        // Wi-Fi and Bluetooth are single-radio on every current Apple
        // SKU so they stay scalar. Ethernet can be a two-element array
        // on dual-NIC Macs, so split positionally like IMEI.
        $singleMacMap = [
            'wifiMacAddress' => 'abm_wifi_mac',
            'bluetoothMacAddress' => 'abm_bluetooth_mac',
        ];
        foreach ($singleMacMap as $mdmKey => $extraKey) {
            $val = self::normalizeMacAddress(Arr::get($attrs, $mdmKey));
            if ($val !== null) {
                $extra[$extraKey] = $val;
            }
        }

        $mdmEthernet = Arr::get($attrs, 'ethernetMacAddress');
        $ethernet1 = self::extractMacAtIndex($mdmEthernet, 0);
        if ($ethernet1 !== null) {
            $extra['abm_ethernet_mac_1'] = $ethernet1;
        }
        $ethernet2 = self::extractMacAtIndex($mdmEthernet, 1);
        if ($ethernet2 !== null) {
            $extra['abm_ethernet_mac_2'] = $ethernet2;
        }

        // IMEI is an array in both orgDevices and MDM-detail shapes.
        // Overwrite each positional slot only when MDM has a value so
        // the orgDevices value stays as the fallback.
        $mdmImei = Arr::get($attrs, 'imei');
        $first = self::extractImeiAtIndex($mdmImei, 0);
        if ($first !== null) {
            $extra['abm_imei_1'] = $first;
        }
        $second = self::extractImeiAtIndex($mdmImei, 1);
        if ($second !== null) {
            $extra['abm_imei_2'] = $second;
        }

        $meid = Arr::get($attrs, 'meid');
        if ($meid !== null && $meid !== '') {
            $extra['abm_meid'] = $meid;
        }

        return $extra;
    }

    /**
     * Insert the colons Snipe-IT's MAC custom-field validator
     * requires. Apple's APIs return bare 12-hex-char strings
     * (`0123456789AB`) and occasionally the 16-char EUI-64 shape for
     * Bluetooth. Both get normalized to colon-separated uppercase
     * pairs. Non-hex characters are stripped so an already-formatted
     * value round-trips cleanly. Returns null for null, empty, or
     * non-string input, or when the stripped hex isn't a valid length.
     */
    private static function normalizeMacAddress(mixed $raw): ?string
    {
        if (! is_string($raw) || $raw === '') {
            return null;
        }

        $hex = strtoupper((string) preg_replace('/[^0-9A-Fa-f]/', '', $raw));
        $length = strlen($hex);
        if ($length !== 12 && $length !== 16) {
            return null;
        }

        return rtrim(chunk_split($hex, 2, ':'), ':');
    }

    /**
     * Pick a single IMEI out of Apple's `imei` field, which is
     * typically an array (dual-SIM carries two, single-SIM carries a
     * one-element array). A bare-scalar shape is tolerated as a
     * fallback so vendor-side drift doesn't drop a valid IMEI into
     * nothing - a scalar fills slot 0 and leaves slot 1 null.
     * Returns null when the slot is absent, when the value isn't a
     * non-empty string, or when a scalar is requested for slot 1.
     */
    /**
     * Pick a single MAC out of Apple's `ethernetMacAddress` field,
     * which is a two-element array on Macs with two physical NICs and
     * a bare string on everything else. Shape semantics mirror
     * extractImeiAtIndex: scalar fills slot 0 only, array indexes
     * positionally, anything else returns null. The returned value
     * passes through normalizeMacAddress so the colon-separated upper-
     * case shape custom-field validators expect round-trips cleanly.
     */
    private static function extractMacAtIndex(mixed $value, int $index): ?string
    {
        if (is_string($value)) {
            return $index === 0 ? self::normalizeMacAddress($value) : null;
        }
        if (! is_array($value)) {
            return null;
        }

        return self::normalizeMacAddress($value[$index] ?? null);
    }

    private static function extractImeiAtIndex(mixed $value, int $index): ?string
    {
        if (is_string($value)) {
            return $index === 0 && $value !== '' ? $value : null;
        }
        if (! is_array($value)) {
            return null;
        }
        $imei = $value[$index] ?? null;
        if (! is_string($imei) || $imei === '') {
            return null;
        }

        return $imei;
    }

    /**
     * Apple's productFamily enum to the runtime OS name customers
     * expect to see in the assets table. Returns null for families
     * without a well-known OS name so callers can leave the field
     * alone rather than blanking it.
     */
    private static function osFromProductFamily(string $family): ?string
    {
        return match ($family) {
            'Mac' => 'macOS',
            'iPhone' => 'iOS',
            'iPad' => 'iPadOS',
            'AppleTV' => 'tvOS',
            'Watch' => 'watchOS',
            'Vision' => 'visionOS',
            default => null,
        };
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

        // Pass the AssetModel name the framework will use (mirrors
        // ensureModelImage's resolution) so the trait can
        // short-circuit the AppleDB info call when an existing row
        // already has every enrichable field set.
        $modelName = $this->mappingFor('abm_model_marketing_name') === 'native:model'
            ? (Arr::get($attrs, 'deviceModel') ?? Arr::get($attrs, 'partNumber'))
            : Arr::get($attrs, 'partNumber');
        $productType = Arr::get($attrs, 'productType');
        $canonical = $this->canonicalIdentityFor($productType, $modelName);
        $appleDbExtras = $this->appleDbExtrasFor($productType, $modelName);

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
            // ABM returns three MAC-shaped strings per device. Wi-Fi
            // is the one every enrolled device has, so it fills the
            // normalized primaryMac field. Ethernet and Bluetooth
            // stay in extras for admins who want to route them to
            // custom fields. normalizeMacAddress() inserts the colons
            // Snipe-IT's MAC custom-field validator requires.
            primaryMac: self::normalizeMacAddress(Arr::get($attrs, 'wifiMacAddress')),
            primaryIp: null,
            os: null,
            osVersion: null,
            lastSeen: null,
            assignedUserEmail: null,
            assignedUserName: null,
            vendorGroupId: null,
            extra: [
                ...$appleDbExtras,
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
                'abm_device_capacity' => Arr::get($attrs, 'deviceCapacity'),
                'abm_wifi_mac' => self::normalizeMacAddress(Arr::get($attrs, 'wifiMacAddress')),
                'abm_bluetooth_mac' => self::normalizeMacAddress(Arr::get($attrs, 'bluetoothMacAddress')),
                // ethernetMacAddress is a two-element array on dual-NIC
                // Macs. Positional split so admins can map each port to
                // its own custom field. See #19877.
                'abm_ethernet_mac_1' => self::extractMacAtIndex(Arr::get($attrs, 'ethernetMacAddress'), 0),
                'abm_ethernet_mac_2' => self::extractMacAtIndex(Arr::get($attrs, 'ethernetMacAddress'), 1),
                // Cellular-only identifiers. Populated for iPhones and
                // cellular iPads, null on Macs / non-cellular devices.
                // IMEI is an array in Apple's shape, _1/_2 split by
                // index so admins can map each to its own custom field.
                'abm_imei_1' => self::extractImeiAtIndex(Arr::get($attrs, 'imei'), 0),
                'abm_imei_2' => self::extractImeiAtIndex(Arr::get($attrs, 'imei'), 1),
                'abm_meid' => Arr::get($attrs, 'meid'),
                'abm_eid' => Arr::get($attrs, 'eid'),
                'abm_added_to_org' => self::parseOrderDate(Arr::get($attrs, 'addedToOrgDateTime')),
                'abm_released_from_org' => self::parseOrderDate(Arr::get($attrs, 'releasedFromOrgDateTime')),
                'abm_org_status' => Arr::get($attrs, 'status'),
                // Populated by enrichRecordWithActivationLock() when
                // the admin has mapped the extra. Kept as a nullable
                // slot so the mapping-target list surfaces the key
                // even before the first sync run fills it in.
                'abm_activation_lock_enabled' => null,
            ],
            canonicalMarketingName: $canonical['canonicalMarketingName'],
            canonicalModelNumber: $canonical['canonicalModelNumber'],
        );
    }

    /**
     * Which mode we're operating in: 'business' or 'school'. Stored
     * plain-text as a credential value so settingsSchema() renders
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
            // Rotate to app.timezone before extracting the date part so a
            // vendor datetime at the UTC day boundary records the date
            // the user would recognize. Same reason the last_seen path
            // converts to app timezone before producing its naive string.
            // #19765.
            return Carbon::parse($value)->timezone(config('app.timezone'))->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
