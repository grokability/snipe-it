<?php

namespace App\SyncAdapters\Support;

use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\Rules\ExternalUrl;
use App\SyncAdapters\HostInventoryAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

/**
 * The single base class every host-inventory adapter extends. Owns
 * every piece of behavior that's the same across adapters regardless
 * of auth shape: URL storage with SSRF-guarded validation, encrypted
 * secret storage, blank-preserves-existing save semantics, active +
 * heartbeat toggles, per-field target mapping, isEnabled().
 *
 * The one thing subclasses declare is their credentialSchema(): a
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
abstract class ConfigurableAdapter implements HostInventoryAdapter
{
    public function __construct(protected readonly SyncAdapterInstance $instance) {}

    /** Display label for the adapter TYPE (not the instance). Shown in the "Add adapter" dropdown. */
    abstract public static function typeLabel(): string;

    /** Vendor-specific inventory fetch. Yield HostInventoryRecord objects. */
    abstract public function pull(): iterable;

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
    abstract public function credentialSchema(): array;

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
        return [];
    }

    public function name(): string
    {
        return $this->instance->slug;
    }

    public function label(): string
    {
        return $this->instance->label;
    }

    public function isActive(): bool
    {
        return $this->instance->active;
    }

    public function isBuiltIn(): bool
    {
        return $this->instance->built_in;
    }

    public function isEnabled(): bool
    {
        if (! $this->instance->active) {
            return false;
        }

        if ($this->usesConfigurableUrl() && empty(SyncAdapterConfig::get($this->instance->id, 'url'))) {
            return false;
        }

        foreach ($this->credentialSchema() as $field) {
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

    public function settingsView(): string
    {
        return 'settings.adapters.adapter_credentials';
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
            $slug.'_asset_tag_pattern' => ['nullable', 'string', 'max:191'],
            $slug.'_default_category_id' => ['required', 'integer', 'exists:categories,id'],
            $slug.'_default_status_id' => ['required', 'integer', 'exists:status_labels,id'],
            $slug.'_user_match_strategy' => ['nullable', 'string', 'in:none,email,username,username_then_email'],
        ];

        if ($this->usesConfigurableUrl()) {
            $rules[$slug.'_url'] = ['required', new ExternalUrl];
        }

        foreach ($this->credentialSchema() as $field) {
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

        $validFields = $this->validMappingFields();
        $this->persistFieldMappings($request, $slug, $validFields);
        $this->persistFieldDirections($request, $slug, $validFields);

        $this->afterSaveConfig();
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
        foreach ($this->credentialSchema() as $field) {
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
    private function persistOperationalSettings(Request $request, string $slug): void
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

        // Push dry-run flag. When on, adapters log the push payload
        // instead of sending it, so admins can verify mapping +
        // direction config against real assets without any vendor
        // side effect.
        SyncAdapterConfig::put(
            $this->instance->id,
            'push_dry_run',
            $request->boolean($slug.'_push_dry_run') ? '1' : '0',
        );

        // Push notes template. Blade-ish {placeholder} string that
        // adapters with a notes-shape vendor field (Intune, Kandji,
        // Jamf, etc.) render + push to that field. Blank = don't push
        // a notes field. Empty-string save clears the template.
        SyncAdapterConfig::put(
            $this->instance->id,
            'push_notes_template',
            (string) $request->input($slug.'_push_notes_template', ''),
        );

        // Optional override for which vendor field the composed
        // notes get pushed to. Blank = use the adapter's default
        // (Kandji: notes, Jamf: general.notes, Intune: notes, etc.).
        // Admin sets this when their vendor has multiple candidate
        // fields (Kandji's `notes` vs a specific custom field, WS1's
        // Custom Attribute name, Ninja's Custom Field name).
        SyncAdapterConfig::put(
            $this->instance->id,
            'push_notes_target',
            (string) $request->input($slug.'_push_notes_target', ''),
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
        foreach ((array) $request->input($slug.'_mapping', []) as $field => $target) {
            if (! in_array($field, $validFields, true)) {
                continue;
            }
            SyncAdapterConfig::put($this->instance->id, 'mapping.'.$field, (string) $target);
        }
    }

    /**
     * Per-field direction (pull / push / skip). Orthogonal to the
     * target column stored above so admins can flip direction without
     * re-picking the target. Only meaningful for adapters that
     * implement PushableAdapter. For pull-only adapters the form
     * doesn't render the direction column and this loop is a no-op.
     * Value 'pull' is the default and doesn't need storing, but writing
     * it explicitly keeps the round-trip behavior predictable for
     * admins reading raw config.
     *
     * @param  array<int, string>  $validFields
     */
    private function persistFieldDirections(Request $request, string $slug, array $validFields): void
    {
        foreach ((array) $request->input($slug.'_direction', []) as $field => $direction) {
            if (! in_array($field, $validFields, true)) {
                continue;
            }
            if (! in_array($direction, ['pull', 'push', 'skip', 'both'], true)) {
                continue;
            }
            SyncAdapterConfig::put($this->instance->id, 'direction.'.$field, (string) $direction);
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
     * Per-field direction for the sync mapping. Returns one of:
     * - 'pull' (vendor -> Snipe-IT, default)
     * - 'push' (Snipe-IT -> vendor)
     * - 'skip' (no sync in either direction)
     *
     * Only meaningful for adapters implementing PushableAdapter. For
     * pure pull adapters callers should always get 'pull' back.
     */
    /**
     * Dry-run mode for push: when true, PushableAdapter::push()
     * implementations should build the payload and log it via the
     * sync-adapters channel but skip the actual vendor API call.
     * Lets admins verify their mapping + direction configuration
     * end-to-end without touching the vendor. Off by default.
     */
    public function isPushDryRun(): bool
    {
        return SyncAdapterConfig::get($this->instance->id, 'push_dry_run') === '1';
    }

    /**
     * Template string admins configure to compose a single-field
     * notes blob from many Snipe-IT fields (asset_tag + status +
     * assigned_to + custom fields, etc.). PushableAdapter
     * implementations render this via NotesComposer and push the
     * result to their vendor's notes-shape field. Empty string
     * means "don't push notes."
     */
    public function pushNotesTemplate(): string
    {
        return (string) SyncAdapterConfig::get($this->instance->id, 'push_notes_template');
    }

    /**
     * Admin-configured vendor field name (or dotted path) for the
     * composed notes push. Blank = fall back to the adapter's
     * suggested default via PushableAdapter::notesFieldTarget().
     * Set this when the vendor exposes multiple candidate targets
     * (Custom Attribute name for WS1, Custom Field name for
     * NinjaOne, alternative path for Jamf, etc.).
     */
    public function pushNotesTargetOverride(): ?string
    {
        $stored = (string) SyncAdapterConfig::get($this->instance->id, 'push_notes_target');

        return $stored === '' ? null : $stored;
    }

    /**
     * Effective vendor field for composed notes: the admin's
     * override if set, else the adapter's suggested default via
     * PushableAdapter::notesFieldTarget(). Returns null when
     * neither is set (adapter doesn't expose a notes concept AND
     * admin didn't name a target field).
     */
    public function effectiveNotesTarget(): ?string
    {
        $override = $this->pushNotesTargetOverride();
        if ($override !== null) {
            return $override;
        }

        return $this instanceof \App\SyncAdapters\PushableAdapter
            ? $this->notesFieldTarget()
            : null;
    }

    public function directionFor(string $field): string
    {
        $stored = SyncAdapterConfig::get($this->instance->id, 'direction.'.$field);

        return in_array($stored, ['pull', 'push', 'skip', 'both'], true) ? $stored : 'pull';
    }

    /**
     * Enumeration of source-field names currently directed 'push' or
     * 'both' on this instance. Used by push() implementations to
     * build the PATCH payload without scanning every possible field.
     *
     * @return array<int, string>
     */
    public function pushDirectedFields(): array
    {
        $out = [];
        $candidates = array_merge(MappingTargets::FIELDS, array_keys($this->extraFields()));
        foreach ($candidates as $field) {
            if (in_array($this->directionFor($field), ['push', 'both'], true)) {
                $out[] = $field;
            }
        }

        return $out;
    }

    /**
     * Shared prologue for every PushableAdapter::push() implementation.
     * Runs the three identical guards each adapter's push() started with
     * and returns the AssetExternalSource row when the push should
     * proceed, or null when any guard rejects it. Callers early-return
     * on null.
     *
     * The three guards, in order:
     *  1. pushDirectedFields is empty AND no composed-notes template is
     *     configured (nothing to send, skip).
     *  2. Caller-provided $changedFields is non-empty but doesn't
     *     intersect pushDirectedFields (the caller told us which
     *     fields changed and none of them are push-directed on this
     *     instance, so skip the vendor call).
     *  3. asset_external_sources row missing (the asset was never
     *     synced from this instance, so we have no vendor device id
     *     to write against).
     *
     * @param  array<int, string>  $changedFields
     */
    public function pushPrologue(\App\Models\Asset $asset, array $changedFields): ?\App\Models\AssetExternalSource
    {
        $pushFields = $this->pushDirectedFields();
        $notesConfigured = $this->pushNotesTemplate() !== '' && $this->effectiveNotesTarget() !== null;

        if ($pushFields === [] && ! $notesConfigured) {
            return null;
        }

        if ($pushFields !== [] && $changedFields !== []
            && array_intersect($changedFields, $pushFields) === []) {
            return null;
        }

        return \App\Models\AssetExternalSource::query()
            ->where('asset_id', $asset->id)
            ->where('source', $this->name())
            ->first();
    }

    /**
     * Render the composed-notes template against the given asset via
     * NotesComposer, or return an empty string when the template or
     * target isn't configured. Adapters call this and check for '' to
     * decide whether to include composed notes in their push payload.
     */
    public function composeNotesForPush(\App\Models\Asset $asset): string
    {
        $template = $this->pushNotesTemplate();
        $target = $this->effectiveNotesTarget();
        if ($template === '' || $target === null) {
            return '';
        }

        return \App\SyncAdapters\Support\NotesComposer::compose($asset, $template);
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
     * assets. Null when unset (SyncHostFromAdapter falls back to
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
     * every AssetModel needs one. When null, SyncHostFromAdapter
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
     * framework calls this from SyncHostFromAdapter when creating a
     * new AssetModel row, so a null return is equivalent to today's
     * one-category-per-instance behavior.
     */
    public function categoryIdForRecord(\App\SyncAdapters\HostInventoryRecord $record): ?int
    {
        return null;
    }

    /**
     * Default status label id for auto-created assets from this
     * adapter. When null, SyncHostFromAdapter falls back to the
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
     * group has no mapping. Callers (SyncHostFromAdapter) fall back
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

        foreach ($this->credentialSchema() as $field) {
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
