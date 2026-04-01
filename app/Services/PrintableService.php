<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\CustomField;
use App\Models\Printable;

/**
 * Service class responsible for rendering Printable HTML templates.
 *
 * Variable substitution uses `{variable_name}` placeholders.
 *
 * Supported core variables:
 *   {asset_tag}, {asset_name}, {serial}, {notes}, {order_number},
 *   {purchase_date}, {purchase_cost}, {warranty_months},
 *   {model_name}, {model_number},
 *   {manufacturer_name},
 *   {category_name},
 *   {location_name}, {default_location_name},
 *   {company_name},
 *   {assigned_to},
 *   {status}
 *
 * Custom fields are accessible via `{custom_field_SLUG}` where SLUG is the
 * database column name of the custom field (e.g. `{custom_field__snipeit_phone_number_1}`).
 */
class PrintableService
{
    /**
     * Render a printable template for a single asset.
     *
     * @param  Printable  $printable  The template to render.
     * @param  Asset       $asset      The asset whose data will be substituted.
     * @return string                  Rendered HTML.
     */
    public function render(Printable $printable, Asset $asset): string
    {
        $asset->loadMissing([
            'model.category',
            'model.manufacturer',
            'location',
            'defaultLoc',
            'company',
            'assignedTo',
            'assetstatus',
            'model.fieldset.fields',
        ]);

        $variables = $this->buildVariableMap($asset);

        $keys   = array_map(fn (string $k): string => '{'.$k.'}', array_keys($variables));
        $values = array_values($variables);

        return str_replace($keys, $values, $printable->content);
    }

    /**
     * Render a printable template for multiple assets and concatenate the results.
     *
     * Each rendered asset is wrapped in a `<div class="printable-asset-page">` element
     * so CSS page-break rules can be applied for printing.
     *
     * @param  Printable                           $printable
     * @param  \Illuminate\Support\Collection<int, Asset>  $assets
     * @return string  Combined HTML.
     */
    public function renderBulk(Printable $printable, \Illuminate\Support\Collection $assets): string
    {
        return $assets
            ->map(fn (Asset $asset): string => '<div class="printable-asset-page">'.$this->render($printable, $asset).'</div>')
            ->implode("\n");
    }

    /**
     * Build an associative map of variable name → value for a given asset.
     *
     * @return array<string, string>
     */
    private function buildVariableMap(Asset $asset): array
    {
        $map = [
            'asset_tag'            => (string) ($asset->asset_tag ?? ''),
            'asset_name'           => (string) ($asset->name ?? ''),
            'serial'               => (string) ($asset->serial ?? ''),
            'notes'                => (string) ($asset->notes ?? ''),
            'order_number'         => (string) ($asset->order_number ?? ''),
            'purchase_cost'        => (string) ($asset->purchase_cost ?? ''),
            'warranty_months'      => (string) ($asset->warranty_months ?? ''),

            // Dates
            'purchase_date'        => $asset->purchase_date
                ? $asset->purchase_date->format('Y-m-d')
                : '',

            // Model
            'model_name'           => (string) ($asset->model?->name ?? ''),
            'model_number'         => (string) ($asset->model?->model_number ?? ''),

            // Manufacturer
            'manufacturer_name'    => (string) ($asset->model?->manufacturer?->name ?? ''),

            // Category
            'category_name'        => (string) ($asset->model?->category?->name ?? ''),

            // Location
            'location_name'        => (string) ($asset->location?->name ?? ''),
            'default_location_name' => (string) ($asset->defaultLoc?->name ?? ''),

            // Company
            'company_name'         => (string) ($asset->company?->name ?? ''),

            // Assigned to
            'assigned_to'          => $asset->assignedTo
                ? (string) ($asset->assignedTo->display_name ?? '')
                : '',

            // Status
            'status'               => (string) ($asset->assetstatus?->name ?? ''),
        ];

        // Custom fields
        if ($asset->model?->fieldset?->fields) {
            foreach ($asset->model->fieldset->fields as $field) {
                /** @var CustomField $field */
                $columnName = $field->db_column_name();
                $value      = $asset->{$columnName};

                if ($field->field_encrypted && $value) {
                    try {
                        $value = \Illuminate\Support\Facades\Crypt::decrypt($value);
                    } catch (\Exception) {
                        $value = '';
                    }
                }

                $map['custom_field_'.$columnName] = (string) ($value ?? '');
            }
        }

        return $map;
    }

    /**
     * Return an array of all supported variable placeholders with human-readable labels.
     *
     * This is used by the template editor UI to show the available variables.
     *
     * @param  \Illuminate\Support\Collection<int, CustomField>  $customFields
     * @return array<string, string>  Keyed by placeholder string, valued by label.
     */
    public static function availableVariables(\Illuminate\Support\Collection $customFields): array
    {
        $vars = [
            '{asset_tag}'             => trans('admin/hardware/form.tag'),
            '{asset_name}'            => trans('admin/hardware/form.name'),
            '{serial}'                => trans('admin/hardware/form.serial'),
            '{notes}'                 => trans('general.notes'),
            '{order_number}'          => trans('general.order_number'),
            '{purchase_date}'         => trans('general.purchase_date'),
            '{purchase_cost}'         => trans('general.purchase_cost'),
            '{warranty_months}'       => trans('admin/hardware/form.warranty'),
            '{model_name}'            => trans('general.asset_model'),
            '{model_number}'          => trans('general.model_no'),
            '{manufacturer_name}'     => trans('general.manufacturer'),
            '{category_name}'         => trans('general.category'),
            '{location_name}'         => trans('general.location'),
            '{default_location_name}' => trans('admin/hardware/form.default_location'),
            '{company_name}'          => trans('general.company'),
            '{assigned_to}'           => trans('general.assigned_to'),
            '{status}'                => trans('general.status'),
        ];

        foreach ($customFields as $field) {
            /** @var CustomField $field */
            $vars['{custom_field_'.$field->db_column_name().'}'] = $field->name;
        }

        return $vars;
    }
}
