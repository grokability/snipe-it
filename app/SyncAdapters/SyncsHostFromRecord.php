<?php

namespace App\SyncAdapters;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\CustomField;
use App\Models\Manufacturer;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Shared asset-side upsert path for every host-inventory adapter,
 * folded onto SyncAdapter via `use SyncsHostFromRecord;` so the entry
 * point sits on the base class. Accepts a normalized
 * HostInventoryRecord and either creates a new asset or updates an
 * existing one keyed on (source, external_id) in
 * asset_external_sources.
 *
 * The write path is deliberately dumb about vendors: no adapter-
 * specific logic lives here. Adapters do their own normalization and
 * hand a clean record over. Per-instance target mappings (configured
 * via MappingTargets on the settings page) let admins redirect each
 * normalized field to a custom field or skip it entirely. This trait
 * reads those overrides and dispatches values accordingly.
 */
trait SyncsHostFromRecord
{
    public static function syncFromRecord(HostInventoryRecord $record): ?Asset
    {
        return DB::transaction(function () use ($record) {
            $instance = SyncAdapterInstance::query()->where('slug', $record->sourceKey)->first();
            $mapping = self::loadMapping($instance);

            $provisioned = self::provisionAsset($record, $instance, $mapping);
            if ($provisioned === null) {
                return null;
            }
            [$asset, $isNew] = $provisioned;

            $externalUpdates = [];
            self::applyStandardFieldMappings($asset, $externalUpdates, $record, $instance, $mapping);
            self::applyExtraFieldMappings($asset, $record, $instance, $mapping);

            $previousExternal = $isNew ? [] : self::loadExternalSourceInventory($asset->id, $record->sourceKey);
            $wasDirty = $asset->isDirty();

            // Heartbeat-only asset-side write: Skip logging when heartbeat-logging is turned off,
            // when the mapping directs last_seen to a custom field (target custom:X).
            // Otherwise the vendor's heartbeat lands on the asset and fires AssetObserver::updating
            // on save, which writes an `update` Actionlog.
            if (! $isNew && self::isHeartbeatOnlyAssetDirty($asset, $mapping, $instance)) {
                if (! $asset->saveQuietly()) {
                    throw new RuntimeException('Failed to save asset during heartbeat-only sync.');
                }
            } else {
                // Asset::save() augments $this->rules with the model's
                // fieldset validation rules INSIDE the save call, so
                // Watson's saveOrFail() upfront isInvalid() check runs
                // before the augmentation and passes. It then delegates
                // to Eloquent's saveOrFail() which wraps save() in a
                // transaction WITHOUT re-checking the return value, so
                // when the ValidatingObserver aborts the save (returns
                // false) the whole chain silently returns false and the
                // sync counts the record as processed. Calling save()
                // directly and checking the boolean mirrors
                // createShellAsset()'s existing guard and surfaces the
                // real validation error to the pull loop's catch.
                if (! $asset->save()) {
                    $errors = implode('; ', $asset->getErrors()->all()) ?: 'unknown validation failure';
                    throw new RuntimeException("could not update asset for {$record->sourceKey} record {$record->sourceId}: {$errors}");
                }
            }

            // External-source column writes gather during the mapping
            // loop and go out together so we make one row-write per
            // sync instead of N updates.
            if ($externalUpdates !== []) {
                self::upsertExternalSource($asset, $record->sourceKey, $externalUpdates);
            }

            // Sync-driven user assignment. Runs after saveOrFail so
            // the asset is committed before we try to check it out.
            // Only touches assets whose adapter has a user-match
            // strategy configured. missing or unresolved users skip
            // silently after logging a warning.
            self::assignUserIfMatched($asset, $record, $instance);

            if (! $isNew) {
                self::logExternalSourceChanges($asset, $previousExternal, $externalUpdates, $wasDirty, $instance);
            }

            return $asset->refresh();
        });
    }

    /**
     * Resolve the asset for this sync record. Either finds it by the
     * existing (source, external_id) tuple in asset_external_sources
     * or creates a fresh shell asset + identity row for first-sync
     * cases. Also handles the update-sync group-reassignment case
     * where a device moved to a different vendor group that maps to
     * a different Snipe-IT company. Returns null when the record was
     * intentionally skipped (no match + create-on-pull disabled).
     *
     * @param  array<string, string>  $mapping
     * @return array{0: Asset, 1: bool}|null
     */
    private static function provisionAsset(HostInventoryRecord $record, ?SyncAdapterInstance $instance, array $mapping = []): ?array
    {
        $existingSource = DB::table('asset_external_sources')
            ->where('source', $record->sourceKey)
            ->where('external_id', $record->sourceId)
            ->first();

        $asset = $existingSource ? Asset::find($existingSource->asset_id) : null;

        if ($asset !== null) {
            // Update-sync path: if the vendor moved this device to a
            // different group and the new group maps to a different
            // Snipe-IT company, reassign. Same "vendor is
            // authoritative" model as native:asset_tag for tags.
            // Admins who want to preserve manual company assignments
            // turn off group scoping for this adapter or delete the
            // specific mapping.
            $resolvedCompanyId = self::resolveCompanyId($record, $instance);
            if ($resolvedCompanyId !== null && $resolvedCompanyId !== (int) $asset->company_id) {
                $asset->company_id = $resolvedCompanyId;
            }

            return [$asset, false];
        }

        // Migration path for customers moving from a homegrown
        // sync script. Their existing assets have a serial populated
        // but no asset_external_sources row for this adapter. When
        // the admin has turned on "adopt existing assets by serial",
        // look up a matching Snipe-IT asset before creating a fresh
        // shell. Establishes the identity link so subsequent syncs
        // treat this as an update, not a duplicate create.
        $adopted = self::adoptExistingAssetBySerial($record, $instance);
        if ($adopted !== null) {
            return [$adopted, false];
        }

        // Opt-out: when the admin has turned "Create new Snipe-IT
        // assets on sync" off, skip unmatched vendor records entirely
        // instead of creating a shell. Log at warning so admins can
        // spot drift between vendor + Snipe-IT inventories.
        $adapter = $instance?->adapter();
        if ($adapter instanceof SyncAdapter && ! $adapter->createsSnipeitAssetsOnPull()) {
            Log::channel('sync-adapters')->warning(sprintf(
                '%s sync: skipped %s record %s (no existing Snipe-IT match and asset creation on pull is off)',
                $adapter->name(),
                $record->sourceKey,
                $record->sourceId,
            ));

            return null;
        }

        // UX layer on top of the authoritative require_serial check
        // in Asset::save().
        if ($record->hardwareSerial === null || $record->hardwareSerial === '') {
            $modelName = self::resolveModelSourceValue($record, $mapping);
            if ($modelName !== null && $modelName !== '') {
                $existingModel = AssetModel::where('name', $modelName)->first();
                if ($existingModel !== null && (string) $existingModel->require_serial === '1') {
                    Log::channel('sync-adapters')->warning(sprintf(
                        '%s sync: skipped %s record %s (no serial, and asset model "%s" requires one)',
                        $adapter?->name() ?? $record->sourceKey,
                        $record->sourceKey,
                        $record->sourceId,
                        $existingModel->name,
                    ));

                    return null;
                }
            }
        }

        $asset = self::createShellAsset($record, $instance, $mapping);

        // Identity row created empty of inventory columns. the
        // mapping loop below fills them and the write goes out
        // through upsertExternalSource().
        DB::table('asset_external_sources')->insert([
            'asset_id' => $asset->id,
            'company_id' => $instance?->company_id,
            'source' => $record->sourceKey,
            'external_id' => $record->sourceId,
            // NULL for CLI / scheduled runs, admin id for
            // interactive Sync Now clicks.
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$asset, true];
    }

    /**
     * Look up an existing Snipe-IT asset with a matching serial to
     * adopt into this adapter's external-sources link, when the
     * admin has turned on adopt_by_serial for this adapter instance.
     *
     * Re-enrollment: some vendors rotate the device's external_id
     * (Intune managedDevice ID, Kandji device UDID, etc)
     * when the device is reinstalled or re-enrolled. On the
     * next sync the record's new external_id does not match the
     * asset's prior asset_external_sources row, so the (source,
     * external_id) lookup in provisionAsset misses. If the hardware
     * serial still matches and adopt_by_serial is on, treat the
     * prior row's external_id as stale and update it to the new one
     * rather than creating a duplicate asset.
     *
     * The genuine case of two actively-enrolled devices
     * legitimately sharing a serial (vendor cloning bug / BYOD) is
     * not handled here: the external_id will flap between the two
     * records across syncs. True fix is pull-level reconciliation :(
     */
    private static function adoptExistingAssetBySerial(HostInventoryRecord $record, ?SyncAdapterInstance $instance): ?Asset
    {
        if ($record->hardwareSerial === null || $record->hardwareSerial === '') {
            return null;
        }
        if ($instance === null) {
            return null;
        }

        $adapter = $instance->adapter();
        if (! ($adapter instanceof SyncAdapter) || ! $adapter->adoptsBySerial()) {
            return null;
        }

        $candidate = Asset::query()
            ->where('serial', $record->hardwareSerial)
            ->first();

        if ($candidate === null) {
            return null;
        }

        $existing = DB::table('asset_external_sources')
            ->where('asset_id', $candidate->id)
            ->where('source', $record->sourceKey)
            ->first();

        if ($existing !== null) {
            DB::table('asset_external_sources')
                ->where('id', $existing->id)
                ->update([
                    'external_id' => $record->sourceId,
                    'updated_at' => now(),
                ]);

            Log::channel('sync-adapters')->info(
                "{$instance->label} re-linked asset #{$candidate->id} (serial {$record->hardwareSerial}): external_id {$existing->external_id} -> {$record->sourceId}. Prior enrollment id appears to have rotated."
            );

            return $candidate;
        }

        DB::table('asset_external_sources')->insert([
            'asset_id' => $candidate->id,
            'company_id' => $instance->company_id,
            'source' => $record->sourceKey,
            'external_id' => $record->sourceId,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $candidate;
    }

    /**
     * Apply per-field mappings. Routes each normalized field to native
     * asset columns, external-source columns, custom fields, or skips
     * it, based on per-instance config. Fields directed 'push' or
     * 'skip' on this instance are excluded from the pull path so the
     * vendor's value can't overwrite a Snipe-IT-authoritative field
     * (asset_tag pushed to the vendor shouldn't get pulled back from
     * what the vendor reports). 'both' direction still pulls (naive
     * last-write-wins) so both sides converge on whichever side ran
     * most recently.
     *
     * @param  array<string, mixed>  $externalUpdates
     * @param  array<string, string>  $mapping
     */
    private static function applyStandardFieldMappings(
        Asset $asset,
        array &$externalUpdates,
        HostInventoryRecord $record,
        ?SyncAdapterInstance $instance,
        array $mapping,
    ): void {
        $adapterForDirection = $instance?->adapter();

        foreach (MappingTargets::FIELDS as $field) {
            $target = $mapping[$field] ?? MappingTargets::defaultTarget($field);
            if ($target === 'skip') {
                continue;
            }
            if ($adapterForDirection instanceof SyncAdapter
                && ! in_array($adapterForDirection->directionFor($field), ['pull', 'both'], true)) {
                continue;
            }
            // Skip the standard `model` slot when an extra is also
            // mapped to native:model. The extras loop is the last
            // writer for model_id, so a standard-slot write here
            // would spend a resolveModelIdByName() creating an
            // AssetModel keyed on hardwareModel that gets orphaned
            // when extras overwrites the asset's model_id. #19691.
            if ($field === 'model' && $target === 'native:model' && self::hasExtraMappedTo('native:model', $mapping)) {
                continue;
            }

            $value = self::recordValueFor($record, $field);
            self::writeToTarget($asset, $externalUpdates, $target, $value, $instance, $record);
        }
    }

    /**
     * Apply extra-field mappings. Extras are vendor-specific keys the
     * adapter emits into HostInventoryRecord::$extra. Valid targets
     * for extras are custom fields OR a small set of native asset
     * columns (asset_tag, notes) for admins who store per-device
     * metadata in the vendor's labels / blueprint / team field.
     * Values get stringified so array or object payloads (Fleet's
     * labels list, etc.) land as readable text regardless of the
     * underlying native / custom write path.
     *
     * Sibling of applyStandardFieldMappings but doesn't take
     * $externalUpdates because extras only route to native columns
     * or custom fields, never to the asset_external_sources side
     * table. writeToTarget (which is what feeds $externalUpdates) is
     * not on the code path here.
     *
     * @param  array<string, string>  $mapping
     */
    private static function applyExtraFieldMappings(
        Asset $asset,
        HostInventoryRecord $record,
        ?SyncAdapterInstance $instance,
        array $mapping,
    ): void {
        if ($instance === null) {
            return;
        }
        $adapter = $instance->adapter();
        if (! $adapter instanceof SyncAdapter) {
            return;
        }

        $extraFields = $adapter->extraFields();
        foreach (array_keys($extraFields) as $extraKey) {
            $target = $mapping[$extraKey] ?? 'skip';
            $value = self::stringifyExtra(
                $record->extra[$extraKey] ?? null,
                self::extraFieldType($extraFields, $extraKey),
            );
            self::writeExtraValueToTarget($asset, $target, $value, $instance, $record);
        }
    }

    /**
     * Route one extra value to its configured target. Extras only
     * write to native:{column} or custom:{id} slots. Skip and any
     * other target shape are no-ops. Null values (missing from the
     * vendor payload or unstringifiable) pass through silently so
     * partially-populated payloads never blank existing data.
     */
    private static function writeExtraValueToTarget(
        Asset $asset,
        string $target,
        ?string $value,
        SyncAdapterInstance $instance,
        ?HostInventoryRecord $record = null,
    ): void {
        if ($target === 'skip' || $value === null) {
            return;
        }

        if (str_starts_with($target, 'custom:')) {
            self::writeCustom($asset, (int) substr($target, 7), $value);

            return;
        }

        if (str_starts_with($target, 'native:')) {
            self::writeNative($asset, substr($target, 7), $value, $instance, $record);
        }
    }

    /**
     * Diff external-source column writes against a pre-sync snapshot
     * and log meaningful changes as an Actionlog. Skips heartbeat-only
     * diffs unless the adapter has heartbeat logging turned on.
     *
     * @param  array<string, mixed>  $previousExternal
     * @param  array<string, mixed>  $externalUpdates
     */
    private static function logExternalSourceChanges(
        Asset $asset,
        array $previousExternal,
        array $externalUpdates,
        bool $wasDirty,
        ?SyncAdapterInstance $instance,
    ): void {
        $externalDiff = self::diffExternal($previousExternal, $externalUpdates);
        $meaningfulExternalChange = count(array_diff(array_keys($externalDiff), ['last_seen'])) > 0;
        $heartbeatOnly = $externalDiff !== [] && ! $meaningfulExternalChange;

        $logsHeartbeats = false;
        if ($instance !== null) {
            $adapter = $instance->adapter();
            if ($adapter instanceof SyncAdapter) {
                $logsHeartbeats = $adapter->logsHeartbeats();
            }
        }

        if (! $meaningfulExternalChange && ! ($heartbeatOnly && $logsHeartbeats)) {
            return;
        }

        self::writeExternalSourceActionlog($asset, $externalDiff);
        // Bump updated_at so "recently updated" sorts reflect the
        // sync activity. Use saveQuietly so AssetObserver::updating
        // doesn't fire and log a redundant `update` Actionlog on top
        // of the external-source row we just wrote. Skipped when the
        // asset was already dirty (native columns changed) because
        // the save above handles it.
        if (! $wasDirty) {
            $asset->updated_at = now();
            $asset->saveQuietly();
        }
    }

    /**
     * Snapshot the current inventory columns from the
     * asset_external_sources row for diffing. Returns an empty array
     * when no row exists yet (first sync on this asset from any
     * adapter). Identity columns (source, external_id, company_id,
     * asset_id, id, timestamps) are stripped so the diff only covers
     * the columns the mapping loop writes.
     *
     * @return array<string, mixed>
     */
    private static function loadExternalSourceInventory(int $assetId, string $source): array
    {
        // Filter by (asset_id, source), not asset_id alone. Multi-
        // adapter assets have one row per source and the pre-sync
        // snapshot must come from THIS adapter's row for the diff
        // to be meaningful. See issue #19880.
        $row = DB::table('asset_external_sources')
            ->where('asset_id', $assetId)
            ->where('source', $source)
            ->first();
        if ($row === null) {
            return [];
        }

        $arr = (array) $row;
        unset(
            $arr['id'],
            $arr['asset_id'],
            $arr['company_id'],
            $arr['source'],
            $arr['external_id'],
            $arr['created_at'],
            $arr['updated_at'],
        );

        return $arr;
    }

    /**
     * Which inventory columns differ between the pre-sync snapshot
     * and the just-written updates. Only diffs the columns the sync
     * actually touched. untouched columns stay stable.
     *
     * @param  array<string, mixed>  $previous
     * @param  array<string, mixed>  $updates
     * @return array<string, array{old: mixed, new: mixed}>
     */
    private static function diffExternal(array $previous, array $updates): array
    {
        $diff = [];
        foreach ($updates as $column => $newValue) {
            $oldValue = $previous[$column] ?? null;
            if ((string) $oldValue !== (string) $newValue) {
                $diff[$column] = ['old' => $oldValue, 'new' => $newValue];
            }
        }

        return $diff;
    }

    /**
     * Write an Actionlog entry describing an external-source column
     * change. Uses log_meta with the same shape AssetObserver uses on
     * native-column updates so the asset-history renderer can display
     * it uniformly. log_meta is a plain TEXT column carrying JSON-
     * encoded strings (per feedback_log_meta_column memory).
     *
     * @param  array<string, array{old: mixed, new: mixed}>  $diff
     */
    private static function writeExternalSourceActionlog(Asset $asset, array $diff): void
    {
        $log = new Actionlog;
        $log->item_id = $asset->id;
        $log->item_type = Asset::class;
        $log->created_at = now();
        $log->created_by = null; // system-driven, not a user action
        $log->log_meta = json_encode($diff);
        $log->logaction('update');
    }

    /**
     * Read all mapping.* config keys for this instance and index them
     * by normalized field name. Empty array when the instance is null
     * (asset carries a legacy source with no corresponding instance).
     *
     * @return array<string, string>
     */
    private static function loadMapping(?SyncAdapterInstance $instance): array
    {
        if ($instance === null) {
            return [];
        }

        $mapping = [];

        // Standard normalized fields.
        foreach (MappingTargets::FIELDS as $field) {
            $stored = SyncAdapterConfig::get($instance->id, 'mapping.'.$field);
            if ($stored !== null) {
                $mapping[$field] = $stored;
            }
        }

        // Adapter-specific extra fields, if the adapter declares any.
        $adapter = $instance->adapter();
        if ($adapter instanceof SyncAdapter) {
            foreach (array_keys($adapter->extraFields()) as $extraKey) {
                $stored = SyncAdapterConfig::get($instance->id, 'mapping.'.$extraKey);
                if ($stored !== null) {
                    $mapping[$extraKey] = $stored;
                }
            }
        }

        return $mapping;
    }

    /**
     * Convert an extra-field value from the vendor's payload into a
     * string suitable for a custom field. Boolean-typed extras render
     * to '1' or '0' so a checkbox custom field's stored value matches
     * what its UI expects. Scalars pass through. Arrays of scalars
     * get comma-joined so a list like Fleet's labels lands as
     * "server, prod, us-east". Everything else falls back to JSON so
     * admins at least see the raw shape.
     */
    private static function stringifyExtra(mixed $value, string $type = 'text'): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($type === 'boolean') {
            return ((bool) $value) ? '1' : '0';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        if (is_array($value)) {
            $allScalar = array_reduce(
                $value,
                fn ($carry, $v) => $carry && is_scalar($v),
                true,
            );

            return $allScalar
                ? implode(', ', array_map(fn ($v) => (string) $v, $value))
                : json_encode($value);
        }

        return json_encode($value);
    }

    /**
     * Extract the type of an extra field from an adapter's
     * extraFields() declaration. Entries can be either plain strings
     * (defaults to text) or arrays with an explicit `type` value
     * ('text', 'boolean'). label_key vs label doesn't matter here;
     * this helper only reads the type slot.
     *
     * @param  array<string, string|array{label?: string, label_key?: string, type?: string, admin_defined?: bool}>  $extraFields
     */
    private static function extraFieldType(array $extraFields, string $key): string
    {
        $entry = $extraFields[$key] ?? null;
        if (is_array($entry) && isset($entry['type'])) {
            return $entry['type'];
        }

        return 'text';
    }

    /**
     * Create the minimum viable Asset row so we have an id to hang
     * downstream mapping writes off. Uses a synthetic name (source
     * slug + external id) since the hostname mapping may point at a
     * custom field or be skipped entirely. The mapping loop overwrites
     * asset.name if the hostname mapping is native:name.
     *
     * When the instance is scoped to a company, new assets inherit its
     * company_id so FMCS-scoped views only see their own company's
     * synced hosts.
     */
    /**
     * @param  array<string, string>  $mapping
     */
    private static function createShellAsset(HostInventoryRecord $record, ?SyncAdapterInstance $instance, array $mapping = []): Asset
    {
        $asset = new Asset;
        $asset->name = $record->sourceKey.'-'.$record->sourceId;
        $asset->model_id = self::resolveModelId($record, $instance, $mapping);
        $asset->status_id = self::resolveStatusId($instance);
        $asset->asset_tag = self::resolveAssetTag($record, $instance);
        $asset->company_id = self::resolveCompanyId($record, $instance);
        // Set serial before save because Asset::save() adds a `required`
        // rule to the serial field when the resolved model has
        // require_serial = 1.
        $asset->serial = $record->hardwareSerial;
        // Asset uses ValidatingTrait, so save() returns false on
        // validation failure without throwing. Without this guard the
        // caller then wrote asset_external_sources with asset_id=null
        // and only surfaced the FK violation, hiding the real cause
        // (e.g. model_id is null because the adapter's model mapping
        // points at a field the vendor didn't return, or asset_tag
        // collided with an existing row). Raise the validation errors
        // so the sync loop's catch block logs the actionable message.
        if (! $asset->save()) {
            throw new RuntimeException(sprintf(
                'could not save asset for %s record %s: %s',
                $record->sourceKey,
                $record->sourceId,
                implode('; ', $asset->getErrors()->all()) ?: 'unknown validation failure',
            ));
        }

        return $asset;
    }

    /**
     * Status label id for newly-created assets. Precedence:
     *   1. Adapter's configured default_status_id (when set and the
     *      referenced label still exists).
     *   2. First deployable status label.
     *   3. First status label of any kind.
     * Throws if the tenant has no status labels at all, since Asset
     * requires status_id to be set.
     */
    private static function resolveStatusId(?SyncAdapterInstance $instance): int
    {
        if ($instance !== null) {
            $adapter = $instance->adapter();
            if ($adapter instanceof SyncAdapter) {
                $configuredId = $adapter->defaultStatusId();
                if ($configuredId !== null && Statuslabel::whereKey($configuredId)->exists()) {
                    return $configuredId;
                }
            }
        }

        $status = Statuslabel::deployable()->first() ?? Statuslabel::first();
        if ($status === null) {
            throw new RuntimeException('No status label available for auto-created host inventory assets.');
        }

        return $status->id;
    }

    /**
     * Pick the Snipe-IT company id for a newly-created asset.
     * Precedence:
     *   1. Adapter's per-group company mapping (when adapter supports
     *      group scoping AND record has a vendorGroupId AND the group
     *      is mapped).
     *   2. Instance's own company_id (existing behavior).
     *   3. Null (asset lands in the shared/no-company bucket).
     *
     * Group mappings let one adapter instance sync devices from a
     * multi-tenant vendor (Kandji, Fleet, Jamf) into per-customer
     * Snipe-IT companies without needing one adapter instance per
     * company. See SyncAdapter::groupMappings().
     */
    private static function resolveCompanyId(HostInventoryRecord $record, ?SyncAdapterInstance $instance): ?int
    {
        if ($instance !== null && $record->vendorGroupId !== null && $record->vendorGroupId !== '') {
            $adapter = $instance->adapter();
            if ($adapter instanceof SyncAdapter && $adapter->supportsGroupScoping()) {
                $mapped = $adapter->companyForVendorGroup($record->vendorGroupId);
                if ($mapped !== null) {
                    return $mapped;
                }
            }
        }

        return $instance?->company_id;
    }

    /**
     * Pick the asset tag for a newly-created asset. Precedence:
     *   1. Adapter's configured pattern (with placeholders substituted).
     *   2. Snipe-IT's global autoincrement setting.
     *   3. Fallback synthetic tag (source-external_id) so save never
     *      lands with a null tag even when the other two miss.
     *
     * The pattern lets admins force a stable per-vendor tag scheme
     * (KANDJI-{serial}, FLEET-{external_id}, etc.) so re-imported
     * assets keep their tag across reinstalls. See SyncAdapter
     * -> assetTagPattern() for the storage side.
     */
    private static function resolveAssetTag(HostInventoryRecord $record, ?SyncAdapterInstance $instance): string
    {
        $adapter = $instance?->adapter();
        if ($adapter instanceof SyncAdapter) {
            $pattern = $adapter->assetTagPattern();
            if ($pattern !== null) {
                $rendered = self::renderAssetTagPattern($pattern, $record);
                if ($rendered !== null && $rendered !== '') {
                    return $rendered;
                }
            }
        }

        return Asset::autoincrement_asset()
            ?: $record->sourceKey.'-'.$record->sourceId;
    }

    /**
     * Substitute the known placeholders in an asset-tag pattern with
     * values from the record. Unknown / unpopulated placeholders
     * render as empty. Returns the trimmed result, or null when the
     * pattern rendered to an empty string entirely (which triggers
     * the caller's autoincrement fallback).
     *
     * Placeholders supported today: {serial} {external_id} {hostname}
     * {model} {source}. Extras (e.g. {extra.kandji_asset_tag}) can be
     * added later without touching callers.
     */
    /**
     * Recognized placeholder tokens for asset_tag_pattern. Kept as a
     * public constant so the validation rule (AssetTagPatternRule)
     * can reference the same list without drifting from the
     * substitution logic below.
     */
    public const ASSET_TAG_PATTERN_PLACEHOLDERS = [
        '{serial}',
        '{external_id}',
        '{hostname}',
        '{model}',
        '{source}',
    ];

    private static function renderAssetTagPattern(string $pattern, HostInventoryRecord $record): ?string
    {
        $substitutions = [
            '{serial}' => (string) ($record->hardwareSerial ?? ''),
            '{external_id}' => $record->sourceId,
            '{hostname}' => (string) ($record->hostname ?? ''),
            '{model}' => (string) ($record->hardwareModel ?? ''),
            '{source}' => $record->sourceKey,
        ];

        $rendered = trim(strtr($pattern, $substitutions));

        return $rendered === '' ? null : $rendered;
    }

    /**
     * Extract the normalized value for a given field from the record.
     *
     * `last_seen` passes through a timezone rotation to app.timezone before
     * the naive string is produced. Vendor adapters typically parse their
     * API response with `Carbon::parse` on an ISO-8601 string carrying a
     * `Z` suffix, which yields a Carbon in UTC. `toDateTimeString()` on
     * that instance would write UTC wall clock into a column every other
     * Snipe-IT write populates with app-timezone wall clock, and the view
     * layer would then interpret that UTC wall clock as if it were in
     * app.timezone (future-dated on anything west of UTC, past-dated on
     * anything east).
     *
     * This is part of a much bigger yuck that we'll eventually have to solve
     * with a fugly migration and then putting everything into UTC properly.
     * For now, this is a stopgap to keep the sync from writing
     * future-dated last_seen values, so while it's still "wrong", it's at
     * least "wrong in the same way as everything else" and doesn't
     * break the view layer.
     *
     * Laravel pulled APP_TIMEZONE out of the config in 10.x but still
     * honors it in 11.x, so this is a workaround until we can get a proper
     * fix in place. See #19765.
     */
    private static function recordValueFor(HostInventoryRecord $record, string $field): mixed
    {
        return match ($field) {
            'hostname' => $record->hostname,
            'serial' => $record->hardwareSerial,
            'asset_tag' => $record->assetTag,
            'model' => $record->hardwareModel,
            'mac' => $record->primaryMac,
            'ip' => $record->primaryIp,
            'os' => $record->os,
            'os_version' => $record->osVersion,
            'last_seen' => $record->lastSeen?->copy()->timezone(config('app.timezone'))->toDateTimeString(),
            default => null,
        };
    }

    /**
     * Route a single (field, target, value) triple. Native writes go
     * on the Asset instance (saved together at the end of the run).
     * External-source column writes gather into a batch. Custom
     * field writes hit the asset's dynamic column via the model's
     * standard attribute magic.
     *
     * @param  array<string, mixed>  $externalUpdates  passed by reference
     */
    private static function writeToTarget(Asset $asset, array &$externalUpdates, string $target, mixed $value, ?SyncAdapterInstance $instance = null, ?HostInventoryRecord $record = null): void
    {
        if ($value === null && $target !== 'native:model') {
            // Nothing to write. Skip so we don't blank existing data on
            // partial syncs.
            return;
        }

        [$type, $id] = array_pad(explode(':', $target, 2), 2, '');

        switch ($type) {
            case 'native':
                self::writeNative($asset, $id, $value, $instance, $record);
                break;
            case 'external':
                // Normalized field names get translated to actual
                // side-table column names here. Keeps the target
                // encoding symmetric with MappingTargets::FIELDS
                // rather than leaking `primary_` schema naming into
                // the target strings and translation keys.
                $column = match ($id) {
                    'mac' => 'primary_mac',
                    'ip' => 'primary_ip',
                    default => $id,
                };
                $externalUpdates[$column] = $value;
                break;
            case 'custom':
                self::writeCustom($asset, (int) $id, $value);
                break;
        }
    }

    private static function writeNative(Asset $asset, string $column, mixed $value, ?SyncAdapterInstance $instance = null, ?HostInventoryRecord $record = null): void
    {
        switch ($column) {
            case 'name':
                if ($value !== null && $value !== '') {
                    $asset->name = $value;
                }
                break;
            case 'serial':
                $asset->serial = $value;
                break;
            case 'asset_tag':
                // Only overwrite when the vendor sent a non-empty tag,
                // so a sync run that happens to omit the tag doesn't
                // blank whatever the admin already curated.
                $tag = is_string($value) ? trim($value) : $value;
                if ($tag !== null && $tag !== '') {
                    $asset->asset_tag = $tag;
                }
                break;
            case 'notes':
                // Same non-empty guard as asset_tag, with the same
                // whitespace-only coercion.
                $notes = is_string($value) ? trim($value) : $value;
                if ($notes !== null && $notes !== '') {
                    $asset->notes = $notes;
                }
                break;
            case 'model':
                // Model comes as a string name. Resolve or auto-create,
                // routing to a per-record category when the adapter
                // provides one (e.g. ABM productFamily) and falling
                // back to the instance-wide default_category_id.
                if ($value !== null && $value !== '') {
                    $asset->model_id = self::resolveModelIdByName((string) $value, $instance, $record);
                }
                break;
            case 'byod':
                // Boolean extras arrive here stringified to '1' or '0'
                // by stringifyExtra(). Convert back to the model's
                // boolean shape so the column stores the right type.
                // Empty string means the vendor didn't send a value on
                // this sync cycle, so skip rather than false-blank.
                if ($value !== null && $value !== '') {
                    $asset->byod = ($value === '1');
                }
                break;
            case 'purchase_date':
                // Watson validating rule wants date_format:Y-m-d.
                // Log + skip if the string isn't parseable so a bad record
                // doesn't donk the sync run.
                //
                // Rotate to app.timezone before extracting the date part so
                // a vendor datetime at the UTC day boundary (e.g.
                // "2024-07-23T03:00:00Z" which is 2024-07-22 20:00 Pacific)
                // records the date the user would recognize. Same reason
                // the last_seen path converts to app timezone before
                // producing its naive string. #19765.
                if ($value !== null && $value !== '') {
                    try {
                        $asset->purchase_date = \Carbon\Carbon::parse((string) $value)->timezone(config('app.timezone'))->format('Y-m-d');
                    } catch (\Exception $e) {
                        Log::channel('sync-adapters')->warning("Skipping unparseable purchase_date \"{$value}\" for asset {$asset->id}: ".$e->getMessage());
                    }
                }
                break;
            case 'order_number':
                if ($value !== null && $value !== '') {
                    $asset->order_number = (string) $value;
                }
                break;
            case 'eol_date':
                // Vendor-sourced EOL. Set eol_explicit so the Asset
                // eolDate accessor honors this value over the derived
                // "purchase_date + model.eol months" default.
                if ($value !== null && $value !== '') {
                    try {
                        $asset->asset_eol_date = \Carbon\Carbon::parse((string) $value)->timezone(config('app.timezone'))->format('Y-m-d');
                        $asset->eol_explicit = true;
                    } catch (\Exception $e) {
                        Log::channel('sync-adapters')->warning("Skipping unparseable eol_date \"{$value}\" for asset {$asset->id}: ".$e->getMessage());
                    }
                }
                break;
            case 'model_number':
                // Writes to the resolved AssetModel row, not the asset.
                $modelNumber = is_string($value) ? trim($value) : $value;
                if ($modelNumber === null || $modelNumber === '' || $asset->model_id === null) {
                    break;
                }
                $assetModel = AssetModel::find($asset->model_id);
                if ($assetModel === null) {
                    break;
                }
                if ($assetModel->model_number === $modelNumber) {
                    break;
                }
                $assetModel->model_number = $modelNumber;
                try {
                    $assetModel->save();
                } catch (\Exception $e) {
                    Log::channel('sync-adapters')->warning("Skipping model_number update \"{$modelNumber}\" for AssetModel {$assetModel->id}: ".$e->getMessage());
                }
                break;
        }
    }

    private static function writeCustom(Asset $asset, int $customFieldId, mixed $value): void
    {
        $field = CustomField::find($customFieldId);
        if ($field === null || $field->db_column === null) {
            return;
        }

        // #19725: a boolean extra with a false value arrives here as
        // the string '0' via stringifyExtra(). Single-option checkbox
        // custom fields (option list = ['1']) reject '0' through the
        // `checkboxes` validator and blow up the whole asset save with
        // "contains invalid options", so nothing on that device syncs.
        // Empty string is what an unchecked checkbox stores natively
        // and passes validation cleanly, so translate here. Checkbox
        // fields that DO list '0' as a valid option (a two-option
        // "on/off" setup) still get the raw '0' passed through.
        if ($field->element === 'checkbox' && $value === '0') {
            $options = $field->formatFieldValuesAsArray();
            if (! in_array('0', $options, true)) {
                $value = '';
            }
        }

        $asset->{$field->db_column} = $value;
    }

    /**
     * Detect the "sync only changed the last_seen custom field" case
     *
     * @param  array<string, string>  $mapping
     */
    private static function isHeartbeatOnlyAssetDirty(
        Asset $asset,
        array $mapping,
        ?SyncAdapterInstance $instance,
    ): bool {
        if (! $asset->isDirty()) {
            return false;
        }
        if ($instance === null) {
            return false;
        }
        $adapter = $instance->adapter();
        if (! $adapter instanceof SyncAdapter || $adapter->logsHeartbeats()) {
            return false;
        }

        $lastSeenTarget = $mapping['last_seen'] ?? MappingTargets::defaultTarget('last_seen');
        if (! str_starts_with($lastSeenTarget, 'custom:')) {
            return false;
        }

        $field = CustomField::find((int) substr($lastSeenTarget, 7));
        if ($field === null || $field->db_column === null) {
            return false;
        }

        return array_keys($asset->getDirty()) === [$field->db_column];
    }

    /**
     * Write the inventory columns onto the asset's external-source
     * row for this specific adapter. The identity columns (source,
     * external_id, company_id) are already written at create time and
     * never change here. updateOrInsert covers the edge case where the
     * identity row was somehow deleted between the create step and
     * this write.
     *
     * Match key is (asset_id, source) because an asset synced by two
     * adapters has two rows in asset_external_sources, one per source.
     * Keying on asset_id alone would overwrite whichever row MySQL
     * returned first regardless of adapter. See issue #19880.
     */
    private static function upsertExternalSource(Asset $asset, string $source, array $updates): void
    {
        DB::table('asset_external_sources')->updateOrInsert(
            ['asset_id' => $asset->id, 'source' => $source],
            $updates + ['updated_at' => now()],
        );
    }

    /**
     * Look up an AssetModel by the record's hardware_model string,
     * auto-create if missing. When the vendor sends no hardware_model
     * (osquery sometimes omits it, some MDMs return null for VMs),
     * return null so the caller / Asset validation ("model_id" is
     * required in Asset::$rules) refuses the save. That leaves the
     * host out of Snipe-IT rather than piling nameless assets into
     * an "Unknown" bucket admins can't clean up later.
     *
     * Auto-created models get hung off a "Discovered Hardware"
     * category which is itself get-or-created.
     */
    /**
     * @param  array<string, string>  $mapping
     */
    private static function resolveModelId(HostInventoryRecord $record, ?SyncAdapterInstance $instance = null, array $mapping = []): ?int
    {
        $modelSource = self::resolveModelSourceValue($record, $mapping);
        if ($modelSource === null || $modelSource === '') {
            return null;
        }

        return self::resolveModelIdByName($modelSource, $instance, $record);
    }

    /**
     * True when at least one extra-field mapping targets the given
     * mapping target string (typically 'native:model'). Used to
     * suppress the standard-field write when an extra is going to
     * overwrite the same target further down the mapping loop.
     *
     * @param  array<string, string>  $mapping
     */
    private static function hasExtraMappedTo(string $target, array $mapping): bool
    {
        foreach ($mapping as $key => $mappedTarget) {
            if ($mappedTarget !== $target) {
                continue;
            }
            if (in_array($key, MappingTargets::FIELDS, true)) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * Pick the value that will end up as this asset's AssetModel name
     * once the mapping loop finishes. When an admin has routed an extra
     * to native:model (Apple Business Manager's marketing name is the
     * common case), the extras loop is the last writer for model_id.
     * createShellAsset must use the same source so the auto-created
     * AssetModel matches what the mapping loop would settle on. Falls
     * back to the record's hardwareModel when nothing is remapped to
     * native:model. Fixes #19691: a phantom AssetModel named after the
     * vendor's part number was left dangling on every new asset when
     * the marketing name was mapped in.
     *
     * @param  array<string, string>  $mapping
     */
    private static function resolveModelSourceValue(HostInventoryRecord $record, array $mapping): ?string
    {
        foreach ($mapping as $key => $target) {
            if ($target !== 'native:model') {
                continue;
            }
            if (in_array($key, MappingTargets::FIELDS, true)) {
                // Standard field slot. Its source is hardwareModel,
                // which is the same value the fallback below returns.
                continue;
            }
            $value = $record->extra[$key] ?? null;
            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return $record->hardwareModel;
    }

    private static function resolveModelIdByName(string $modelName, ?SyncAdapterInstance $instance = null, ?HostInventoryRecord $record = null): int
    {
        $canonicalModelNumber = $record?->canonicalModelNumber;
        $canonicalMarketingName = $record?->canonicalMarketingName;

        // When AppleDB enrichment updated
        // canonical values on the model record, consolidate rows across
        // adapters that emit the same hardware under different
        // vendor name strings (e.g. ABM's long marketing name vs.
        // Fleet's short osquery name for the same productType).
        // Primary clause finds-by-name-and-number. Fallback clause
        // picks up rows a prior sync named differently but tagged
        // with the same canonical marketing name.
        if ($canonicalModelNumber !== null) {
            $query = AssetModel::query()
                ->where('model_number', $canonicalModelNumber)
                ->where(function ($q) use ($modelName, $canonicalMarketingName) {
                    $q->where('name', $modelName);
                    if ($canonicalMarketingName !== null) {
                        $q->orWhere('mfg_marketing_name', $canonicalMarketingName);
                    }
                });

            $model = $query->first();
            if ($model !== null) {
                $perRecordId = self::perRecordCategoryId($instance, $record);
                if ($perRecordId !== null && $model->category_id !== $perRecordId) {
                    $model->category_id = $perRecordId;
                    $model->save();
                }

                return $model->id;
            }
        }

        $model = AssetModel::where('name', $modelName)->first();
        if ($model !== null) {
            // Re-sync the category ONLY when the adapter has a
            // per-record opinion (e.g. ABM's productFamily +
            // deviceModel routing). Falling through to
            // default_category_id doesn't touch the existing
            // category, because admins often curate model categories
            // by hand and the instance-wide default would clobber
            // that work every sync.
            $perRecordId = self::perRecordCategoryId($instance, $record);
            if ($perRecordId !== null && $model->category_id !== $perRecordId) {
                $model->category_id = $perRecordId;
                $model->save();
            }

            return $model->id;
        }

        $model = new AssetModel;
        $model->name = $modelName;
        $model->category_id = self::categoryIdFor($instance, $record);
        $model->manufacturer_id = self::manufacturerIdFor($record);
        if ($canonicalModelNumber !== null) {
            $model->model_number = $canonicalModelNumber;
        }
        if ($canonicalMarketingName !== null) {
            $model->mfg_marketing_name = $canonicalMarketingName;
        }
        $adapter = $instance?->adapter();
        if ($adapter instanceof SyncAdapter) {
            $model->fieldset_id = $adapter->defaultFieldsetId();
        }
        $model->save();

        return $model->id;
    }

    /**
     * Resolve the manufacturer for a new model. MySQL's default
     * collation is case-insensitive, so firstOrCreate matches 'apple'
     * or 'APPLE' to an existing 'Apple' row without a duplicate. When
     * we do create a new one, log a warning so admins can spot near-
     * duplicates like 'Apple' vs 'Apple, Inc.' that the collation
     * won't catch. Returns null when the record carries no
     * manufacturer so we don't stamp a default.
     */
    private static function manufacturerIdFor(?HostInventoryRecord $record): ?int
    {
        $name = $record?->manufacturer;
        if (! is_string($name) || trim($name) === '') {
            return null;
        }

        $manufacturer = Manufacturer::firstOrCreate(['name' => trim($name)]);
        if ($manufacturer->wasRecentlyCreated) {
            Log::channel('sync-adapters')->warning(
                "Sync adapter created new manufacturer \"{$manufacturer->name}\". If this duplicates an existing manufacturer under a different spelling, reassign the affected asset models to the manufacturer you want to keep and delete the extra one.",
            );
        }

        return (int) $manufacturer->id;
    }

    /**
     * Category id auto-created models get hung off. Precedence:
     *   1. Adapter's per-record override (e.g. ABM productFamily
     *      routing to family-specific categories) via
     *      categoryIdForRecord($record).
     *   2. Adapter's instance-wide default_category_id.
     *   3. Get-or-create "Discovered Hardware" fallback so we always
     *      produce a valid category_id (AssetModel requires one).
     */
    private static function categoryIdFor(?SyncAdapterInstance $instance, ?HostInventoryRecord $record = null): int
    {
        $perRecordId = self::perRecordCategoryId($instance, $record);
        if ($perRecordId !== null) {
            return $perRecordId;
        }

        if ($instance !== null) {
            $adapter = $instance->adapter();
            if ($adapter instanceof SyncAdapter) {
                $configuredId = $adapter->defaultCategoryId();
                if ($configuredId !== null && Category::whereKey($configuredId)->exists()) {
                    return $configuredId;
                }
            }
        }

        return Category::firstOrCreate(
            ['name' => 'Discovered Hardware', 'category_type' => 'asset'],
        )->id;
    }

    /**
     * Adapter-declared per-record category id, validated to still
     * exist. Returns null when the adapter has no per-record opinion
     * OR the id it returned no longer resolves (e.g. an admin
     * deleted the target category after configuring the override).
     * Null return means "no explicit re-sync intent" and callers
     * should fall through to their own defaults.
     */
    private static function perRecordCategoryId(?SyncAdapterInstance $instance, ?HostInventoryRecord $record): ?int
    {
        if ($instance === null || $record === null) {
            return null;
        }
        $adapter = $instance->adapter();
        if (! $adapter instanceof SyncAdapter) {
            return null;
        }
        $id = $adapter->categoryIdForRecord($record);
        if ($id === null || ! Category::whereKey($id)->exists()) {
            return null;
        }

        return $id;
    }

    /**
     * Look up the vendor-reported user on the record against Snipe-IT
     * users per the adapter's configured match strategy, and assign
     * the asset when the lookup succeeds. Never un-assigns: a payload
     * missing the user field leaves the existing assignment intact.
     */
    private static function assignUserIfMatched(Asset $asset, HostInventoryRecord $record, ?SyncAdapterInstance $instance): void
    {
        if ($instance === null) {
            return;
        }

        $adapter = $instance->adapter();
        if (! $adapter instanceof SyncAdapter) {
            return;
        }

        $strategy = $adapter->userMatchStrategy();
        if ($strategy === 'none') {
            return;
        }

        $attempts = self::buildUserMatchAttempts($record, $strategy);

        if ($attempts === []) {
            if ($adapter->checksInOnNullUser()
                && $asset->assigned_to !== null
                && $asset->assigned_type === User::class) {
                self::silentCheckinAsset($asset, $instance);
            }

            return;
        }

        $user = self::resolveUserFromAttempts($attempts, $instance, $strategy, $record->sourceId);
        if ($user === null) {
            return;
        }

        self::applyUserAssignment($asset, $user, $adapter, $instance);
    }

    /**
     * Build the ordered list of (field, value) attempts for the user-
     * match strategy. Cascade strategy tries username first (guaranteed
     * unique in Snipe-IT) then email as fallback. Explicit single-field
     * strategies produce one attempt. Attempts where the vendor gave us
     * nothing to match on are filtered out so callers can distinguish
     * "vendor reported no user at all" (opt-in checkin-on-null) from
     * "vendor gave us a user we couldn't find" (warning log).
     *
     * @return array<int, array{0: string, 1: string}>
     */
    private static function buildUserMatchAttempts(HostInventoryRecord $record, string $strategy): array
    {
        $attempts = match ($strategy) {
            'email' => [['email', $record->assignedUserEmail]],
            'username' => [['username', $record->assignedUserName]],
            'username_then_email' => [
                ['username', $record->assignedUserName],
                ['email', $record->assignedUserEmail],
            ],
            default => [],
        };

        return array_values(array_filter(
            $attempts,
            fn (array $a) => is_string($a[1]) && $a[1] !== '',
        ));
    }

    /**
     * Walk the ordered attempts and return the first matching user.
     * Emits an info log on success and a warning on complete miss so
     * admins can see the strategy played out per-host. Returns null
     * when no attempt matched.
     *
     * @param  array<int, array{0: string, 1: string}>  $attempts
     */
    private static function resolveUserFromAttempts(
        array $attempts,
        SyncAdapterInstance $instance,
        string $strategy,
        string $sourceId,
    ): ?User {
        foreach ($attempts as [$field, $value]) {
            $candidate = User::query()->where($field, $value)->first();
            if ($candidate !== null) {
                Log::channel('sync-adapters')->info(sprintf(
                    '%s sync: matched user via %s=%s for host %s',
                    $instance->slug,
                    $field,
                    $value,
                    $sourceId,
                ));

                return $candidate;
            }
        }

        $tried = implode(', ', array_map(
            fn (array $a) => sprintf('%s=%s', $a[0], $a[1]),
            $attempts,
        ));
        Log::channel('sync-adapters')->warning(sprintf(
            '%s sync: no Snipe-IT user matched (strategy=%s, tried [%s]) for host %s',
            $instance->slug,
            $strategy,
            $tried,
            $sourceId,
        ));

        return null;
    }

    /**
     * Commit the assignment. Suppress mode is always silent (direct
     * assignment). Otherwise fall through to Asset::checkOut() which
     * fires the CheckoutableCheckedOut event and its notification
     * listener. The event constructor requires a non-null admin User.
     * auth() is null for CLI-triggered sync runs, so silently fall
     * back to direct assignment when there's no auth context to
     * attach the event to. Skips when the asset is already assigned
     * to the resolved user.
     */
    private static function applyUserAssignment(
        Asset $asset,
        User $user,
        SyncAdapter $adapter,
        SyncAdapterInstance $instance,
    ): void {
        if ((int) $asset->assigned_to === (int) $user->id
            && $asset->assigned_type === User::class) {
            return;
        }

        $admin = auth()->user();
        if ($adapter->suppressesNotifications() || $admin === null) {
            self::silentAssignToUser($asset, $user);

            return;
        }

        $asset->checkOut(
            target: $user,
            admin: $admin,
            checkout_at: now()->toDateTimeString(),
            note: 'Assigned via sync from '.$instance->slug,
            name: $asset->name,
        );
    }

    /**
     * Direct assignment that skips the CheckoutableCheckedOut event
     * (and therefore the checkout-email listener). Used when the
     * adapter has suppress_notifications on. Still writes an
     * Actionlog entry so the asset's history reflects the assignment.
     */
    private static function silentAssignToUser(Asset $asset, User $user): void
    {
        $asset->assigned_to = $user->id;
        $asset->assigned_type = User::class;
        $asset->last_checkout = now();
        if ($user->location_id !== null) {
            $asset->location_id = $user->location_id;
        }
        // See #19726 note above the primary write site. saveOrFail()
        // returns false rather than throwing when Asset's fieldset
        // validation aborts the save, so we check save()'s return
        // and raise for the pull loop's catch to log.
        if (! $asset->save()) {
            $errors = implode('; ', $asset->getErrors()->all()) ?: 'unknown validation failure';
            throw new RuntimeException("could not persist sync-driven checkout on asset {$asset->id}: {$errors}");
        }

        $log = new Actionlog;
        $log->item_id = $asset->id;
        $log->item_type = Asset::class;
        $log->target_id = $user->id;
        $log->target_type = User::class;
        $log->created_at = now();
        $log->created_by = null; // system-driven
        $log->note = 'Assigned via sync';
        $log->logaction('checkout');
    }

    /**
     * Silent counterpart to silentAssignToUser: clears the assignment
     * on the asset and writes a checkin Actionlog so the asset's
     * history shows the sync-driven un-assign. Skips the standard
     * CheckoutableCheckedIn event to keep scheduled syncs from
     * blasting checkin notifications every cycle.
     */
    private static function silentCheckinAsset(Asset $asset, SyncAdapterInstance $instance): void
    {
        $previousUserId = $asset->assigned_to;
        $previousUserType = $asset->assigned_type;

        $asset->assigned_to = null;
        $asset->assigned_type = null;
        $asset->last_checkin = now();
        // See #19726 note above the primary write site.
        if (! $asset->save()) {
            $errors = implode('; ', $asset->getErrors()->all()) ?: 'unknown validation failure';
            throw new RuntimeException("could not persist sync-driven checkin on asset {$asset->id}: {$errors}");
        }

        $log = new Actionlog;
        $log->item_id = $asset->id;
        $log->item_type = Asset::class;
        $log->target_id = $previousUserId;
        $log->target_type = $previousUserType;
        $log->created_at = now();
        $log->created_by = null;
        $log->note = 'Checked in via sync from '.$instance->slug.' (vendor reported no assigned user)';
        $log->logaction('checkin from');
    }
}
