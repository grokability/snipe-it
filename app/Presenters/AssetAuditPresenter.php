<?php

namespace App\Presenters;

use App\Models\CustomField;

/**
 * Class AssetPresenter
 */
class AssetAuditPresenter extends Presenter
{
    /**
     * Json Column Layout for bootstrap table
     *
     * @return string
     */
    public static function dataTableLayout()
    {
        $layout = [
            [
                'field' => 'checkbox',
                'scope' => 'col',
                'checkbox' => true,
                'titleTooltip' => trans('general.select_all_none'),
                'printIgnore' => true,
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
                'field' => 'company',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.company'),
                'visible' => false,
                'formatter' => 'assetCompanyObjFilterFormatter',
            ], [
                'field' => 'name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                // Match the import wizard's Name target label
                'title' => trans('general.item_name_var', ['item' => trans('general.asset')]),
                'visible' => true,
                'formatter' => 'hardwareLinkFormatter',
            ], [
                'field' => 'file',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.image'),
                'visible' => false,
                'formatter' => 'auditImageFormatter',
            ], [
                'field' => 'asset_tag',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/table.asset_tag'),
                'visible' => true,
                'formatter' => 'hardwareLinkFormatter',
            ], [
                'field' => 'serial',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/form.serial'),
                'visible' => true,
                'formatter' => 'hardwareLinkFormatter',
            ],  [
                'field' => 'model',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/form.model'),
                'visible' => true,
                'formatter' => 'modelsLinkObjFormatter',
            ], [
                'field' => 'model_number',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/models/table.modelnumber'),
                'visible' => false,
            ], [
                'field' => 'category',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.category'),
                'visible' => false,
                'formatter' => 'categoriesLinkObjFormatter',
            ], [
                'field' => 'status_label',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/table.status'),
                'visible' => true,
                'formatter' => 'statuslabelsLinkObjFormatter',
            ], [
                'field' => 'assigned_to',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/form.checkedout_to'),
                'visible' => true,
                'formatter' => 'polymorphicItemFormatter',
            ], [
                'field' => 'location',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/table.location'),
                'visible' => true,
                'formatter' => 'deployedLocationFormatter',
            ], [
                'field' => 'rtd_location',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/form.default_location'),
                'visible' => false,
                'formatter' => 'deployedLocationFormatter',
            ], [
                'field' => 'manufacturer',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.manufacturer'),
                'visible' => false,
                'formatter' => 'manufacturersLinkObjFormatter',
            ], [
                'field' => 'purchase_date',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.purchase_date'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'purchase_cost',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.purchase_cost'),
                'footerFormatter' => 'sumFormatter',
                'class' => 'text-right',
            ], [
                'field' => 'order_number',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.order_number'),
                'formatter' => 'orderNumberObjFilterFormatter',
            ], [
                'field' => 'eol',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'visible' => false,
                'title' => trans('general.eol'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'warranty_months',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('admin/hardware/form.warranty'),
            ], [
                'field' => 'warranty_expires',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'visible' => false,
                'title' => trans('admin/hardware/form.warranty_expires'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'notes',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.notes'),

            ], [
                'field' => 'checkout_counter',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.checkouts_count'),

            ], [
                'field' => 'checkin_counter',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.checkins_count'),

            ], [
                'field' => 'requests_counter',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.user_requests_count'),

            ], [
                'field' => 'created_at',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.created_at'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'updated_at',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.updated_at'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'last_checkout',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('admin/hardware/table.checkout_date'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'expected_checkin',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('admin/hardware/form.expected_checkin'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'last_audit_date',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'visible' => true,
                'title' => trans('general.last_audit'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'next_audit_date',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'visible' => true,
                'title' => trans('general.next_audit_date'),
                'formatter' => 'dateDisplayFormatter',
            ],
        ];

        // This looks complicated, but we have to confirm that the custom fields exist in custom fieldsets
        // *and* those fieldsets are associated with models, otherwise we'll trigger
        // javascript errors on the bootstrap tables side of things, since we're asking for properties
        // on fields that will never be passed through the REST API since they're not associated with
        // models. We only pass the fieldsets that pertain to each asset (via their model) so that we
        // don't junk up the REST API with tons of custom fields that don't apply

        $fields = CustomField::assignedToInUseFieldsets()->get();

        foreach ($fields as $field) {
            $layout[] = [
                'field' => 'custom_fields.'.$field->db_column,
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'switchable' => true,
                'title' => ($field->field_encrypted == '1') ? '<i class="fas fa-lock"></i> '.e($field->name) : e($field->name),
                'formatter' => 'customFieldsFormatter',
                // Use tableExport attributes on the TD so dot-containing
                // text values stop being misread as EU-locale thousands
                // numbers on export. See issue #19595.
                'cellStyle' => 'customFieldsCellStyle',
            ];
        }

        $layout[] = [
            'field' => 'actions',
            'scope' => 'col',
            'searchable' => false,
            'sortable' => false,
            'switchable' => false,
            'title' => trans('table.actions'),
            'formatter' => 'hardwareAuditFormatter',
            'printIgnore' => true,
            'class' => 'hidden-print',
        ];

        return json_encode($layout);
    }
}
