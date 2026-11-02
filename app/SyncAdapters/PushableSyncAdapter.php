<?php

namespace App\SyncAdapters;

use App\Models\Asset;
use App\Models\CustomField;
use App\Models\SyncAdapterConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Marker + contract for adapters that support pushing Snipe-IT-owned
 * field values back to the vendor (Snipe-IT asset_tag -> vendor
 * asset_tag, Snipe-IT notes -> vendor notes, etc.). Kept separate
 * from SyncAdapter so pull-only vendors (osctrl, Zentral,
 * UniFi) don't have to stub anything.
 *
 * Adapters that implement this ALSO implement SyncAdapter
 * in practice — the two directions share credentials, base URL, and
 * asset identity (asset_external_sources.external_id). The interfaces
 * stay separate so an adapter can be pull-only, push-only, or both.
 */
abstract class PushableSyncAdapter extends SyncAdapter
{
    /**
     * Runtime check: is push actually available for this instance
     * right now? Adapters return false when a vendor-side gate is
     * in effect (Fleet manual labels are Premium-only, Jamf writes
     * need extra role scopes, etc.) so the settings-page UI hides
     * the push controls instead of letting admins configure a path
     * that will 403 at execution time.
     *
     * Default implementations may return true unconditionally. The
     * check is opt-in per adapter.
     */
    abstract public function canPush(): bool;

    /**
     * Name (or dotted path) of the vendor's single freeform notes
     * field that admins can compose from a template. Return null
     * when the vendor has no such field (or when routing composed
     * notes would be a semantic stretch, e.g. hostname).
     *
     * Push implementations call SyncAdapter::composeNotesForPush()
     * and set the returned ['value'] on the returned ['target']
     * position in the outgoing payload.
     */
    abstract public function notesFieldTarget(): ?string;

    /**
     * Push the currently-configured `push`-direction fields for one
     * asset to the vendor. Implementations are responsible for:
     * - Resolving the vendor-side external_id from the asset's
     *   asset_external_sources row scoped to this adapter's instance
     * - Filtering the field set to those directed 'push' via
     *   SyncAdapter::directionFor()
     * - Serializing each field into the vendor's expected shape
     * - Sending the write call
     *
     * Silent no-op is acceptable when the asset has no external_source
     * row for this instance (never synced from here, so we have no
     * vendor-side id to write against).
     *
     * Return true when the adapter actually reached out to the vendor
     * (or logged a dry-run payload). Return false when the call was
     * skipped for any reason (nothing to push for this asset, no
     * external-source row, empty payload, etc.). The controller's
     * batch runner uses the return value to distinguish "successfully
     * pushed" from "skipped" so the flash message reflects reality
     * instead of counting every silent no-op as a success.
     *
     * @param  array<int, string>  $changedFields
     *                                             Optional list of source-field names (hostname, asset_tag,
     *                                             etc.) that changed and prompted this push. Implementations
     *                                             may use this to skip an API call when no push-direction field
     *                                             is in the changed set. When empty, treat as "push everything
     *                                             marked push-direction".
     */
    abstract public function push(Asset $asset, array $changedFields = []): bool;

    // default implementations for 'push', can be overridden


    public function pushableFields(): array
    {
        return MappingTargets::FIELDS;
    }

    protected function persistOperationalSettings(Request $request, string $slug): void
    {
        parent::persistOperationalSettings($request, $slug);
        // Push dry-run flag. When on, adapters log the push payload
        // instead of sending it, so admins can verify mapping +
        // direction config against real assets without any vendor
        // side effect.
        SyncAdapterConfig::put(
            $this->instance->id,
            'push_dry_run',
            $request->boolean($slug . '_push_dry_run') ? '1' : '0',
        );

        // Push notes template. Blade-ish {placeholder} string that
        // adapters with a notes-shape vendor field (Intune, Kandji,
        // Jamf, etc.) render + push to that field. Blank = don't push
        // a notes field. Empty-string save clears the template.
        SyncAdapterConfig::put(
            $this->instance->id,
            'push_notes_template',
            (string) $request->input($slug . '_push_notes_template', ''),
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
            (string) $request->input($slug . '_push_notes_target', ''),
        );
    }

    /**
     * Per-field direction (pull / push / skip). Orthogonal to the
     * target column stored above so admins can flip direction without
     * re-picking the target. Only meaningful for adapters that
     * implement PushableSyncAdapter. For pull-only adapters the form
     * doesn't render the direction column, and the default is always pull.
     *
     * @param  array<int, string>  $validFields
     */
    protected function persistFieldDirections(Request $request, string $slug, array $validFields): void
    {
        $posted = (array) $request->input($slug . '_direction', []);

        // Widgets' picker rows submit their unfinished pair under
        // <slug>_<map_key>_pending[key|value] so admins can save
        // without clicking + to commit the row first.
        $pairedMapKeys = [$slug . '_mapping_pending'];
        foreach ($this->settingsSchema() as $field) {
            if (($field['type'] ?? '') === 'field_map') {
                $pairedMapKeys[] = $slug . '_' . $field['key'] . '_pending';
            }
        }
        $directionPending = (array) $request->input($slug . '_direction_pending', []);
        $pendingDir = is_string($directionPending['value'] ?? null) ? trim($directionPending['value']) : '';
        foreach ($pairedMapKeys as $pendingField) {
            $pairedPending = (array) $request->input($pendingField, []);
            $pendingKey = is_string($pairedPending['key'] ?? null) ? trim($pairedPending['key']) : '';
            $pendingValue = is_string($pairedPending['value'] ?? null) ? trim($pairedPending['value']) : '';
            if ($pendingKey !== '' && $pendingValue !== '' && $pendingDir !== '' && !array_key_exists($pendingKey, $posted)) {
                $posted[$pendingKey] = $pendingDir;
            }
        }

        // Standard-field pushability gate. Adapters that only know
        // how to push a subset of fields (CustomHttp) coerce
        // push/both on non-pushable standard fields down to pull so
        // we don't persist an unenforceable direction. Extras are
        // defined by the adapter and assumed pushable.
        $pushable = $this->pushableFields();

        foreach ($posted as $field => $direction) {
            if (!in_array($field, $validFields, true)) {
                continue;
            }
            if (!in_array($direction, ['pull', 'push', 'skip', 'both'], true)) {
                continue;
            }
            if (
                in_array($field, MappingTargets::FIELDS, true)
                && !in_array($field, $pushable, true)
                && in_array($direction, ['push', 'both'], true)
            ) {
                $direction = 'pull';
            }
            SyncAdapterConfig::put($this->instance->id, 'direction.' . $field, (string) $direction);
        }

        // Same forget-on-omit rule as persistFieldMappings for extras:
        // rows removed via the mapping-picker widget are absent from
        // POST, and their direction needs clearing so it doesn't
        // linger on the next sync if the extra gets re-added.
        foreach (array_keys($this->extraFields()) as $extraKey) {
            if (!array_key_exists($extraKey, $posted)) {
                SyncAdapterConfig::forget($this->instance->id, 'direction.' . $extraKey);
            }
        }
    }


    /**
     * Dry-run mode for push: when true, PushableSyncAdapter::push()
     * implementations should build the payload and log it via the
     * sync-adapters channel but skip the actual vendor API call.
     * Lets admins verify their mapping + direction configuration
     * end-to-end without touching the vendor. Off by default.
     */
    public function isPushDryRun(): bool
    {
        if ($this->pushDryRunOverride !== null) {
            return $this->pushDryRunOverride;
        }

        return SyncAdapterConfig::get($this->instance->id, 'push_dry_run') === '1';
    }

    /**
     * Per-request override for the dry-run flags. Set by callers that
     * want to force a dry run without flipping the persisted config
     * (CLI --dry-run flag on snipeit:push-inventory, UI toggle inside
     * a single click, etc.).
     * Null means "fall back to the persisted config value". Lives on
     * the adapter instance so it's scoped to one adapter object for
     * the current request.
     */
    protected ?bool $pushDryRunOverride = null;

    public function withPushDryRunOverride(?bool $override): static
    {
        $this->pushDryRunOverride = $override;

        return $this;
    }

    /**
     * Valid placeholder keys for the composed-notes push template.
     * Admins wrap these tokens in {curly-braces} inside their
     * push_notes_template to build a single-field notes blob from
     * many Snipe-IT fields. `{custom.Field Name}` accesses any
     * CustomField by its exact display name (case-sensitive).
     * Unknown placeholders and null values render as empty so a
     * template referencing missing fields degrades gracefully.
     *
     * Admin-facing descriptions of each placeholder live in the
     * `push_notes_template_help` translation, not here, so the
     * catalog stays translator-friendly.
     *
     * @var array<int, string>
     */
    public const NOTES_TEMPLATE_PLACEHOLDERS = [
        'asset_tag',
        'name',
        'serial',
        'model',
        'model_number',
        'manufacturer',
        'category',
        'status',
        'status_type',
        'assigned_to',
        'assigned_to_email',
        'assigned_to_username',
        'location',
        'company',
        'supplier',
        'last_checkout',
        'last_checkin',
        'expected_checkin',
        'notes',
        'order_number',
        'purchase_date',
        'purchase_cost',
        'warranty_months',
        'warranty_expires',
    ];

    public function pushDirectedFields(): array
    {
        $out = [];
        $candidates = array_merge(MappingTargets::FIELDS, array_keys($this->extraFields()));
        foreach ($candidates as $field) {
            // Standard-field target "Skip (do not sync)" wins over
            // whatever the paired direction dropdown says - the field
            // is explicitly opted out of syncing in either direction.
            // The direction value may still be 'push' in the stored
            // config from before the target was flipped to skip, but
            // we treat the field as excluded from the push set
            // regardless. Also protects against stale state on
            // direction=push + mapping=skip combos the UI used to be
            // able to produce.
            //
            // Extras skip this gate because their mapping default is
            // 'skip' (no stored row = "not configured, not synced")
            // and their direction toggle is the only control the
            // admin has to pick push on them.
            if (in_array($field, MappingTargets::FIELDS, true) && $this->mappingFor($field) === 'skip') {
                continue;
            }
            if (in_array($this->directionFor($field), ['push', 'both'], true)) {
                $out[] = $field;
            }
        }

        return $out;
    }


    /**
     * Resolve one of the standard source-field names (hostname,
     * asset_tag, serial, model, notes, etc.) to the matching value
     * on the asset. Push implementations call this when building
     * their outgoing payload so every adapter reads Snipe-IT values
     * the same way. Returns null for unknown source-field names,
     * for extras (vendor-specific keys the base class can't map),
     * or when the asset has no value for that field.
     *
     * Adapters with a vendor-specific extra that needs pushing
     * override in the subclass, call parent for the standard fields,
     * and handle the vendor-specific keys in the child branch.
     */
    protected function assetValueForSourceField(Asset $asset, string $field): mixed
    {
        // Native fast-path: the four fields every adapter can push
        // without consulting a mapping target. Pre-dates the mapping-
        // target resolver below so adapters that call this with
        // non-mapped fields (test harnesses, future callers) still get
        // a sensible answer.
        $native = match ($field) {
            'hostname', 'name' => $asset->name,
            'serial' => $asset->serial,
            'asset_tag' => $asset->asset_tag,
            'model' => $asset->model?->name,
            'notes' => $asset->notes,
            default => null,
        };
        if ($native !== null) {
            return $native;
        }

        // For other source fields (os, os_version, last_seen, mac,
        // custom:X, etc.) consult the per-field mapping target so the
        // middleman push scenario (pull from vendor A, push to vendor
        // B) can actually surface the stored value. The mapping is
        // the admin's choice of "where in Snipe-IT does this field
        // live" - we read from that slot.
        $target = $this->mappingFor($field);
        if ($target === '' || $target === 'skip') {
            return null;
        }

        if (str_starts_with($target, 'custom:')) {
            $customFieldId = (int) substr($target, 7);
            $customField = CustomField::find($customFieldId);
            if ($customField === null || $customField->db_column === null) {
                return null;
            }

            return $asset->getAttribute($customField->db_column);
        }

        if (str_starts_with($target, 'external:')) {
            $column = substr($target, 9);
            $row = \Illuminate\Support\Facades\DB::table('asset_external_sources')
                ->where('asset_id', $asset->id)
                ->where('source', $this->name())
                ->first();

            return $row->{$column} ?? null;
        }

        if (str_starts_with($target, 'native:')) {
            // Direct read from the asset column named after the
            // target. The sync writers use the same convention, so
            // native:foo stored by pull is readable as $asset->foo.
            $column = substr($target, 7);

            return $asset->getAttribute($column);
        }

        return null;
    }

    /**
     * Standard push() implementation for single-endpoint adapters. An
     * adapter whose push() writes one payload to one endpoint (Kandji,
     * Jamf, NinjaOne) overrides two hooks (buildPushPayload,
     * dispatchPush) and delegates push() to this template.
     *
     * Adapters with split-endpoint pushes (Mosyle posts asset_tag and
     * notes to separate operations, Workspace ONE splits device fields
     * from Custom Attributes) implement push() directly and don't call
     * this. CustomHttp also implements push() directly because its
     * dry-run log and dispatch depend on admin-configured HTTP method
     * and endpoint that the template can't parameterize.
     *
     * Success and dry-run log lines use typeLabel() plus the flat
     * $touched list (falling back to array_keys($payload) when the
     * adapter builds a flat-keyed payload and doesn't populate
     * $touched). Adapters wanting vendor-specific log wording override
     * push() directly instead of using the template.
     *
     * @param  array<int, string>  $changedFields
     */
    protected function pushViaSinglePayload(Asset $asset, array $changedFields): bool
    {
        $externalSource = $this->pushPrologue($asset, $changedFields);
        if ($externalSource === null) {
            return false;
        }

        [$payload, $touched] = $this->buildPushPayload($asset);
        $this->applyComposedNotesToPayload($asset, $payload, $touched);

        if ($payload === []) {
            return false;
        }

        $written = $touched !== [] ? $touched : array_keys($payload);

        if ($this->isPushDryRun()) {
            Log::channel('sync-adapters')->info(sprintf(
                '%s push [dry-run]: would send %s to %s device %s',
                $this->name(),
                json_encode($payload, JSON_UNESCAPED_SLASHES),
                static::typeLabel(),
                $externalSource->external_id,
            ));

            return true;
        }

        $this->dispatchPush($externalSource, $payload);

        Log::channel('sync-adapters')->info(sprintf(
            '%s push: updated %s device %s [%s]',
            $this->name(),
            static::typeLabel(),
            $externalSource->external_id,
            implode(', ', $written),
        ));

        return true;
    }

    /**
     * Build the outgoing vendor payload for pushViaSinglePayload().
     * Adapters that call pushViaSinglePayload() override this to
     * return their payload and the flat list of dot-paths / keys that
     * were written (for the post-push audit log). Empty payload signals
     * "nothing to send" and the template short-circuits.
     *
     * @return array{0: array<string, mixed>, 1: array<int, string>}
     */
    protected function buildPushPayload(Asset $asset): array
    {
        throw new \LogicException(static::class . ' calls pushViaSinglePayload() but does not override buildPushPayload().');
    }

    /**
     * Fire the vendor HTTP call for pushViaSinglePayload(). Adapters
     * that call pushViaSinglePayload() override this. Failures throw
     * (the outer controller catches and renders a red-flash summary,
     * matching the pull path). No return value: presence of the call
     * is enough, the template logs success on its own.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function dispatchPush(\App\Models\AssetExternalSource $externalSource, array $payload): void
    {
        throw new \LogicException(static::class . ' calls pushViaSinglePayload() but does not override dispatchPush().');
    }

    /**
     * Shared prologue for every PushableSyncAdapter::push() implementation.
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
    public function pushPrologue(Asset $asset, array $changedFields): ?\App\Models\AssetExternalSource
    {
        $pushFields = $this->pushDirectedFields();
        $notesConfigured = $this->hasComposedNotesConfigured();

        if ($pushFields === [] && !$notesConfigured) {
            // Nothing directed push and no composed-notes template.
            // Logged at info because this is a config-shape issue
            // the admin should see in the per-run log + file log
            // instead of just "0 pushed, N skipped" with no reason.
            Log::channel('sync-adapters')->info(sprintf(
                '%s push: skipped asset %d (asset_tag=%s) - no push-directed fields and no composed-notes template configured',
                $this->name(),
                $asset->id,
                $asset->asset_tag ?? '(none)',
            ));

            return null;
        }

        if ($pushFields !== [] && $changedFields !== []
            && array_intersect($changedFields, $pushFields) === []) {
            Log::channel('sync-adapters')->info(sprintf(
                '%s push: skipped asset %d (asset_tag=%s) - changed fields [%s] do not intersect push-directed fields [%s]',
                $this->name(),
                $asset->id,
                $asset->asset_tag ?? '(none)',
                implode(', ', $changedFields),
                implode(', ', $pushFields),
            ));

            return null;
        }

        $externalSource = \App\Models\AssetExternalSource::query()
            ->where('asset_id', $asset->id)
            ->where('source', $this->name())
            ->first();

        if ($externalSource === null) {
            Log::channel('sync-adapters')->info(sprintf(
                '%s push: skipped asset %d (asset_tag=%s) - no asset_external_sources row for this adapter instance',
                $this->name(),
                $asset->id,
                $asset->asset_tag ?? '(none)',
            ));
        }

        return $externalSource;
    }

    /**
     * Composed notes payload for a push, or null when nothing to
     * push. Reads the admin's push_notes_template from config,
     * resolves the target vendor field (admin override or
     * PushableSyncAdapter::notesFieldTarget() default), then substitutes
     * every {placeholder} against the given asset.
     *
     * Placeholders are enumerated in NOTES_TEMPLATE_PLACEHOLDERS.
     * `{custom.Field Name}` resolves any CustomField by exact
     * display name. Unknown keys and null values render as empty,
     * so a template referencing missing fields degrades gracefully
     * instead of throwing.
     *
     * Adapter push() implementations call this and check for null:
     * non-null returns give both the vendor field name (`target`)
     * and the rendered string (`value`), so the caller can splice
     * both into its payload in one hit.
     *
     * @return array{target: string, value: string}|null
     */
    public function composeNotesForPush(Asset $asset): ?array
    {
        $template = (string) SyncAdapterConfig::get($this->instance->id, 'push_notes_template');
        if (trim($template) === '') {
            // Blank template is the common "feature not in use" case,
            // so log at debug - skipping it on every asset push would
            // just noise up the file log on fleets that haven't set
            // up composed notes.
            Log::channel('sync-adapters')->debug(sprintf(
                '%s push: asset %d (asset_tag=%s) - composed notes not applied: push_notes_template is blank',
                $this->name(),
                $asset->id,
                $asset->asset_tag ?? '(none)',
            ));

            return null;
        }

        $target = $this->resolveComposedNotesTarget();
        if ($target === null) {
            // Template is set but target field path is not. This IS
            // worth surfacing at info - the admin wrote a template
            // expecting it to be pushed somewhere and the destination
            // config is missing.
            Log::channel('sync-adapters')->info(sprintf(
                '%s push: asset %d (asset_tag=%s) - composed notes not applied: push_notes_template is set but no target field is configured (push_notes_target)',
                $this->name(),
                $asset->id,
                $asset->asset_tag ?? '(none)',
            ));

            return null;
        }

        // Match anything between braces that isn't itself a brace.
        // Permissive so custom-field names with spaces or punctuation
        // ({custom.Cost Center}, {custom.Warranty (extended)}) match
        // cleanly. The inner match block below decides validity.
        $value = preg_replace_callback(
            '/\{([^{}]+)\}/',
            function (array $m) use ($asset): string {
                $key = $m[1];

                if (str_starts_with($key, 'custom.')) {
                    $field = CustomField::query()->where('name', substr($key, 7))->first();
                    if ($field === null || $field->db_column === null) {
                        return '';
                    }
                    $stored = $asset->getAttribute($field->db_column);

                    return $stored === null ? '' : (string) $stored;
                }

                return match ($key) {
                    'asset_tag' => (string) ($asset->asset_tag ?? ''),
                    'name' => (string) ($asset->name ?? ''),
                    'serial' => (string) ($asset->serial ?? ''),
                    'model' => (string) ($asset->model->name ?? ''),
                    'model_number' => (string) ($asset->model->model_number ?? ''),
                    'manufacturer' => (string) ($asset->model->manufacturer->name ?? ''),
                    'category' => (string) ($asset->model->category->name ?? ''),
                    'status' => (string) ($asset->status->name ?? ''),
                    'status_type' => (string) ($asset->status?->getStatuslabelType() ?? ''),
                    'assigned_to' => (string) ($asset->assignedTo?->present()?->name() ?? ''),
                    'assigned_to_email' => (string) ($asset->assignedTo->email ?? ''),
                    'assigned_to_username' => (string) ($asset->assignedTo->username ?? ''),
                    'location' => (string) ($asset->location->name ?? ''),
                    'company' => (string) ($asset->company->name ?? ''),
                    'supplier' => (string) ($asset->supplier->name ?? ''),
                    'last_checkout' => (string) ($asset->last_checkout ?? ''),
                    'last_checkin' => (string) ($asset->last_checkin ?? ''),
                    'expected_checkin' => (string) ($asset->expected_checkin ?? ''),
                    'notes' => (string) ($asset->notes ?? ''),
                    'order_number' => (string) ($asset->order_number ?? ''),
                    'purchase_date' => (string) ($asset->purchase_date ?? ''),
                    'purchase_cost' => (string) ($asset->purchase_cost ?? ''),
                    'warranty_months' => (string) ($asset->warranty_months ?? ''),
                    'warranty_expires' => (string) ($asset->warranty_expires ?? ''),
                    default => '',
                };
            },
            $template,
        ) ?? '';

        return ['target' => $target, 'value' => $value];
    }

    /**
     * Splice the composed-notes payload (from composeNotesForPush)
     * into an outgoing vendor payload. Returns true when composed
     * notes were configured and spliced, false when there was
     * nothing to send. Callers that track which fields they pushed
     * (for post-push logging or event dispatch) pass their $touched
     * array by reference. The target field name gets appended to
     * it on success.
     *
     * Uses Arr::set so dotted target paths (Jamf's "general.notes"
     * shape) branch into nested payload structures rather than
     * writing a literal key with a dot in it.
     *
     * @param  array<mixed>  $payload
     * @param  array<int, string>  $touched
     */
    public function applyComposedNotesToPayload(Asset $asset, array &$payload, array &$touched = []): bool
    {
        $composedNotes = $this->composeNotesForPush($asset);
        if ($composedNotes === null) {
            return false;
        }

        \Illuminate\Support\Arr::set($payload, $composedNotes['target'], $composedNotes['value']);
        $touched[] = $composedNotes['target'];

        return true;
    }

    /**
     * Stored composed-notes template + target-override, exposed for
     * the settings-page form so it can pre-fill the two inputs.
     * Runtime callers use composeNotesForPush() instead, which
     * resolves the effective target (falling back to
     * PushableSyncAdapter::notesFieldTarget()) and does the substitution
     * in one hit.
     *
     * @return array{template: string, target_override: ?string}
     */
    public function storedPushNotesConfig(): array
    {
        $template = (string) SyncAdapterConfig::get($this->instance->id, 'push_notes_template');
        $target = (string) SyncAdapterConfig::get($this->instance->id, 'push_notes_target');

        return [
            'template' => $template,
            'target_override' => $target === '' ? null : $target,
        ];
    }

    /**
     * True when this instance has ANY push configuration in place
     * (at least one field directed 'push' OR a composed-notes
     * template + target). Used by the controller's Push Now handler
     * to abort with a clear error before iterating asset rows when
     * the admin hit the button on a fresh or incorrectly-configured
     * adapter, instead of iterating N rows that each silent-no-op
     * and reporting an inaccurate success.
     */
    public function hasPushConfiguration(): bool
    {
        return $this->pushDirectedFields() !== [] || $this->hasComposedNotesConfigured();
    }

    /**
     * Cheap "should we bother trying to push composed notes?" check
     * used by pushPrologue to gate the vendor call without paying
     * the cost of rendering the template. True when the admin has a
     * non-blank push_notes_template AND a resolvable target field.
     */
    private function hasComposedNotesConfigured(): bool
    {
        $template = (string) SyncAdapterConfig::get($this->instance->id, 'push_notes_template');

        return trim($template) !== '' && $this->resolveComposedNotesTarget() !== null;
    }

    /**
     * Vendor field name for the composed-notes push. Admin's
     * override (push_notes_target config key) wins if set. Falls
     * back to the adapter's suggested default via
     * PushableSyncAdapter::notesFieldTarget(). Null when neither is
     * set (adapter has no notes concept AND admin didn't name a
     * target field).
     */
    private function resolveComposedNotesTarget(): ?string
    {
        $stored = (string) SyncAdapterConfig::get($this->instance->id, 'push_notes_target');
        if ($stored !== '') {
            return $stored;
        }

        return $this->notesFieldTarget();
    }

    /**
     * Whether the composed-notes push UI should appear on this
     * adapter's settings page. Default is "yes if the adapter is
     * pushable at all" so existing adapters stay unchanged. Adapters
     * targeting APIs that have no notes-like field anywhere in their
     * push payload (Mosyle Manager v2's push elements, for example)
     * override this to false so admins don't fill in a template that
     * can never land. notesFieldTarget() returning null doesn't carry
     * this meaning (Workspace ONE / NinjaOne return null legitimately
     * because the admin picks the target).
     */
    public function supportsComposedNotesPush(): bool
    {
        return $this->canPush();
    }

}
