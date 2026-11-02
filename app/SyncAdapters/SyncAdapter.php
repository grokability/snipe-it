<?php

namespace App\SyncAdapters;

use App\Models\Asset;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\Rules\ExternalUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

/**
 * The single base class every host-inventory adapter extends. Owns
 * every piece of behavior that's the same across adapters regardless
 * of auth shape: URL storage with SSRF-guarded validation, encrypted
 * secret storage, blank-preserves-existing save semantics, active +
 * heartbeat toggles, per-field target mapping, isEnabled().
 *
 * The one thing subclasses declare is their settingsSchema(): a
 * list of the auth fields the vendor requires and whether each is a
 * secret (masked with a show/hide toggle) or plain text. That's
 * enough for the shared blade partial to render the form, for
 * saveConfig() to persist correctly, for isEnabled() to enforce
 * required-cred presence, and for the sync path to read decrypted
 * values via credential().
 *
 * Auth shapes currently modeled by schema alone:
 *   - single API token (Fleet, Kandji, Jamf Pro, osctrl, Zentral, JumpCloud)
 *   - dual key pair (Addigy, Datto RMM)
 *   - OAuth 2.0 client credentials (Intune, Google Workspace, Azure AD)
 *
 * Auth shapes we can add later without touching the base. Schema
 * gains a `type` value beyond text/password (file upload for JSON
 * key files and certs, oauth_button for browser-redirect flows,
 * textarea for pasted private keys):
 *   - Google service account (file upload)
 *   - Apple ABM/ASM (certificate + private key)
 *   - Slack / any browser-redirect OAuth (oauth_button + callback)
 *   - AWS SigV4 (still three text/password fields plus a region)
 *   - Basic auth (still two fields, no new type)
 */
abstract class SyncAdapter
{
    // Asset-side upsert path. Exposes SyncAdapter::syncFromRecord($record) as
    // the public entry point every caller hits. Kept in its own trait
    // rather than inlined so this file stays focused on the adapter
    // contract (settings, credentials, mapping, direction, group
    // scoping, push helpers).
    use SyncsHostFromRecord;

    public function __construct(protected readonly SyncAdapterInstance $instance) {}

    /** Display label for the adapter TYPE (not the instance). Shown in the "Add adapter" dropdown. */
    abstract public static function typeLabel(): string;

    /**
     * Stable identifier stored in sync_adapter_instances.adapter_type.
     * Distinct from typeLabel() (localized display string) and name()
     * (per-instance slug). Adapters declare their own so the discovery
     * loop below can key the class map without a central registry.
     */
    abstract public static function typeSlug(): string;

    /**
     * Link to the vendor's public API documentation. Rendered as an
     * external link in the adapter catalog so admins can jump to the
     * vendor's own reference for the endpoints this adapter hits.
     * Return null when the vendor has no stable public docs URL or
     * gates its docs behind a login.
     */
    public static function docsUrl(): ?string
    {
        return null;
    }

    /** Vendor-specific inventory fetch. Yield HostInventoryRecord objects. */
    abstract public function pull(): iterable;

    /**
     * Discover every adapter subclass under app/SyncAdapters/*Adapter.php
     * and return a slug-keyed map of the concrete classes. Cached in a
     * static so repeat calls within a request don't re-scan the tree.
     *
     * @return array<string, class-string<self>>
     */
    public static function allTypes(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $out = [];
        $base = app_path('SyncAdapters').DIRECTORY_SEPARATOR;
        foreach (glob($base.'*'.DIRECTORY_SEPARATOR.'*Adapter.php') as $file) {
            $relative = substr($file, strlen($base), -4);
            $class = 'App\\SyncAdapters\\'.str_replace(DIRECTORY_SEPARATOR, '\\', $relative);
            if (! class_exists($class) || ! is_subclass_of($class, self::class)) {
                continue;
            }
            $reflection = new \ReflectionClass($class);
            if ($reflection->isAbstract()) {
                continue;
            }
            $out[$class::typeSlug()] = $class;
        }
        ksort($out);

        return $cache = $out;
    }

    /**
     * Instantiate the adapter class for a given SyncAdapterInstance row.
     * Returns null when the instance's adapter_type doesn't match any
     * discovered class (e.g. an old row for a removed adapter).
     */
    public static function factory(SyncAdapterInstance $instance): ?self
    {
        $types = self::allTypes();
        $class = $types[$instance->adapter_type] ?? null;
        if ($class === null) {
            return null;
        }

        return new $class($instance);
    }

    /**
     * List of registered type slugs, in the same order as allTypes().
     *
     * @return array<int, string>
     */
    public static function typeNames(): array
    {
        return array_keys(self::allTypes());
    }

    /**
     * Type slug -> display label. Powers the "Add adapter" dropdown.
     *
     * @return array<string, string>
     */
    public static function typeLabels(): array
    {
        $out = [];
        foreach (self::allTypes() as $slug => $class) {
            $out[$slug] = $class::typeLabel();
        }

        return $out;
    }

    /**
     * Type slug -> {label, docs_url, direction} catalog. Powers the
     * help pane's list of supported adapters so admins can jump into
     * either the "Add adapter" modal with the type pre-selected or
     * the docs page (vendor's own for named adapters, Snipe-IT's own
     * for Custom HTTP). docs_url is null for adapters whose vendor
     * has no stable public reference URL or gates its docs behind a
     * login. direction is derived from whether the class extends
     * PushableSyncAdapter so the catalog UI reflects reality without
     * a separate list for admins or docs to drift from.
     *
     * @return array<string, array{label: string, docs_url: ?string, direction: 'pull'|'both'}>
     */
    public static function typeCatalog(): array
    {
        $out = [];
        foreach (self::allTypes() as $slug => $class) {
            $out[$slug] = [
                'label' => $class::typeLabel(),
                'docs_url' => $class::docsUrl(),
                'direction' => is_subclass_of($class, PushableSyncAdapter::class) ? 'both' : 'pull',
            ];
        }

        return $out;
    }

    /**
     * Hydrate every configured instance from the database as a live
     * adapter, skipping rows whose adapter_type is no longer registered.
     *
     * @return array<int, self>
     */
    public static function allInstances(): array
    {
        return SyncAdapterInstance::query()
            ->orderBy('label')
            ->get()
            ->map(fn (SyncAdapterInstance $i) => self::factory($i))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Declare this adapter's authentication field schema. The base
     * class drives every settings-page, storage, validation, and
     * enable-check decision off this list. Adapters typically have
     * only this and pull() to think about.
     *
     * Field entry keys:
     *   - key      (required) storage key AND form-field-name suffix
     *   - label    (required) display label rendered next to the input
     *   - type     (optional, default 'text') one of 'text', 'password',
     *              'textarea', 'checkbox', 'select'. 'password' also
     *              implied when secret => true.
     *   - options  (optional) for type='select', a map of
     *              value => display-label pairs.
     *   - secret   (optional, default false) render as password with
     *              show/hide toggle AND encrypt the value at rest
     *   - required (optional, default true) treat as required for the
     *              isEnabled() gate. UI hint is up to the label copy
     *   - help     (optional, default null) inline help text under the
     *              field, passed straight to `<x-form.row help_text=...>`
     *   - placeholder (optional) placeholder text shown inside an
     *              empty text / textarea input. Ignored for other
     *              types.
     *   - default  (optional) initial-render fallback used when
     *              nothing is stored yet. For type='multiselect' pass
     *              an array of option keys to pre-select on first
     *              render.
     *
     * @return array<int, array{key: string, label: string, type?: string, options?: array<string, string>, secret?: bool, required?: bool, help?: string|null, placeholder?: string|null, default?: array<int, string>|string|null}>
     */
    abstract public function settingsSchema(): array;

    /**
     * Adapter-specific extra fields the vendor emits, keyed by the
     * extra-array key from HostInventoryRecord. Values are either a
     * plain string label (defaults to text type) or an array shaped
     * ['label' => 'Human Label', 'type' => 'text' | 'boolean',
     * 'admin_defined' => bool] OR ['label_key' => 'lang.key.path',
     * 'type' => 'text' | 'boolean', 'admin_defined' => bool]. The
     * label_key variant is resolved at render time via trans() with
     * ':vendor' bound to the adapter's typeLabel so translators
     * localize each shape once for every adapter that emits it.
     *
     * Type filters the target pool via MappingTargets::optionsForExtra:
     * 'text' offers text/textarea/markdown-textarea custom fields
     * plus native asset_tag/model/notes. 'boolean' offers checkbox
     * fields only. admin_defined (used by adapters that expose
     * tenant-labeled vendor custom fields) further narrows to
     * custom-only.
     *
     * Adapters that don't emit extras leave this empty.
     *
     * @return array<string, string|array{label?: string, label_key?: string, type?: string, admin_defined?: bool}>
     */
    public function extraFields(): array
    {
        return $this->presetExtraFields() + $this->adminDefinedExtraFields();
    }

    /**
     * Vendor-specific extras the adapter extracts in its own normalize()
     * from well-known API fields. Default empty - adapters that emit
     * extras override this with their preset keys (e.g. Intune's
     * compliance_state, Jamf's building, Kandji's blueprint).
     *
     * This replaced the old extraFields() hook when admin-defined extras
     * were added. extraFields() now composes presets with admin extras
     * so Blade / mapping-targets consumers see the union.
     *
     * @return array<string, string|array{label?: string, label_key?: string, type?: string, admin_defined?: bool}>
     */
    public function presetExtraFields(): array
    {
        return [];
    }

    /**
     * Admin-defined extras surfaced as mapping targets. Every adapter
     * gets a Custom Extras widget below its mapping table where admins
     * can pair a Snipe-IT destination (custom:X or native:X) with a
     * vendor-record dot-path. extraFields() includes these alongside
     * the adapter's own presetExtraFields() so the mapping picker
     * shows both groups.
     *
     * @return array<string, array{label: string, admin_defined: true}>
     */
    public function adminDefinedExtraFields(): array
    {
        $out = [];
        foreach (array_keys($this->adminDefinedExtras()) as $key) {
            $out[$key] = ['label' => $key, 'admin_defined' => true];
        }

        return $out;
    }

    /**
     * Admin-configured extras rows for this instance: destination key
     * (`custom:X` / `native:X`) mapped to a dot-path into the vendor
     * record. Shared with CustomHttpAdapter's existing Vendor Response
     * Paths widget via the same `field_paths` config key. Entries whose
     * destination is a HostInventoryRecord slot (hostname / serial /
     * etc.) are filtered out here - those are CustomHttp-specific and
     * driven by its own normalize path, not the admin-extras overlay.
     *
     * @return array<string, string>
     */
    public function adminDefinedExtras(): array
    {
        $raw = SyncAdapterConfig::get($this->instance->id, 'field_paths');
        if (! is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return [];
        }

        $out = [];
        foreach ($decoded as $key => $path) {
            if (! is_string($key) || ! is_string($path) || $path === '') {
                continue;
            }
            if (! str_starts_with($key, 'custom:') && ! str_starts_with($key, 'native:')) {
                continue;
            }
            $out[$key] = $path;
        }

        return $out;
    }

    /**
     * Overlay admin-defined extras onto an adapter's preset extras.
     * Called from each adapter's normalize() after assembling the
     * preset extras, right before yielding the HostInventoryRecord.
     * Walks every configured dot-path against the raw vendor record
     * and populates the corresponding extras key.
     *
     * @param  array<string, mixed>  $extra
     * @param  array<string, mixed>  $vendorRecord
     * @return array<string, mixed>
     */
    protected function applyAdminExtras(array $extra, array $vendorRecord): array
    {
        foreach ($this->adminDefinedExtras() as $key => $path) {
            $extra[$key] = self::dotPathGet($vendorRecord, $path);
        }

        return $extra;
    }

    /**
     * Which destination options to expose in this adapter's Custom
     * Extras widget. Named adapters only let admins add new custom:X
     * / native:X mappings so they can't accidentally remap an identity
     * field (serial, hostname, etc.) that the adapter's normalize()
     * owns. CustomHttp overrides this to include HostInventoryRecord
     * slots because for that adapter, admins ARE the ones defining
     * where every field comes from.
     *
     * @return array<string, string>
     */
    public function customExtrasDestinationOptions(): array
    {
        $options = [
            // native:asset_tag lets admins route a vendor field (Intune's
            // deviceName, Jamf's name, etc.) to the asset_tag column.
            // writeNative() prevents blank vendor values overwriting an
            // admin-curated asset_tag.
            'native:asset_tag' => MappingTargets::labelFor('native:asset_tag'),
            'native:notes' => trans('admin/settings/sync_adapters.target_native_notes'),
            'native:purchase_date' => MappingTargets::labelFor('native:purchase_date'),
            'native:order_number' => MappingTargets::labelFor('native:order_number'),
        ];

        foreach (\App\Models\CustomField::whereIn('element', ['text', 'textarea', 'markdown-textarea', 'checkbox'])->orderBy('name')->get() as $cf) {
            $options['custom:'.$cf->id] = trans('admin/settings/sync_adapters.target_custom_prefix').': '.$cf->name;
        }

        return $options;
    }

    /**
     * CustomHttp drives its field_paths widget via its own schema
     * entry (type: field_map) because that widget predates admin-
     * extras and also exposes HostInventoryRecord slots. Named
     * adapters opt into the shared Custom Extras widget by leaving
     * this at its default of false.
     */
    public function rendersFieldPathsInSchema(): bool
    {
        return false;
    }

    /**
     * Whether the shared "Custom Extras" fieldset renders on this
     * adapter's settings page. Default off so adapters that haven't
     * been wired up (no applyAdminExtras() call in their normalize())
     * don't advertise a widget that then does nothing at sync time.
     * Adapters opt in once their normalize() is calling
     * applyAdminExtras() by overriding this to true.
     */
    public function supportsAdminDefinedExtras(): bool
    {
        return false;
    }

    /**
     * Hook for an adapter-specific "fetch extra details per device"
     * toggle in the shared settings UI. When this returns a label +
     * help string, the base saveConfig persists the posted
     * `{slug}_fetch_per_device_details` boolean and the shared Blade
     * renders a checkbox row. Adapters that don't need the toggle
     * (every adapter whose LIST endpoint already returns everything)
     * leave this at the default null.
     *
     * @return array{label: string, help: string}|null
     */
    public function perDeviceDetailsToggle(): ?array
    {
        return null;
    }

    /**
     * Walk a dot-separated path into a nested array structure. Empty
     * path returns the input unchanged. Numeric segments (like
     * "data.0.serial") index into sequential arrays. Returns null for
     * any miss so downstream null-guards do the right thing.
     */
    public static function dotPathGet(mixed $data, string $path): mixed
    {
        if ($path === '') {
            return $data;
        }

        $current = $data;
        foreach (explode('.', $path) as $segment) {
            if (! is_array($current)) {
                return null;
            }
            if (array_key_exists($segment, $current)) {
                $current = $current[$segment];

                continue;
            }
            if (ctype_digit($segment) && array_key_exists((int) $segment, $current)) {
                $current = $current[(int) $segment];

                continue;
            }

            return null;
        }

        return $current;
    }

    /**
     * Section titles + help copy for the settings schema. Adapters
     * that group their credential fields via a `section` key on
     * schema entries override this to give each section a labeled
     * <fieldset> in the settings UI. Default is empty, in which case
     * the credentials partial renders the schema entries flat.
     *
     * Expected shape:
     *   ['section_key' => ['title' => string, 'help' => ?string], ...]
     *
     * @return array<string, array{title: string, help?: string}>
     */
    public function settingsSections(): array
    {
        return [];
    }

    /**
     * Optional: name a settingsSections() key that should include
     * the Base URL row. The shell opens the fieldset around Base URL
     * and the credentials partial's schema loop continues rendering
     * into the same fieldset for that section's entries. Returns null
     * to keep Base URL rendered above the fieldsets (default).
     */
    public function baseUrlSection(): ?string
    {
        return null;
    }

    public function name(): string
    {
        return $this->instance->slug;
    }

    public function label(): string
    {
        return $this->instance->label;
    }

    /**
     * Public accessor for the backing instance. Blade views that need to
     * reason about per-instance state (lock keys, mapping tables, etc)
     * use through this vs relying on the protected constructor property.
     */
    public function instance(): SyncAdapterInstance
    {
        return $this->instance;
    }

    public function companyId(): ?int
    {
        return $this->instance->company_id;
    }

    public function isActive(): bool
    {
        return $this->instance->active;
    }

    public function isEnabled(): bool
    {
        if (! $this->instance->active) {
            return false;
        }

        if ($this->usesConfigurableUrl() && empty(SyncAdapterConfig::get($this->instance->id, 'url'))) {
            return false;
        }

        foreach ($this->settingsSchema() as $field) {
            if (($field['required'] ?? true) && ! SyncAdapterConfig::has($this->instance->id, $field['key'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether the admin needs to configure a base URL for this
     * adapter. Defaults to true because most vendors expose a
     * tenant-specific host. Adapters whose host is fixed (Apple ABM
     * derives the host from the Portal enum, so there's nothing for
     * the admin to type) override to false, and the shared form,
     * validation, and isEnabled() gate all skip the URL field.
     */
    public function usesConfigurableUrl(): bool
    {
        return true;
    }

    /**
     * Three-state readiness for the settings-page tab indicator:
     *
     * - 'active'   = will sync (Active toggle on AND URL + required credentials all present)
     * - 'partial'  = Active toggle on but the config is incomplete (admin turned it on before finishing setup)
     * - 'inactive' = Active toggle off (regardless of whether config exists, this is the "off" state)
     *
     * Kept separate from isEnabled() so the tab indicator can distinguish
     * "not turned on yet" from "turned on but not working yet", which
     * needs different admin attention.
     */
    public function readinessStatus(): string
    {
        if ($this->isEnabled()) {
            return 'active';
        }

        return $this->instance->active ? 'partial' : 'inactive';
    }

    /**
     * Rules the controller enforces before saveConfig() runs.
     * ExternalUrl blocks loopback / RFC-1918 / cloud-metadata targets
     * so an admin can't turn Snipe-IT into an SSRF primitive by
     * pointing at 127.0.0.1 or 169.254.169.254. Credential fields
     * stay nullable at rules-time so admins can save a URL-only
     * draft while they go fetch credentials from the vendor console.
     */
    public function validationRules(): array
    {
        $slug = $this->instance->slug;

        $rules = [
            $slug.'_asset_tag_pattern' => ['nullable', 'string', 'max:191', new \App\Rules\AssetTagPatternRule],
            $slug.'_default_category_id' => ['nullable', 'integer', 'exists:categories,id'],
            $slug.'_default_status_id' => ['required', 'integer', 'exists:status_labels,id'],
            $slug.'_default_fieldset_id' => ['nullable', 'integer', 'exists:custom_fieldsets,id'],
            $slug.'_user_match_strategy' => ['nullable', 'string', 'in:none,email,username,username_then_email'],
            $slug.'_adopt_by_serial' => ['nullable', 'boolean'],
            $slug.'_create_snipeit_assets_on_pull' => ['nullable', 'boolean'],
        ];

        // Admin-defined extras widget. Posts as an associative array
        // of {destination: vendor_path}. Only rendered for adapters
        // that opt in via supportsAdminDefinedExtras(), but the rule
        // itself is cheap and harmless when absent.
        if ($this->supportsAdminDefinedExtras()) {
            $rules[$slug.'_field_paths'] = ['nullable', 'array'];
            $rules[$slug.'_field_paths.*'] = ['nullable', 'string', 'max:191'];
        }

        if ($this->perDeviceDetailsToggle() !== null) {
            $rules[$slug.'_fetch_per_device_details'] = ['nullable', 'boolean'];
        }

        if ($this->usesConfigurableUrl()) {
            $rules[$slug.'_url'] = ['required', new ExternalUrl('app.sync_adapter_allow_internal_targets')];
        }

        foreach ($this->settingsSchema() as $field) {
            // Auth fields default to required so admins can't save a
            // half-configured adapter that then silently refuses to
            // sync because isEnabled() returns false. Secret fields
            // are pre-populated with the decrypted value on the
            // settings page, so submission always carries a value
            // unless the admin explicitly cleared it (which is an
            // error worth surfacing). Adapters declaring a truly
            // optional credential set `required => false`.
            $required = $field['required'] ?? true;
            $fieldName = $slug.'_'.$field['key'];
            $type = $field['type'] ?? 'text';

            // Multiselect fields post as `name[]`, so they arrive as
            // an array (or absent when nothing is selected). Validate
            // as an array with string members, not as a string.
            if ($type === 'multiselect') {
                $rules[$fieldName] = [$required ? 'required' : 'nullable', 'array'];
                $rules[$fieldName.'.*'] = ['string'];

                continue;
            }

            // Category selectors post the chosen category id (or an
            // empty string to fall back to default_category_id).
            if ($type === 'category') {
                $rules[$fieldName] = [$required ? 'required' : 'nullable', 'integer', 'exists:categories,id'];

                continue;
            }

            // Field-map repeater posts as an associative array of
            // {snipe_field: vendor_path}. Values are trimmed strings
            // (dot-paths). Empty submissions produce an empty array,
            // which persists as "{}" and reads back as no mappings.
            if ($type === 'field_map') {
                $rules[$fieldName] = [$required ? 'required' : 'nullable', 'array'];
                $rules[$fieldName.'.*'] = ['nullable', 'string'];

                continue;
            }

            $rules[$fieldName] = [
                $required ? 'required' : 'nullable',
                'string',
            ];
        }

        return $rules;
    }

    public function saveConfig(Request $request): void
    {
        $slug = $this->instance->slug;

        if ($this->usesConfigurableUrl()) {
            $this->persistUrl($request, $slug);
        }
        $this->persistCredentialsFromSchema($request, $slug);
        $this->persistOperationalSettings($request, $slug);

        if ($this->supportsGroupScoping()) {
            $this->persistGroupMappings($request, $slug);
        }

        if ($this->supportsAdminDefinedExtras()) {
            $this->persistAdminDefinedExtras($request, $slug);
        }

        $validFields = $this->validMappingFields();
        $this->persistFieldMappings($request, $slug, $validFields);
        $this->persistFieldDirections($request, $slug, $validFields);

        $this->afterSaveConfig();
    }

    /**
     * Persist the Custom Extras widget's rows. Posted shape is an
     * associative array mapping destination (`custom:X` or `native:X`)
     * to a vendor-record dot-path. Stored as a JSON blob in
     * `field_paths` and also written as self-routing mapping entries
     * (mapping.<key> = <key>) so the framework's extras loop writes
     * each extracted value to its target at sync time.
     */
    private function persistAdminDefinedExtras(Request $request, string $slug): void
    {
        $posted = (array) $request->input($slug.'_field_paths', []);
        $cleaned = [];
        foreach ($posted as $key => $path) {
            if (! is_string($key) || ! is_string($path)) {
                continue;
            }
            $path = trim($path);
            if ($path === '') {
                continue;
            }
            if (! str_starts_with($key, 'custom:') && ! str_starts_with($key, 'native:')) {
                continue;
            }
            $cleaned[$key] = $path;
            SyncAdapterConfig::put($this->instance->id, 'mapping.'.$key, $key);
        }

        SyncAdapterConfig::put(
            $this->instance->id,
            'field_paths',
            $cleaned === [] ? '' : json_encode($cleaned, JSON_UNESCAPED_SLASHES),
        );
    }

    /**
     * URL is always overwritten from the form. Blank means "no URL
     * configured yet" which the isEnabled() gate then treats as
     * "instance not usable."
     */
    private function persistUrl(Request $request, string $slug): void
    {
        SyncAdapterConfig::put($this->instance->id, 'url', $request->input($slug.'_url'));
    }

    /**
     * Blank input preserves the existing value so admins can update the
     * URL without re-entering every credential every time. Secrets get
     * encrypted at rest. Plain-text fields (like OAuth tenant / client
     * IDs) get stored as-is.
     */
    private function persistCredentialsFromSchema(Request $request, string $slug): void
    {
        foreach ($this->settingsSchema() as $field) {
            $fieldName = $slug.'_'.$field['key'];
            $type = $field['type'] ?? 'text';

            // Checkbox fields always overwrite: an unchecked box is
            // a real value ("off") and needs to overwrite a
            // previously-saved "on". Text and password fields
            // preserve blank input so admins can update the URL
            // without re-entering every secret.
            if ($type === 'checkbox') {
                SyncAdapterConfig::put(
                    $this->instance->id,
                    $field['key'],
                    $request->boolean($fieldName) ? '1' : '0',
                );

                continue;
            }

            // Multiselect also always overwrites. Empty selection
            // is a real value (means "sync all"). Store as JSON so
            // values with commas / special chars round-trip cleanly.
            if ($type === 'multiselect') {
                $values = (array) $request->input($fieldName, []);
                $values = array_values(array_filter($values, fn ($v) => $v !== null && $v !== ''));
                SyncAdapterConfig::put(
                    $this->instance->id,
                    $field['key'],
                    json_encode($values),
                );

                continue;
            }

            // Category selector: empty means "fall back to the
            // instance-wide default_category_id". Store as-is so a
            // deliberate clear overwrites a previous pick.
            if ($type === 'category') {
                $value = $request->input($fieldName, '');
                SyncAdapterConfig::put(
                    $this->instance->id,
                    $field['key'],
                    $value === null ? '' : (string) $value,
                );

                continue;
            }

            // Field-map repeater: assoc array of {field_key: path}.
            // Empty submissions overwrite as {} so a deliberate
            // "clear everything".
            if ($type === 'field_map') {
                $values = (array) $request->input($fieldName, []);
                $pending = (array) $request->input($fieldName.'_pending', []);
                $pendingKey = is_string($pending['key'] ?? null) ? trim($pending['key']) : '';
                $pendingValue = is_string($pending['value'] ?? null) ? trim($pending['value']) : '';
                if ($pendingKey !== '' && $pendingValue !== '') {
                    $values[$pendingKey] = $pendingValue;
                }
                $values = array_filter(
                    $values,
                    fn ($v, $k) => is_string($k) && $k !== '' && is_string($v) && trim($v) !== '',
                    ARRAY_FILTER_USE_BOTH,
                );
                SyncAdapterConfig::put(
                    $this->instance->id,
                    $field['key'],
                    json_encode($values),
                );

                continue;
            }

            if (! $request->filled($fieldName)) {
                continue;
            }

            $value = $request->input($fieldName);
            if ($field['secret'] ?? false) {
                $value = Crypt::encrypt($value);
            }
            SyncAdapterConfig::put($this->instance->id, $field['key'], $value);
        }
    }

    /**
     * Non-credential operational toggles + defaults + push settings.
     * Grouped here so saveConfig() reads as a list of intent rather
     * than a wall of SyncAdapterConfig::put() calls.
     */
    protected function persistOperationalSettings(Request $request, string $slug): void
    {
        SyncAdapterConfig::put(
            $this->instance->id,
            'log_heartbeats',
            $request->boolean($slug.'_log_heartbeats') ? '1' : '0',
        );

        // Asset-tag pattern for auto-created assets. Empty input clears
        // the setting so we fall back to Asset::autoincrement_asset().
        SyncAdapterConfig::put(
            $this->instance->id,
            'asset_tag_pattern',
            (string) $request->input($slug.'_asset_tag_pattern', ''),
        );

        // Default category for auto-created AssetModels. Empty falls
        // back to the get-or-create "Discovered Hardware" category.
        SyncAdapterConfig::put(
            $this->instance->id,
            'default_category_id',
            (string) $request->input($slug.'_default_category_id', ''),
        );

        // Default status label for auto-created assets. Empty falls
        // back to the first deployable label (or any label if none
        // are marked deployable).
        SyncAdapterConfig::put(
            $this->instance->id,
            'default_status_id',
            (string) $request->input($slug.'_default_status_id', ''),
        );

        // Default fieldset for AssetModels auto-created from this
        // adapter. Empty leaves fieldset_id null on new models (today's
        // behavior). Admins pick a fieldset here so custom fields show
        // on devices discovered by the adapter without having to
        // revisit each new model by hand.
        SyncAdapterConfig::put(
            $this->instance->id,
            'default_fieldset_id',
            (string) $request->input($slug.'_default_fieldset_id', ''),
        );

        // User match strategy for sync-driven assignments. 'none'
        // disables the lookup entirely. Anything else the accessor
        // doesn't recognize also degrades to 'none'.
        SyncAdapterConfig::put(
            $this->instance->id,
            'user_match_strategy',
            (string) $request->input($slug.'_user_match_strategy', 'none'),
        );

        // Suppress notifications checkbox. Stored as '0' when the
        // admin opted OUT of suppression (i.e., wants events to fire).
        // Any other value means suppress. Matches the accessor.
        SyncAdapterConfig::put(
            $this->instance->id,
            'suppress_notifications',
            $request->boolean($slug.'_suppress_notifications') ? '1' : '0',
        );

        // Opt-in: check the asset in when the vendor stops reporting
        // an assigned user (a previously-assigned device that now
        // returns null / empty on the user field). Default off because
        // any single missing sync cycle would flap the assignment.
        // Admins turn it on when they trust their vendor's user
        // reporting to be consistent.
        SyncAdapterConfig::put(
            $this->instance->id,
            'checkin_on_null_user',
            $request->boolean($slug.'_checkin_on_null_user') ? '1' : '0',
        );

        // Migration aid for customers moving from a homegrown sync
        // script that already populated the assets table. When on,
        // provisionAsset() looks up an existing asset by serial
        // before creating a new shell asset, and inserts an
        // asset_external_sources row so subsequent syncs match by
        // external_id. Default off, keeps the safe "vendor host with
        // no known link creates a fresh asset" behavior.
        SyncAdapterConfig::put(
            $this->instance->id,
            'adopt_by_serial',
            $request->boolean($slug.'_adopt_by_serial') ? '1' : '0',
        );

        // Opt-out: skip asset creation on pull when the vendor record
        // has no existing Snipe-IT counterpart.
        SyncAdapterConfig::put(
            $this->instance->id,
            'create_snipeit_assets_on_pull',
            $request->boolean($slug.'_create_snipeit_assets_on_pull') ? '1' : '0',
        );

        // Per-device enrichment opt-in. Only persisted for adapters
        // that declare a perDeviceDetailsToggle(). Default off so
        // large-fleet installs don't eat an extra Graph call per
        // device unless the admin explicitly opts in.
        if ($this->perDeviceDetailsToggle() !== null) {
            SyncAdapterConfig::put(
                $this->instance->id,
                'fetch_per_device_details',
                $request->boolean($slug.'_fetch_per_device_details') ? '1' : '0',
            );
        }

        // Pull dry-run flag. When on, Sync Now still calls pull() so
        // vendor-side fetch, mapping, and normalization all run - but
        // the consumer logs each HostInventoryRecord instead of handing
        // it to syncFromRecord, so no asset is created, updated, or
        // linked. Mirror of push_dry_run on the other direction.
        SyncAdapterConfig::put(
            $this->instance->id,
            'pull_dry_run',
            $request->boolean($slug.'_pull_dry_run') ? '1' : '0',
        );
    }

    /**
     * Per-vendor-group Snipe-IT company mappings. Form emits one input
     * per group (name = {slug}_group_mapping[{vendor_group_id}]).
     * Blank / "unassigned" values clear that group's mapping so it
     * falls back to the instance company_id at sync time.
     */
    private function persistGroupMappings(Request $request, string $slug): void
    {
        foreach ((array) $request->input($slug.'_group_mapping', []) as $vendorGroupId => $companyId) {
            $key = 'group_mapping.'.$vendorGroupId;

            if ($companyId === null || $companyId === '') {
                SyncAdapterConfig::forget($this->instance->id, $key);

                continue;
            }

            SyncAdapterConfig::put($this->instance->id, $key, (string) (int) $companyId);
        }
    }

    /**
     * Standard fields have shipped defaults from MappingTargets. Extras
     * default to skip. Both share the same mapping.{key} storage prefix
     * because the two key namespaces don't collide (standard =
     * hostname/serial/etc, extras = adapter-prefixed).
     *
     * @return array<int, string>
     */
    private function validMappingFields(): array
    {
        return array_merge(
            MappingTargets::FIELDS,
            array_keys($this->extraFields()),
        );
    }

    /**
     * @param  array<int, string>  $validFields
     */
    private function persistFieldMappings(Request $request, string $slug, array $validFields): void
    {
        $posted = (array) $request->input($slug.'_mapping', []);

        // The mapping-picker widget's picker row submits its unfinished
        // pair under <slug>_mapping_pending[key|value] so admins can
        // save without clicking the + button first. Fold it into the
        // posted array before save.
        $pending = (array) $request->input($slug.'_mapping_pending', []);
        $pendingKey = is_string($pending['key'] ?? null) ? trim($pending['key']) : '';
        $pendingValue = is_string($pending['value'] ?? null) ? trim($pending['value']) : '';
        if ($pendingKey !== '' && $pendingValue !== '' && ! array_key_exists($pendingKey, $posted)) {
            $posted[$pendingKey] = $pendingValue;
        }

        foreach ($posted as $field => $target) {
            if (! in_array($field, $validFields, true)) {
                continue;
            }
            SyncAdapterConfig::put($this->instance->id, 'mapping.'.$field, (string) $target);
        }

        // Extras use the mapping-picker widget, which submits only for
        // rows the admin left mapped. Removing a row = the row's key
        // is absent from POST.
        foreach (array_keys($this->extraFields()) as $extraKey) {
            if (! array_key_exists($extraKey, $posted)) {
                SyncAdapterConfig::forget($this->instance->id, 'mapping.'.$extraKey);
            }
        }
    }

    /**
     * Per-field direction (pull / push / skip). Orthogonal to the
     * target column stored above so admins can flip direction without
     * re-picking the target. Only meaningful for adapters that
     * implement PushableSyncAdapter. For pull-only adapters the form
     * doesn't render the direction column, and the default is always pull, and
     * this method is a no-op.
     *
     * @param  array<int, string>  $validFields
     */
    protected function persistFieldDirections(Request $request, string $slug, array $validFields): void
    {
        \Log::debug("This should never be called - there's no such thing as 'direction' for pull-only SyncAdapters");
        // Though, maybe, there is? 'skip' is a direction, and you might use that?
        // TODO - this is copypasta from PushableSyncAdapter, but without it, tests won't pass
        $posted = (array) $request->input($slug.'_direction', []);

        // Widgets' picker rows submit their unfinished pair under
        // <slug>_<map_key>_pending[key|value] so admins can save
        // without clicking + to commit the row first.
        $pairedMapKeys = [$slug.'_mapping_pending'];
        foreach ($this->settingsSchema() as $field) {
            if (($field['type'] ?? '') === 'field_map') {
                $pairedMapKeys[] = $slug.'_'.$field['key'].'_pending';
            }
        }
        $directionPending = (array) $request->input($slug.'_direction_pending', []);
        $pendingDir = is_string($directionPending['value'] ?? null) ? trim($directionPending['value']) : '';
        foreach ($pairedMapKeys as $pendingField) {
            $pairedPending = (array) $request->input($pendingField, []);
            $pendingKey = is_string($pairedPending['key'] ?? null) ? trim($pairedPending['key']) : '';
            $pendingValue = is_string($pairedPending['value'] ?? null) ? trim($pairedPending['value']) : '';
            if ($pendingKey !== '' && $pendingValue !== '' && $pendingDir !== '' && ! array_key_exists($pendingKey, $posted)) {
                $posted[$pendingKey] = $pendingDir;
            }
        }

        // Standard-field pushability gate. Adapters that only know
        // how to push a subset of fields (CustomHttp) coerce
        // push/both on non-pushable standard fields down to pull so
        // we don't persist an unenforceable direction. Extras are
        // defined by the adapter and assumed pushable.
        $pushable = MappingTargets::FIELDS;

        foreach ($posted as $field => $direction) {
            if (! in_array($field, $validFields, true)) {
                continue;
            }
            if (! in_array($direction, ['pull', 'push', 'skip', 'both'], true)) {
                continue;
            }
            if (
                in_array($field, MappingTargets::FIELDS, true)
                && ! in_array($field, $pushable, true)
                && in_array($direction, ['push', 'both'], true)
            ) {
                $direction = 'pull';
            }
            SyncAdapterConfig::put($this->instance->id, 'direction.'.$field, (string) $direction);
        }

        // Same forget-on-omit rule as persistFieldMappings for extras:
        // rows removed via the mapping-picker widget are absent from
        // POST, and their direction needs clearing so it doesn't
        // linger on the next sync if the extra gets re-added.
        foreach (array_keys($this->extraFields()) as $extraKey) {
            if (! array_key_exists($extraKey, $posted)) {
                SyncAdapterConfig::forget($this->instance->id, 'direction.'.$extraKey);
            }
        }
    }

    /**
     * Hook that runs after saveConfig() finishes persisting the form.
     * Empty on the base class. Adapters override to do things like
     * probe the vendor for tier / feature-flag info now that credentials
     * are in place (FleetAdapter caches its license tier here so the
     * group-mapping UI can be hidden on Fleet Free).
     */
    protected function afterSaveConfig(): void
    {
        // no-op by default
    }

    /**
     * Dry-run mode for pull: when true, Sync Now still fetches from
     * the vendor and runs the adapter's normalization, but the pull
     * consumers (PullInventory CLI + SyncAdapterController UI) log
     * each HostInventoryRecord instead of handing it to
     * SyncFromRecord. No asset is created, updated, or linked.
     * Mirror of isPushDryRun() on the other direction. Off by default.
     */
    public function isPullDryRun(): bool
    {
        if ($this->pullDryRunOverride !== null) {
            return $this->pullDryRunOverride;
        }

        return SyncAdapterConfig::get($this->instance->id, 'pull_dry_run') === '1';
    }

    /**
     * Per-request override for the dry-run flags. Set by callers that
     * want to force a dry run without flipping the persisted config
     * (CLI --dry-run flag on snipeit:pull-inventory, UI toggle inside a
     * single click, etc.).
     * Null means "fall back to the persisted config value". Lives on
     * the adapter instance so it's scoped to one adapter object for
     * the current request.
     */
    protected ?bool $pullDryRunOverride = null;

    public function withPullDryRunOverride(?bool $override): static
    {
        $this->pullDryRunOverride = $override;

        return $this;
    }

    // FIXME - do we want to do this differently here versus elsewhere?
    public function directionFor(string $field): string
    {
        $stored = SyncAdapterConfig::get($this->instance->id, 'direction.'.$field);

        return in_array($stored, ['pull', 'push', 'skip', 'both'], true) ? $stored : 'pull';
    }

    /**
     * Whether this adapter's settings form should render the
     * "<Adapter>-specific fields" mapping-picker section. True by
     * default - the picker lets admins route adapter-declared extras
     * to Snipe-IT slots. CustomHttp returns false because its extras
     * are admin-defined via the field_paths widget rather than
     * declared statically by the adapter: the picker there would just
     * duplicate what Vendor Response Paths already captures.
     */
    public function rendersExtrasPicker(): bool
    {
        return true;
    }

    /**
     * Currently-selected target for a field, or the shipped default
     * when no per-instance override exists. Standard fields fall back
     * to MappingTargets::defaultTarget(). extras fall back to skip.
     */
    public function mappingFor(string $field): string
    {
        $default = in_array($field, MappingTargets::FIELDS, true)
            ? MappingTargets::defaultTarget($field)
            : 'skip';

        return SyncAdapterConfig::get(
            $this->instance->id,
            'mapping.'.$field,
            $default,
        );
    }

    public function getUrl(): ?string
    {
        return SyncAdapterConfig::get($this->instance->id, 'url');
    }

    public function logsHeartbeats(): bool
    {
        return SyncAdapterConfig::get($this->instance->id, 'log_heartbeats') === '1';
    }

    /**
     * Custom asset-tag pattern the admin configured for auto-created
     * assets. Null when unset (SyncAdapter falls back to
     * Asset::autoincrement_asset() in that case). Placeholders like
     * `{serial}` / `{external_id}` / `{hostname}` get substituted at
     * create time from HostInventoryRecord fields.
     */
    public function assetTagPattern(): ?string
    {
        $stored = SyncAdapterConfig::get($this->instance->id, 'asset_tag_pattern');

        return $stored === null || $stored === '' ? null : $stored;
    }

    /**
     * Default category id for auto-created AssetModels from this
     * adapter. Vendors don't send Snipe-IT's category concept, but
     * every AssetModel needs one. When null, SyncAdapter
     * falls back to a get-or-create "Discovered Hardware" category.
     */
    public function defaultCategoryId(): ?int
    {
        $stored = SyncAdapterConfig::get($this->instance->id, 'default_category_id');

        return $stored === null || $stored === '' ? null : (int) $stored;
    }

    /**
     * Per-record category override. Adapters that can classify a
     * record's device type (Apple's productFamily, Intune's
     * deviceCategory, etc.) override this to route each record to a
     * more specific category than the instance-wide default.
     *
     * Return null to fall through to defaultCategoryId(). The
     * framework calls this from SyncAdapter when creating a
     * new AssetModel row, so a null return is equivalent to today's
     * one-category-per-instance behavior.
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function categoryIdForRecord(\App\SyncAdapters\HostInventoryRecord $record): ?int
    {
        return null;
    }

    /**
     * Default status label id for auto-created assets from this
     * adapter. When null, SyncAdapter falls back to the
     * first deployable status label (or, absent that, any status
     * label). Admins pick a specific status to route synced devices
     * into a curated workflow bucket ("Awaiting Assignment", "In
     * Production", etc.) rather than lumping them into whatever
     * happens to be the first deployable row.
     */
    public function defaultStatusId(): ?int
    {
        $stored = SyncAdapterConfig::get($this->instance->id, 'default_status_id');

        return $stored === null || $stored === '' ? null : (int) $stored;
    }

    /**
     * Default fieldset for AssetModels auto-created from this adapter.
     * When set, resolveModelIdByName stamps this id on the new model's
     * fieldset_id so custom fields show on synced devices without the
     * admin revisiting each new model after a pull. Null leaves
     * fieldset_id unset (today's behavior for installs that haven't
     * configured this).
     */
    public function defaultFieldsetId(): ?int
    {
        $stored = SyncAdapterConfig::get($this->instance->id, 'default_fieldset_id');

        return $stored === null || $stored === '' ? null : (int) $stored;
    }

    /**
     * How the sync path resolves a Snipe-IT user from the vendor's
     * assigned-user data. Returns one of:
     * - 'none'                 no assignment attempted (default)
     * - 'username'             match against users.username only
     * - 'email'                match against users.email only
     * - 'username_then_email'  try username first, fall back to email
     *
     * Anything unrecognized (or absent) degrades to 'none' so a
     * corrupt row or a future rename can't leak into an unexpected
     * match strategy.
     */
    public function userMatchStrategy(): string
    {
        $stored = SyncAdapterConfig::get($this->instance->id, 'user_match_strategy');

        return in_array($stored, ['email', 'username', 'username_then_email'], true) ? $stored : 'none';
    }

    /**
     * Whether sync-driven user assignments skip the checkout
     * notification path entirely. When true, the sync goes through
     * silentAssignToUser() which bypasses the CheckoutableCheckedOut
     * event and every listener on it (email, Slack, webhook, etc.).
     * Default true so scheduled syncs don't blast users every time
     * their device phones home.
     */
    public function suppressesNotifications(): bool
    {
        $stored = SyncAdapterConfig::get($this->instance->id, 'suppress_notifications');

        // Absent = suppress (safe default). Only an explicit '0'
        // opts back into the standard checkout-event flow.
        return $stored !== '0';
    }

    /**
     * Whether sync should check the asset in when the vendor stops
     * reporting an assigned user. Default false. Turning it on means
     * a device that reported alice@ yesterday and reports null today
     * gets checked in from Alice. Admins opt in when their vendor's
     * user reporting is consistent enough that a null value truly
     * means "unassigned" rather than "field wasn't populated this
     * sync cycle".
     */
    public function checksInOnNullUser(): bool
    {
        return SyncAdapterConfig::get($this->instance->id, 'checkin_on_null_user') === '1';
    }

    /**
     * Migration aid: when on, the sync loop looks for an existing
     * Snipe-IT asset with a matching serial before creating a new
     * shell asset for a first-time sync vendor host.
     */
    public function adoptsBySerial(): bool
    {
        $stored = SyncAdapterConfig::get($this->instance->id, 'adopt_by_serial');
        if ($stored !== null) {
            return $stored === '1';
        }

        return $this->instance->last_synced_at === null;
    }

    /**
     * When off, pull-side vendor records with no `asset_external_sources`
     * link are logged and skipped rather than creating a new Snipe-IT
     * asset. Default on so existing installs behave unchanged. Admins
     * flip it off when their vendor data contains rows that shouldn't
     * become Snipe-IT assets (e.g. Intune devices with generic serials
     * that would duplicate manually-entered rows).
     */
    public function createsSnipeitAssetsOnPull(): bool
    {
        $stored = SyncAdapterConfig::get($this->instance->id, 'create_snipeit_assets_on_pull');

        return $stored === null ? true : $stored === '1';
    }

    /**
     * Whether this adapter's vendor exposes a groups concept
     * (Fleet Teams, Jamf Sites, Kandji Blueprints, etc.) that
     * admins can map to Snipe-IT companies. Default false so
     * adapters without group support don't render the mapping
     * section on the settings page. Adapters override to true and
     * implement fetchGroups() + populate vendorGroupId in normalize.
     */
    public function supportsGroupScoping(): bool
    {
        return false;
    }

    /**
     * Vendor's own terminology for their groups concept. Used in the
     * mapping section header (e.g. "Fleet Team", "Kandji Blueprint",
     * "Jamf Site") so admins see wording they recognize.
     */
    public function vendorGroupLabel(): string
    {
        return 'Group';
    }

    /**
     * Placeholder shown in the base-URL input on the settings page.
     * Overridden by adapters whose vendor uses a well-known URL
     * (SaaS APIs with fixed hostnames or a well-defined subdomain
     * pattern). Adapters targeting self-hosted vendors return null.
     */
    public function baseUrlPlaceholder(): ?string
    {
        return null;
    }

    /**
     * Adapter-specific help copy rendered under the base-URL input.
     * Overridden by adapters whose vendor requires the admin to look
     * up the API URL in a non-obvious place (e.g. Kandji hides the
     * API URL behind the Access page's API Token section). Returning
     * null falls back to the generic base_url_help lang key.
     */
    public function baseUrlHelp(): ?string
    {
        return null;
    }

    /**
     * Live-fetch the current list of groups from the vendor.
     * Returns an array of ['id' => '...', 'label' => '...']. Called
     * when the admin clicks the "refresh groups" button on the
     * settings page. Default throws so adapters that opt into
     * supportsGroupScoping have to implement it. Adapters without
     * group support never have this called.
     *
     * @return array<int, array{id: string, label: string}>
     */
    public function fetchGroups(): array
    {
        throw new \RuntimeException(static::class.' declared group scoping support but did not implement fetchGroups().');
    }

    /**
     * Per-instance vendor-group to Snipe-IT-company mappings, keyed
     * by vendor group id. Empty when the admin has not configured any
     * mappings.
     *
     * @return array<string, int>
     */
    public function groupMappings(): array
    {
        $mappings = [];
        foreach (SyncAdapterConfig::listForInstance($this->instance->id) as $key => $value) {
            if (! str_starts_with($key, 'group_mapping.')) {
                continue;
            }
            $vendorGroupId = substr($key, strlen('group_mapping.'));
            if ($value !== null && $value !== '') {
                $mappings[$vendorGroupId] = (int) $value;
            }
        }

        return $mappings;
    }

    /**
     * Snipe-IT company id for a vendor group id, or null when the
     * group has no mapping. Callers (SyncAdapter) fall back
     * to the instance's own company_id in that case.
     */
    public function companyForVendorGroup(string $vendorGroupId): ?int
    {
        $mappings = $this->groupMappings();

        return $mappings[$vendorGroupId] ?? null;
    }

    /**
     * Cached copy of the last fetchGroups() result, so the settings
     * page renders the mapping table without hitting the vendor on
     * every page load. Refreshed when the admin clicks the button.
     *
     * @return array<int, array{id: string, label: string}>
     */
    public function cachedGroups(): array
    {
        $stored = SyncAdapterConfig::get($this->instance->id, 'cached_groups');
        if ($stored === null || $stored === '') {
            return [];
        }
        $decoded = json_decode($stored, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Whether the adapter's vendor exposes a tenant / site / workspace
     * concept that scopes the pull to one chosen entry. UniFi
     * Network's Site, Jamf School's Location, Microsoft Entra Tenant,
     * etc. Different from supportsGroupScoping(): sites choose ONE
     * entry to pull from, groups map many vendor groups to Snipe-IT
     * companies. Adapters that opt in render Site ID as a select
     * backed by cachedSites() and implement fetchSites().
     */
    public function supportsSiteScoping(): bool
    {
        return false;
    }

    /**
     * Vendor's own terminology for the site concept. Mirrors
     * vendorGroupLabel() so admins see wording they recognize in the
     * refresh button and empty-state copy.
     */
    public function vendorSiteLabel(): string
    {
        return 'Site';
    }

    /**
     * Live-fetch the current list of sites from the vendor. Returns
     * an array of ['id' => '...', 'label' => '...']. Called from the
     * settings page's "refresh sites" button. $overrides lets the
     * caller supply the base URL and credentials from the pending
     * form so a brand-new adapter can discover its sites before the
     * admin saves anything. Keys match the schema field keys the
     * adapter declares (e.g. 'url', 'api_key'). Default throws so
     * adapters that opt into supportsSiteScoping have to implement it.
     *
     * @param  array<string, string|null>  $overrides
     * @return array<int, array{id: string, label: string}>
     */
    public function fetchSites(array $overrides = []): array
    {
        throw new \RuntimeException(static::class.' declared site scoping support but did not implement fetchSites().');
    }

    /**
     * Cached copy of the last fetchSites() result, so the settings
     * page renders the Site ID select without hitting the vendor on
     * every page load.
     *
     * @return array<int, array{id: string, label: string}>
     */
    public function cachedSites(): array
    {
        $stored = SyncAdapterConfig::get($this->instance->id, 'cached_sites');
        if ($stored === null || $stored === '') {
            return [];
        }
        $decoded = json_decode($stored, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Whether the adapter supports fetching a list of vendor-defined
     * custom fields at settings-refresh time. Vendors like Kaseya VSA
     * 10 expose per-tenant custom fields whose names admins can only
     * discover at the tenant level, so extraFields() alone cannot
     * declare them statically. Adapters that opt in override this and
     * implement fetchVendorCustomFields() plus enrichment in pull().
     */
    public function supportsVendorCustomFields(): bool
    {
        return false;
    }

    /**
     * Fetch the current list of custom-field definitions from the
     * vendor. Default is empty. Adapters that opt into
     * supportsVendorCustomFields() must override this to hit the
     * vendor's custom-fields listing endpoint.
     *
     * @return array<int, array{id: string, name: string, type: string}>
     */
    public function fetchVendorCustomFields(): array
    {
        return [];
    }

    /**
     * Return the cached vendor custom fields from the last refresh.
     * Same storage pattern as cachedGroups(): a JSON blob under the
     * `vendor_custom_fields` config key, refreshed when the admin
     * clicks the button. extraFields() reads this back to merge
     * vendor-specific fields into the mapping UI.
     *
     * @return array<int, array{id: string, name: string, type: string}>
     */
    public function cachedVendorCustomFields(): array
    {
        $stored = SyncAdapterConfig::get($this->instance->id, 'vendor_custom_fields');
        if ($stored === null || $stored === '') {
            return [];
        }
        $decoded = json_decode($stored, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function lastSyncedAt(): ?\Carbon\CarbonInterface
    {
        return $this->instance->last_synced_at;
    }

    public function lastSyncResult(): ?string
    {
        return $this->instance->last_sync_result;
    }

    /**
     * Value of a credential for form pre-fill. Returns '' on any
     * failure (missing row, rotated app key, corrupt ciphertext) so
     * blade always has a string to drop into `value=""`. Failed
     * decryption becomes an empty field the admin can re-enter.
     * Loud failures during sync go through credential() instead.
     */
    public function credentialForDisplay(string $key): string
    {
        try {
            return $this->credential($key);
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * Decrypted (or plain, per schema) value of a credential, for
     * pull() implementations building their vendor API client.
     * Throws loudly on any failure so admins see a real error message
     * instead of a silent auth failure on the vendor's side.
     */
    public function credential(string $key): string
    {
        $stored = SyncAdapterConfig::get($this->instance->id, $key);
        if ($stored === null) {
            throw new \RuntimeException(
                'No '.$key.' stored for '.$this->instance->label.'. Save one on the adapters settings page.',
            );
        }

        foreach ($this->settingsSchema() as $field) {
            if ($field['key'] !== $key) {
                continue;
            }

            if (! ($field['secret'] ?? false)) {
                return $stored;
            }

            try {
                return Crypt::decrypt($stored);
            } catch (\Throwable $e) {
                throw new \RuntimeException(
                    'Unable to decrypt '.$key.' for '.$this->instance->label.'. Re-enter it on the adapters settings page.',
                    previous: $e,
                );
            }
        }

        throw new \RuntimeException(
            $key.' is not a declared credential for '.$this->instance->label,
        );
    }

    protected function url(): string
    {
        return SyncAdapterConfig::get($this->instance->id, 'url') ?? '';
    }
}
