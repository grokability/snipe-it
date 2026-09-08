<?php

namespace App\SyncAdapters\Support;

use App\Models\Asset;
use App\Models\CustomField;

/**
 * Renders a multi-field notes blob from an Asset using a
 * placeholder template. Used by PushableAdapter implementations
 * whose vendor has a single freeform "notes" field per device and
 * admins want to push a composed summary (asset_tag + status +
 * assigned user + custom fields, etc.) rather than one Snipe-IT
 * field to one vendor field.
 *
 * Placeholders use `{key}` syntax with a fixed vocabulary. Custom
 * fields are addressed via `{custom.field_name}` where `field_name`
 * is the exact CustomField name (case-sensitive). Unknown keys and
 * null values render as empty strings so a template with references
 * to missing custom fields degrades gracefully.
 */
class NotesComposer
{
    /**
     * Compose the notes value for an asset from the template.
     * Returns the empty string when the template is blank so
     * callers can gate on `''` for "nothing to push".
     */
    public static function compose(Asset $asset, string $template): string
    {
        if (trim($template) === '') {
            return '';
        }

        // Match anything between braces that isn't itself a brace.
        // Permissive so custom field names with spaces / punctuation
        // ({custom.Cost Center}, {custom.Warranty (extended)}) match
        // cleanly. The resolver decides validity.
        return preg_replace_callback(
            '/\{([^{}]+)\}/',
            fn (array $m) => (string) self::resolve($asset, $m[1]),
            $template,
        ) ?? '';
    }

    /**
     * Resolve a single placeholder key against the asset. Native
     * columns and relationship-derived values are hardcoded. Custom
     * fields go through the `custom.field_name` path.
     */
    private static function resolve(Asset $asset, string $key): string
    {
        if (str_starts_with($key, 'custom.')) {
            return self::resolveCustomField($asset, substr($key, 7));
        }

        return match ($key) {
            'asset_tag' => (string) ($asset->asset_tag ?? ''),
            'name' => (string) ($asset->name ?? ''),
            'serial' => (string) ($asset->serial ?? ''),
            'model' => (string) ($asset->model?->name ?? ''),
            'model_number' => (string) ($asset->model?->model_number ?? ''),
            'manufacturer' => (string) ($asset->model?->manufacturer?->name ?? ''),
            'category' => (string) ($asset->model?->category?->name ?? ''),
            'status' => (string) ($asset->status?->name ?? ''),
            'status_type' => (string) ($asset->status?->getStatuslabelType() ?? ''),
            'assigned_to' => (string) ($asset->assignedTo?->present()?->name() ?? ''),
            'assigned_to_email' => (string) ($asset->assignedTo?->email ?? ''),
            'assigned_to_username' => (string) ($asset->assignedTo?->username ?? ''),
            'location' => (string) ($asset->location?->name ?? ''),
            'company' => (string) ($asset->company?->name ?? ''),
            'supplier' => (string) ($asset->supplier?->name ?? ''),
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
    }

    /**
     * Look up a custom field value by name. Snipe-IT custom fields
     * store per-asset values on the asset row via a `_snipeit_...`
     * db_column, so we resolve the CustomField -> db_column ->
     * $asset->$dbColumn.
     */
    private static function resolveCustomField(Asset $asset, string $fieldName): string
    {
        $field = CustomField::query()->where('name', $fieldName)->first();
        if ($field === null || $field->db_column === null) {
            return '';
        }

        $value = $asset->getAttribute($field->db_column);

        return $value === null ? '' : (string) $value;
    }
}
