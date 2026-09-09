<?php

namespace App\SyncAdapters;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\CustomField;
use App\Models\Statuslabel;
use App\Models\SyncAdapterConfig;
use App\Models\SyncAdapterInstance;
use App\Models\User;
use App\SyncAdapters\Support\ConfigurableAdapter;
use App\SyncAdapters\Support\MappingTargets;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Shared asset-side upsert path for every host-inventory adapter.
 * Accepts a normalized HostInventoryRecord and either creates a new
 * asset or updates an existing one keyed on (source, external_id) in
 * asset_external_sources.
 *
 * The write path is deliberately dumb about vendors: no adapter-specific
 * logic lives here. Adapters do their own normalization and hand a
 * clean record over. Per-instance target mappings (configured via
 * MappingTargets on the settings page) let admins redirect each
 * normalized field to a custom field or skip it entirely.
 * SyncHostFromAdapter reads those overrides and dispatches values
 * accordingly.
 */
class SyncHostFromAdapter
{
    public static function run(HostInventoryRecord $record): Asset
    {
        return DB::transaction(function () use ($record) {
            $instance = SyncAdapterInstance::query()->where('slug', $record->sourceKey)->first();
            $mapping = self::loadMapping($instance);

            [$asset, $isNew] = self::provisionAsset($record, $instance);

            $externalUpdates = [];
            self::applyStandardFieldMappings($asset, $externalUpdates, $record, $instance, $mapping);
            self::applyExtraFieldMappings($asset, $record, $instance, $mapping);

            $previousExternal = $isNew ? [] : self::loadExternalSourceInventory($asset->id);
            $wasDirty = $asset->isDirty();
            $asset->saveOrFail();

            // External-source column writes gather during the mapping
            // loop and go out together so we make one row-write per
            // sync instead of N updates.
            if ($externalUpdates !== []) {
                self::upsertExternalSource($asset, $externalUpdates);
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
     * a different Snipe-IT company.
     *
     * @return array{0: Asset, 1: bool} Tuple of the resolved asset and an "isNew" flag.
     */
    private static function provisionAsset(HostInventoryRecord $record, ?SyncAdapterInstance $instance): array
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

        $asset = self::createShellAsset($record, $instance);

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
            if ($adapterForDirection instanceof ConfigurableAdapter
                && ! in_array($adapterForDirection->directionFor($field), ['pull', 'both'], true)) {
                continue;
            }

            $value = self::recordValueFor($record, $field);
            self::writeToTarget($asset, $externalUpdates, $target, $value, $instance);
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
        if (! $adapter instanceof ConfigurableAdapter) {
            return;
        }

        $extraFields = $adapter->extraFields();
        foreach (array_keys($extraFields) as $extraKey) {
            $target = $mapping[$extraKey] ?? 'skip';
            if ($target === 'skip') {
                continue;
            }
            $isCustom = str_starts_with($target, 'custom:');
            $isNative = str_starts_with($target, 'native:');
            if (! $isCustom && ! $isNative) {
                continue;
            }

            $raw = $record->extra[$extraKey] ?? null;
            $type = self::extraFieldType($extraFields, $extraKey);

            // Booleans render '0' when raw is false. other types
            // treat blank strings as null. Guard on raw-is-null so
            // we don't blank existing data for partially populated
            // payloads.
            if ($raw === null && $type !== 'boolean') {
                continue;
            }

            $value = self::stringifyExtra($raw, $type);
            if ($value === null) {
                continue;
            }

            if ($isCustom) {
                self::writeCustom($asset, (int) substr($target, 7), $value);

                continue;
            }
            self::writeNative($asset, substr($target, 7), $value, $instance);
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
            if ($adapter instanceof ConfigurableAdapter) {
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
    private static function loadExternalSourceInventory(int $assetId): array
    {
        $row = DB::table('asset_external_sources')->where('asset_id', $assetId)->first();
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
        if ($adapter instanceof ConfigurableAdapter) {
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
     * ('text', 'boolean').
     *
     * @param  array<string, string|array{label: string, type?: string}>  $extraFields
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
    private static function createShellAsset(HostInventoryRecord $record, ?SyncAdapterInstance $instance): Asset
    {
        $asset = new Asset;
        $asset->name = $record->sourceKey.'-'.$record->sourceId;
        $asset->model_id = self::resolveModelId($record, $instance);
        $asset->status_id = self::resolveStatusId($instance);
        $asset->asset_tag = self::resolveAssetTag($record, $instance);
        $asset->company_id = self::resolveCompanyId($record, $instance);
        $asset->save();

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
            if ($adapter instanceof ConfigurableAdapter) {
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
     * company. See ConfigurableAdapter::groupMappings().
     */
    private static function resolveCompanyId(HostInventoryRecord $record, ?SyncAdapterInstance $instance): ?int
    {
        if ($instance !== null && $record->vendorGroupId !== null && $record->vendorGroupId !== '') {
            $adapter = $instance->adapter();
            if ($adapter instanceof ConfigurableAdapter && $adapter->supportsGroupScoping()) {
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
     * assets keep their tag across reinstalls. See ConfigurableAdapter
     * -> assetTagPattern() for the storage side.
     */
    private static function resolveAssetTag(HostInventoryRecord $record, ?SyncAdapterInstance $instance): string
    {
        $adapter = $instance?->adapter();
        if ($adapter instanceof ConfigurableAdapter) {
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
            'last_seen' => $record->lastSeen?->toDateTimeString(),
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
    private static function writeToTarget(Asset $asset, array &$externalUpdates, string $target, mixed $value, ?SyncAdapterInstance $instance = null): void
    {
        if ($value === null && $target !== 'native:model') {
            // Nothing to write. Skip so we don't blank existing data on
            // partial syncs.
            return;
        }

        [$type, $id] = array_pad(explode(':', $target, 2), 2, '');

        switch ($type) {
            case 'native':
                self::writeNative($asset, $id, $value, $instance);
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

    private static function writeNative(Asset $asset, string $column, mixed $value, ?SyncAdapterInstance $instance = null): void
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
                if ($value !== null && $value !== '') {
                    $asset->asset_tag = $value;
                }
                break;
            case 'notes':
                // Same non-empty guard as asset_tag. Reachable when
                // an admin routes a text-like extra (fleet_labels,
                // kandji_blueprint_id, etc.) to native:notes.
                if ($value !== null && $value !== '') {
                    $asset->notes = $value;
                }
                break;
            case 'model':
                // Model comes as a string name. Resolve or auto-create
                // using the instance's configured default category
                // (or fall back to "Discovered Hardware" when unset).
                if ($value !== null && $value !== '') {
                    $asset->model_id = self::resolveModelIdByName((string) $value, $instance);
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

        $asset->{$field->db_column} = $value;
    }

    /**
     * Write the inventory columns onto the asset's external-source
     * row. The identity columns (source, external_id, company_id)
     * are already written at create time and never change here.
     * updateOrInsert covers the edge case where the identity row was
     * somehow deleted between the create step and this write.
     */
    private static function upsertExternalSource(Asset $asset, array $updates): void
    {
        DB::table('asset_external_sources')->updateOrInsert(
            ['asset_id' => $asset->id],
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
    private static function resolveModelId(HostInventoryRecord $record, ?SyncAdapterInstance $instance = null): ?int
    {
        if ($record->hardwareModel === null || $record->hardwareModel === '') {
            return null;
        }

        return self::resolveModelIdByName($record->hardwareModel, $instance);
    }

    private static function resolveModelIdByName(string $modelName, ?SyncAdapterInstance $instance = null): int
    {
        $model = AssetModel::where('name', $modelName)->first();
        if ($model !== null) {
            return $model->id;
        }

        $model = new AssetModel;
        $model->name = $modelName;
        $model->category_id = self::categoryIdFor($instance);
        $model->save();

        return $model->id;
    }

    /**
     * Category id auto-created models get hung off. Uses the
     * adapter's configured default_category_id when set (and the
     * category still exists), else falls back to a get-or-create
     * "Discovered Hardware" category so we always produce a valid
     * category_id (AssetModel requires one).
     */
    private static function categoryIdFor(?SyncAdapterInstance $instance): int
    {
        if ($instance !== null) {
            $adapter = $instance->adapter();
            if ($adapter instanceof ConfigurableAdapter) {
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
        if (! $adapter instanceof ConfigurableAdapter) {
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
        ConfigurableAdapter $adapter,
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
        $asset->saveOrFail();

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
        $asset->saveOrFail();

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
