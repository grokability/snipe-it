<?php

namespace App\Presenters;

/**
 * Class AssetModelPresenter
 */
class MaintenancesPresenter extends Presenter
{
    /**
     * Json Column Layout for bootstrap table
     * @return string
     */
    public static function dataTableLayout()
    {
        $layout = [
            [
                'field' => 'id',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.id'),
                'visible' => false,
            ], [
                'field' => 'name',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.name'),
                'visible' => true,
                'formatter' => 'maintenancesLinkFormatter',
            ],
            [
                'field' => 'image',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.image'),
                'visible' => true,
                'formatter' => 'imageFormatter',
            ],
            [
                'field' => 'company',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/companies/table.title'),
                'visible' => false,
                'formatter' => 'companiesLinkObjFormatter',
            ], [
                'field' => 'asset_name',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/maintenances/table.asset_name'),
                'formatter' => 'assetNameLinkFormatter',
            ], [
                'field' => 'asset_tag',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/table.asset_tag'),
                'formatter' => 'assetTagLinkFormatter',
            ], [
                'field' => 'serial',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/table.serial'),
                'formatter' => 'assetSerialLinkFormatter',
            ], [
                'field' => 'status_label',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/table.status'),
                'visible' => true,
                'formatter' => 'statuslabelsLinkObjFormatter',
            ], [
                'field' => 'model',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/hardware/form.model'),
                'visible' => false,
                'formatter' => 'modelsLinkObjFormatter',
            ], [
                'field' => 'supplier',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.supplier'),
                'visible' => false,
                'formatter' => 'suppliersLinkObjFormatter',
            ], [
                'field' => 'location',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.location'),
                'formatter' => 'locationsLinkObjFormatter',
            ], [
                'field' => 'asset_maintenance_type',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/maintenances/form.asset_maintenance_type'),
            ], [
                'field' => 'start_date',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/maintenances/form.start_date'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'completion_date',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/maintenances/form.completion_date'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'url',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.url'),
                'formatter' => 'externalLinkFormatter',
            ], [
                'field' => 'notes',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/maintenances/form.notes'),
            ], [
                'field' => 'is_warranty',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/maintenances/table.is_warranty'),
                'formatter' => 'trueFalseFormatter'
            ], [
                'field' => 'cost',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/maintenances/form.cost'),
                'class' => 'text-right',
            ], [
                'field' => 'created_by',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.created_by'),
                'visible' => false,
                'formatter' => 'usersLinkObjFormatter',
            ], [
                'field' => 'created_at',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.created_at'),
                'visible' => false,
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'updated_at',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.updated_at'),
                'visible' => false,
                'formatter' => 'dateDisplayFormatter',
            ],[
                'field' => 'actions',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'visible' => true,
                'formatter' => 'maintenancesActionsFormatter',
                'printIgnore' => true,
            ],
        ];

        return json_encode($layout);
    }
}
