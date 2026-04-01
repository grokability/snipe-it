<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Models\Asset;
use App\Models\CustomField;
use App\Models\Printable;
use App\Models\Statuslabel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;

/**
 * Service class responsible for rendering Printable HTML templates.
 *
 * Template rendering uses expression syntax with `{{ expression }}` and
 * `{% if %}` blocks.
 *
 * Supported core variables:
 *   {{ asset_tag }}, {{ asset_name }}, {{ serial }}, {{ notes }}, {{ order_number }},
 *   {{ purchase_date }}, {{ purchase_cost }}, {{ warranty_months }},
 *   {{ model_name }}, {{ model_number }},
 *   {{ manufacturer_name }},
 *   {{ category_name }},
 *   {{ location_name }}, {{ default_location_name }},
 *   {{ company_name }},
 *   {{ assigned_to }},
 *   {{ status }}
 *
 * Expression helpers include `current_date`, `current_datetime`, and
 * `checked_out_user.first_name|last_name|email|full_name|display_name`.
 *
 * Custom fields are accessible via `{{ custom_field_SLUG }}` where SLUG is the
 * database column name of the custom field (e.g. `{{ custom_field__snipeit_phone_number_1 }}`).
 */
class PrintableService
{
    public function __construct(private ?PrintableTemplateRenderer $renderer = null) {}

    /**
     * Render a printable template for a single asset.
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

        $context = $this->buildTemplateContext($asset, $this->buildVariableMap($asset));

        return $this->renderer()->render($printable->content, $context);
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

    private function renderer(): PrintableTemplateRenderer
    {
        return $this->renderer ??= new PrintableTemplateRenderer;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildTemplateContext(Asset $asset, array $legacyPlaceholders): array
    {
        return array_merge($legacyPlaceholders, [
            'current_date' => Helper::getFormattedDateObject(Carbon::now(), 'date', false) ?? '',
            'current_datetime' => Helper::getFormattedDateObject(Carbon::now(), 'datetime', false) ?? '',
            'checked_out_user' => $this->buildCheckedOutUserContext($asset),
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildCheckedOutUserContext(Asset $asset): ?array
    {
        if ($asset->assignedType() !== Asset::USER) {
            return null;
        }

        $assignedTo = $asset->assignedTo instanceof User
            ? $asset->assignedTo
            : User::withTrashed()->find($asset->assigned_to);

        if (! $assignedTo instanceof User) {
            return null;
        }

        return [
            'first_name' => (string) ($assignedTo->first_name ?? ''),
            'last_name' => (string) ($assignedTo->last_name ?? ''),
            'email' => (string) ($assignedTo->email ?? ''),
            'full_name' => (string) ($assignedTo->getFullNameAttribute() ?? ''),
            'display_name' => (string) ($assignedTo->display_name ?? ''),
        ];
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
            'assigned_to'          => $this->resolveAssignedToName($asset),

            // Status
            'status'               => $this->resolveStatusName($asset),
        ];

        // Custom fields
        if ($asset->model?->fieldset?->fields) {
            foreach ($asset->model->fieldset->fields as $field) {
                /** @var CustomField $field */
                $columnName = $field->db_column_name();
                $value      = $asset->{$columnName};

                if ($field->field_encrypted && $value) {
                    try {
                        $value = Crypt::decrypt($value);
                    } catch (\Exception) {
                        $value = '';
                    }
                }

                $map['custom_field_'.$columnName] = (string) ($value ?? '');
            }
        }

        return $map;
    }

    private function resolveAssignedToName(Asset $asset): string
    {
        if ($asset->assignedType() !== Asset::USER) {
            return '';
        }

        $assignedTo = $asset->assignedTo instanceof User
            ? $asset->assignedTo
            : User::withTrashed()->find($asset->assigned_to);

        if (! $assignedTo instanceof User) {
            return '';
        }

        $fullName = $assignedTo->getFullNameAttribute();

        return (string) ($fullName !== '' ? $fullName : ($assignedTo->display_name ?? ''));
    }

    private function resolveStatusName(Asset $asset): string
    {
        $status = $asset->assetstatus instanceof Statuslabel
            ? $asset->assetstatus
            : Statuslabel::find($asset->status_id);

        return (string) ($status->name ?? '');
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
            '{{ current_date }}'      => trans('general.date').' (localized current date)',
            '{{ current_datetime }}'  => trans('general.date').' / '.trans('general.time').' (localized current datetime)',
            '{{ checked_out_user.first_name }}' => trans('general.first_name'),
            '{{ checked_out_user.last_name }}'  => trans('general.last_name'),
            '{{ checked_out_user.email }}'      => trans('general.email'),
            '{{ asset_tag }}'             => trans('admin/hardware/form.tag'),
            '{{ asset_name }}'            => trans('admin/hardware/form.name'),
            '{{ serial }}'                => trans('admin/hardware/form.serial'),
            '{{ notes }}'                 => trans('general.notes'),
            '{{ order_number }}'          => trans('general.order_number'),
            '{{ purchase_date }}'         => trans('general.purchase_date'),
            '{{ purchase_cost }}'         => trans('general.purchase_cost'),
            '{{ warranty_months }}'       => trans('admin/hardware/form.warranty'),
            '{{ model_name }}'            => trans('general.asset_model'),
            '{{ model_number }}'          => trans('general.model_no'),
            '{{ manufacturer_name }}'     => trans('general.manufacturer'),
            '{{ category_name }}'         => trans('general.category'),
            '{{ location_name }}'         => trans('general.location'),
            '{{ default_location_name }}' => trans('admin/hardware/form.default_location'),
            '{{ company_name }}'          => trans('general.company'),
            '{{ assigned_to }}'           => trans('general.assigned_to'),
            '{{ status }}'                => trans('general.status'),
        ];

        foreach ($customFields as $field) {
            /** @var CustomField $field */
            $vars['{{ custom_field_'.$field->db_column_name().' }}'] = $field->name;
        }

        return $vars;
    }
}
