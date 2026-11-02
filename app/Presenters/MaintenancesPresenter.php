<?php

namespace App\Presenters;

/**
 * Class MaintenancesPresenter
 *
 * @property \App\Models\Maintenance $model
 */
class MaintenancesPresenter extends Presenter
{
    /**
     * Json Column Layout for bootstrap table. Pass fields to drop
     * from the layout in $hide_fields.
     *
     * @param  array<int, string>  $hide_fields
     * @return string
     */
    public static function dataTableLayout(array $hide_fields = [])
    {
        $layout = [
            [
                'field' => 'checkbox',
                'scope' => 'col',
                'checkbox' => true,
                'formatter' => 'checkboxEnabledFormatter',
                'titleTooltip' => trans('general.select_all_none'),
                'printIgnore' => true,
                'class' => 'hidden-print',
            ],
            [
                'field' => 'id',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.id'),
                'visible' => false,
            ], [
                'field' => 'name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.name'),
                'visible' => true,
                'formatter' => 'maintenancesLinkFormatter',
            ],
            [
                'field' => 'image',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.image'),
                'visible' => true,
                'formatter' => 'imageFormatter',
            ],
            [
                'field' => 'company',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/companies/table.title'),
                'visible' => false,
                'formatter' => 'companiesLinkObjFormatter',
            ], [
                'field' => 'asset_name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/maintenances/table.asset_name'),
                'formatter' => 'assetNameLinkFormatter',
            ], [
                'field' => 'asset_tag',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/table.asset_tag'),
                'formatter' => 'assetTagLinkFormatter',
            ], [
                'field' => 'serial',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/table.serial'),
                'formatter' => 'assetSerialLinkFormatter',
            ], [
                'field' => 'status_label',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/table.status'),
                'visible' => true,
                'formatter' => 'statuslabelsLinkObjFormatter',
            ], [
                'field' => 'model',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/hardware/form.model'),
                'visible' => false,
                'formatter' => 'modelsLinkObjFormatter',
            ], [
                'field' => 'model.model_number',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.model_no'),
                'visible' => true,
            ], [
                'field' => 'assigned_to',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/form.checkedout_to'),
                'visible' => true,
                'formatter' => 'polymorphicItemFormatter',
            ],
            [
                'field' => 'supplier',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.supplier'),
                'visible' => false,
                'formatter' => 'suppliersLinkObjFormatter',
            ], [
                'field' => 'location',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.location'),
                'formatter' => 'locationsLinkObjFormatter',
            ], [
                'field' => 'maintenance_type_details',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/maintenances/form.asset_maintenance_type'),
                'visible' => true,
                'formatter' => 'maintenanceTypesLinkObjFormatter',
            ], [
                'field' => 'responsible_party',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/maintenances/form.responsible_party'),
                'visible' => false,
                'formatter' => 'usersLinkObjFormatter',
            ], [
                'field' => 'checked_out_to_at_creation',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/maintenances/form.checked_out_to_at_creation'),
                'visible' => false,
            ], [
                'field' => 'completed_at',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/maintenances/form.completed_at'),
                'visible' => false,
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'completed_by',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/maintenances/form.completed_by'),
                'visible' => false,
                'formatter' => 'usersLinkObjFormatter',
            ], [
                'field' => 'start_date',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/maintenances/form.start_date'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'expected_completion_date',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/maintenances/form.completion_date'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'url',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.url'),
                'formatter' => 'externalLinkFormatter',
            ], [
                'field' => 'notes',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/maintenances/form.notes'),
            ], [
                'field' => 'is_warranty',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/maintenances/table.is_warranty'),
                'formatter' => 'trueFalseFormatter',
            ], [
                'field' => 'cost',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/maintenances/form.cost'),
                'class' => 'text-right',
                'footerFormatter' => 'sumFormatter',
            ], [
                'field' => 'created_by',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.created_by'),
                'visible' => false,
                'formatter' => 'usersLinkObjFormatter',
            ], [
                'field' => 'created_at',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.created_at'),
                'visible' => false,
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'updated_at',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.updated_at'),
                'visible' => false,
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'actions',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'visible' => true,
                'formatter' => 'maintenancesActionsFormatter',
                'printIgnore' => true,
            ],
        ];

        if ($hide_fields !== []) {
            $layout = array_values(array_filter(
                $layout,
                fn (array $col) => ! in_array($col['field'], $hide_fields, true),
            ));
        }

        return json_encode($layout);
    }

    public static function reportLayout()
    {
        $layout = [
            [
                'field' => 'company',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/companies/table.title'),
                'visible' => false,
            ],
            [
                'field' => 'asset_tag',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/table.asset_tag'),
            ],
            [
                'field' => 'asset_name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/maintenances/table.asset_name'),
            ],
            [
                'field' => 'name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.name'),
                'visible' => true,
            ],
            [
                'field' => 'image',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.image'),
                'visible' => true,
                'formatter' => 'imageFormatter',
            ],
            [
                'field' => 'serial',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/table.serial'),
            ], [
                'field' => 'status_label',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/table.status'),
                'visible' => true,
            ], [
                'field' => 'model',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/hardware/form.model'),
                'visible' => false,
            ], [
                'field' => 'model_number',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.model_no'),
                'visible' => true,
            ], [
                'field' => 'assigned_to',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/form.checkedout_to'),
                'visible' => true,
            ],
            [
                'field' => 'supplier',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.supplier'),
                'visible' => false,
            ], [
                'field' => 'location',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.location'),
            ], [
                'field' => 'maintenance_type_details',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/maintenances/form.asset_maintenance_type'),
                'visible' => true,
                'formatter' => 'maintenanceTypesLinkObjFormatter',
            ], [
                'field' => 'responsible_party',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/maintenances/form.responsible_party'),
                'visible' => false,
                'formatter' => 'usersLinkObjFormatter',
            ], [
                'field' => 'checked_out_to_at_creation',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/maintenances/form.checked_out_to_at_creation'),
                'visible' => false,
            ], [
                'field' => 'start_date',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/maintenances/form.start_date'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'expected_completion_date',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/maintenances/form.completion_date'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'asset_maintenance_time',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/maintenances/form.asset_maintenance_time'),
            ], [
                'field' => 'completed_at',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/maintenances/form.completed_at'),
                'visible' => false,
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'completed_by',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/maintenances/form.completed_by'),
                'visible' => false,
                'formatter' => 'usersLinkObjFormatter',
            ], [
                'field' => 'url',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.url'),
                'formatter' => 'externalLinkFormatter',
            ], [
                'field' => 'notes',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/maintenances/form.notes'),
            ], [
                'field' => 'is_warranty',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/maintenances/table.is_warranty'),
                'formatter' => 'trueFalseFormatter',
            ], [
                'field' => 'cost',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/maintenances/form.cost'),
                'class' => 'text-right',
                'footerFormatter' => 'sumFormatter',
            ], [
                'field' => 'created_by',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.created_by'),
                'visible' => false,
            ], [
                'field' => 'created_at',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.created_at'),
                'visible' => false,
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'updated_at',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.updated_at'),
                'visible' => false,
                'formatter' => 'dateDisplayFormatter',
            ],
        ];

        return json_encode($layout);
    }

    /**
     * Display name for the unified calendar. Renders as
     * "<maintenance name> - <asset name or unknown>" so the event
     * tile carries enough context to identify what and where without
     * clicking through.
     */
    public function name()
    {
        $itemLabel = $this->model->item
            ? ($this->model->item->display_name ?? $this->model->item->name ?? '')
            : trans('general.unknown');

        return sprintf('%s - %s', $this->model->name, $itemLabel);
    }

    /**
     * Deep link used by the calendar event tile. Routes to the
     * maintenance show page.
     */
    public function calendarUrl(): ?string
    {
        return route('maintenances.show', $this->model->id);
    }

    /**
     * Color precedence for the calendar tile: maintenance type's
     * tag_color when the admin has set one so tenants can color-code
     * by "recall vs upgrade vs routine service". Otherwise null and
     * the frontend paints from a per-event_type CSS palette.
     */
    public function calendarColor(): ?string
    {
        return $this->model->maintenanceType?->tag_color;
    }
}
