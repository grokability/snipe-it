<?php

namespace App\SyncAdapters\Support;

use App\Models\CustomField;

/**
 * Enumerates the target options each normalized host-inventory field can
 * be mapped to. Every adapter reads the same normalized shape (hostname,
 * serial, model, mac, ip, os, os_version, last_seen) and hands it to
 * SyncHostFromAdapter. per-instance overrides let admins redirect each
 * field to a different Snipe-IT destination (native column, side table,
 * custom field, or skip).
 *
 * Target encoding is `<type>:<id>` so the value stays compact and
 * self-describing when stored in sync_adapter_settings:
 *   - skip                      -> don't write this field
 *   - native:name               -> asset.name
 *   - native:serial             -> asset.serial
 *   - native:model              -> AssetModel (auto-created if missing)
 *   - external:mac              -> asset_external_sources.primary_mac
 *   - external:ip               -> asset_external_sources.primary_ip
 *   - external:os               -> asset_external_sources.os
 *   - external:os_version       -> asset_external_sources.os_version
 *   - external:last_seen        -> asset_external_sources.last_seen
 *   - custom:{id}               -> assets._snipeit_<db_column>_<id>
 */
class MappingTargets
{
    /** Every normalized field the sync pipeline knows about. */
    public const FIELDS = ['hostname', 'serial', 'asset_tag', 'model', 'mac', 'ip', 'os', 'os_version', 'last_seen'];

    /**
     * The shipped default target for a field (before any per-instance
     * override). Used by SyncHostFromAdapter as the fallback.
     */
    public static function defaultTarget(string $field): string
    {
        return match ($field) {
            'hostname' => 'native:name',
            'serial' => 'native:serial',
            'model' => 'native:model',
            'mac' => 'external:mac',
            'ip' => 'external:ip',
            'os' => 'external:os',
            'os_version' => 'external:os_version',
            'last_seen' => 'external:last_seen',
            // Vendor-side asset tag mapping to Snipe-IT's asset_tag
            // column is the useful default. writeNative()'s
            // "only overwrite when vendor sent a non-empty tag" guard
            // means admin-curated tags survive vendor payloads that
            // happen to omit the field.
            'asset_tag' => 'native:asset_tag',
            default => 'skip',
        };
    }

    /**
     * Dropdown options for a given normalized field, keyed by target
     * encoding and valued by human-readable label. Custom fields whose
     * element type doesn't fit the normalized field's value shape are
     * filtered out.
     *
     * @return array<string, string>
     */
    public static function options(string $field): array
    {
        $options = ['skip' => trans('admin/settings/sync_adapters.target_skip')];

        $default = self::defaultTarget($field);
        if ($default !== 'skip') {
            $options[$default] = self::labelFor($default).' '.trans('admin/settings/sync_adapters.target_default_suffix');
        }

        foreach (self::compatibleCustomFields($field) as $customField) {
            $options['custom:'.$customField->id] = trans('admin/settings/sync_adapters.target_custom_prefix').': '.$customField->name;
        }

        return $options;
    }

    /**
     * Human-readable label for a stored target value, used when
     * rendering the current selection outside a dropdown context.
     */
    public static function labelFor(string $target): string
    {
        if ($target === 'skip') {
            return trans('admin/settings/sync_adapters.target_skip');
        }

        [$type, $id] = array_pad(explode(':', $target, 2), 2, '');

        return match ($type) {
            'native' => trans('admin/settings/sync_adapters.target_native_'.$id),
            'external' => trans('admin/settings/sync_adapters.target_external_'.$id),
            'custom' => trans('admin/settings/sync_adapters.target_custom_prefix').': '
                .(CustomField::find((int) $id)?->name ?? '?'),
            default => $target,
        };
    }

    /**
     * Dropdown options for an adapter-specific extra field. Extras
     * can only be routed to a custom field or skipped. `type` filters
     * the custom-field target pool: 'text' offers text/textarea/
     * markdown-textarea fields, 'boolean' offers checkbox fields.
     *
     * `$adminDefined` narrows the target pool to skip + custom-only
     * for tenant-defined vendor extras (Kaseya VSA 10 custom fields,
     * ServiceNow variables, etc). Those hold arbitrary admin-labeled
     * key/value data and never make sense as a Snipe-IT native
     * column, so we don't show the native branch at all. Adapter-
     * declared extras (fleet_labels, kandji_blueprint_id) still get
     * native:asset_tag / native:notes as options because a real
     * admin use-case exists for routing labels/blueprints to those
     * slots.
     *
     * @return array<string, string>
     */
    public static function optionsForExtra(string $type = 'text', bool $adminDefined = false): array
    {
        $options = ['skip' => trans('admin/settings/sync_adapters.target_skip')];

        // Adapter-declared text-type extras can also route into native
        // asset columns that hold arbitrary strings. Admins whose
        // vendor stores per-device metadata in a labels / blueprint /
        // team field may want that landing in asset_tag or notes
        // rather than a custom field. asset_tag is uniqueness-
        // constrained so the sync path relies on the vendor value
        // being unique per host when this target is picked. native:
        // model is here so admins can route a vendor's friendlier
        // "marketing name" field (Fleet's hardware_marketing_name,
        // ABM's deviceModel) to the standard AssetModel column when
        // the vendor's default `hardwareModel` value would be the
        // less-friendly SMBIOS identifier or SKU. Not appropriate
        // for list-typed extras like fleet_labels which stringify to
        // comma-joined sets. Boolean-typed extras stay custom-only
        // because no native asset column carries a boolean semantic.
        // Admin-defined extras stay custom-only for the same reason
        // (arbitrary tenant-labeled data doesn't belong in native
        // slots).
        if ($type !== 'boolean' && ! $adminDefined) {
            foreach (['asset_tag', 'model', 'notes'] as $nativeColumn) {
                $options['native:'.$nativeColumn] = trans('admin/settings/sync_adapters.target_native_'.$nativeColumn);
            }
        }

        $customFields = match ($type) {
            'boolean' => self::checkboxCustomFields(),
            default => self::textLikeCustomFields(),
        };

        foreach ($customFields as $customField) {
            $options['custom:'.$customField->id] = trans('admin/settings/sync_adapters.target_custom_prefix').': '.$customField->name;
        }

        return $options;
    }

    /**
     * Custom fields whose element type can plausibly hold this
     * normalized field's value. For MVP: text/textarea for any string,
     * date_picker/datetime_picker for last_seen. Extend as we hit
     * real-world requests.
     *
     * @return \Illuminate\Support\Collection<int, CustomField>
     */
    private static function compatibleCustomFields(string $field): \Illuminate\Support\Collection
    {
        $textLike = ['text', 'textarea', 'markdown-textarea'];
        $dateLike = ['date_picker', 'datetime_picker'];

        $accepted = match ($field) {
            'last_seen' => $dateLike,
            default => $textLike,
        };

        return CustomField::query()
            ->whereIn('element', $accepted)
            ->orderBy('name')
            ->get();
    }

    /**
     * Text-like custom fields, used as the target pool for extras.
     * Extras are stringified before write so any text-like field can
     * hold whatever the vendor emits.
     *
     * @return \Illuminate\Support\Collection<int, CustomField>
     */
    private static function textLikeCustomFields(): \Illuminate\Support\Collection
    {
        return CustomField::query()
            ->whereIn('element', ['text', 'textarea', 'markdown-textarea'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Checkbox-style custom fields, used as the target pool for
     * boolean-typed extras (mdm_enabled, supervised, adopted, etc).
     *
     * @return \Illuminate\Support\Collection<int, CustomField>
     */
    private static function checkboxCustomFields(): \Illuminate\Support\Collection
    {
        return CustomField::query()
            ->where('element', 'checkbox')
            ->orderBy('name')
            ->get();
    }
}
