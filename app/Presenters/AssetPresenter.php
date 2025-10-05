<?php

namespace App\Presenters;

/**
 * Class AssetPresenter
 * Extended version with Purchase Order column support
 */
class AssetPresenter extends Presenter
{
    /**
     * Json Column Layout for bootstrap table
     * @return string
     */
    public static function dataTableLayout()
    {
        $layout = [
            [
                'field' => 'checkbox',
                'checkbox' => true,
                'titleTooltip' => trans('general.select_all_none'),
                'printIgnore' => true,
            ], [
                'field' => 'id',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.id'),
                'visible' => false,
            ], [
                'field' => 'company',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.company'),
                'visible' => false,
                'formatter' => 'companiesLinkObjFormatter',
            ], [
                'field' => 'name',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/form.name'),
                'visible' => true,
                'formatter' => 'hardwareLinkFormatter',
            ], [
                'field' => 'image',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/hardware/table.image'),
                'visible' => true,
                'formatter' => 'imageFormatter',
            ], [
                'field' => 'asset_tag',
                'searchable' => true,
                'sortable' => true,
                'switchable' => false,
                'title' => trans('admin/hardware/table.asset_tag'),
                'visible' => true,
                'formatter' => 'hardwareLinkFormatter',
            ], [
                'field' => 'serial',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/form.serial'),
                'visible' => true,
                'formatter' => 'hardwareLinkFormatter',
            ],  [
                'field' => 'model',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/form.model'),
                'visible' => true,
                'formatter' => 'modelsLinkObjFormatter',
            ], [
                'field' => 'model_number',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/models/table.modelnumber'),
                'visible' => false,
            ], [
                'field' => 'category',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.category'),
                'visible' => true,
                'formatter' => 'categoriesLinkObjFormatter',
            ], [
                'field' => 'status_label',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/table.status'),
                'visible' => true,
                'formatter' => 'statuslabelsLinkObjFormatter',
            ], [
                'field' => 'assigned_to',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/form.checkedout_to'),
                'visible' => true,
                'formatter' => 'polymorphicItemFormatter',
            ], [
                'field' => 'employee_number',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('general.employee_number'),
                'visible' => false,
                'formatter' => 'employeeNumFormatter',
            ],[
                'field' => 'jobtitle',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/users/table.title'),
                'visible' => false,
                'formatter' => 'jobtitleFormatter',
            ], [
                'field' => 'location',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/table.location'),
                'visible' => true,
                'formatter' => 'deployedLocationFormatter',
            ], [
                'field' => 'rtd_location',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/form.default_location'),
                'visible' => false,
                'formatter' => 'deployedLocationFormatter',
            ], [
                'field' => 'manufacturer',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.manufacturer'),
                'visible' => false,
                'formatter' => 'manufacturersLinkObjFormatter',
            ], [
                'field' => 'supplier',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.supplier'),
                'visible' => false,
                'formatter' => 'suppliersLinkObjFormatter',
            ], [
                // NEW: Purchase Order column
                'field' => 'purchase_order',
                'searchable' => true,
                'sortable' => true,
                'title' => 'Purchase Order',
                'visible' => true,
                'formatter' => 'purchaseOrderLinkFormatter',
            ], [
                'field' => 'purchase_date',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.purchase_date'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'age',
                'searchable' => false,
                'sortable' => false,
                'visible' => false,
                'title' => trans('general.age'),
            ], [
                'field' => 'purchase_cost',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.purchase_cost'),
                'footerFormatter' => 'sumFormatter',
                'class' => 'text-right',
            ], [
                "field" => "book_value",
                "searchable" => false,
                "sortable" => false,
                "title" => trans('admin/hardware/table.book_value'),
                "footerFormatter" => 'sumFormatter',
                "class" => "text-right",
            ],[
                'field' => 'order_number',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.order_number'),
                'formatter' => 'orderNumberObjFilterFormatter',
            ], [
                'field' => 'eol',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('admin/hardware/form.eol_rate'),
            ],
            [
                'field' => 'asset_eol_date',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('admin/hardware/form.eol_date'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'warranty_months',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('admin/hardware/form.warranty'),
            ], [
                'field' => 'warranty_expires',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('admin/hardware/form.warranty_expires'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'created_at',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.created_at'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'updated_at',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.updated_at'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'last_audit_date',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.last_audit'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'next_audit_date',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.next_audit_date'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'last_checkout',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('admin/hardware/table.checkout_date'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'expected_checkin',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('admin/hardware/form.expected_checkin'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'checkin_counter',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.checkin_counter'),
            ], [
                'field' => 'checkout_counter',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.checkout_counter'),
            ], [
                'field' => 'requests_counter',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.requests_counter'),
            ], [
                'field' => 'notes',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.notes'),
                'formatter' => 'notesFormatter',
            ],

        ];

        return json_encode($layout);
    }

    // ... rest of the original AssetPresenter methods would go here
    // For now, we'll just include the essential dataTableLayout method
}
