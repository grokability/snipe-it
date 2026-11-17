<?php

namespace App\Presenters;

/**
 * Class AccessoryPresenter
 */
class AccessoryPresenter extends Presenter
{
    /**
     * Json Column Layout for bootstrap table
     *
     * @return string
     */
    /**
     * JSON column layout for bootstrap-table.
     *
     * @param  array<int, string>  $hide_fields  Column field names to omit from the layout
     *                                           entirely. Used by the main /accessories list
     *                                           to skip `deleted_at`, which is always NULL on
     *                                           non-trash views (the Archived / Deleted
     *                                           status filter owns surfacing those rows).
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
            ], [
                'field' => 'id',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.id'),
                'visible' => false,
                'printIgnore' => true,
            ], [
                'field' => 'name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => false,
                'title' => trans('general.name'),
                'formatter' => 'accessoriesLinkFormatter',
            ], [
                'field' => 'image',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.image'),
                'visible' => true,
                'formatter' => 'imageFormatter',
            ], [
                'field' => 'company',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/companies/table.title'),
                'visible' => false,
                'formatter' => 'companiesLinkObjFormatter',
            ],  [
                'field' => 'category',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.category'),
                'formatter' => 'categoriesLinkObjFormatter',
            ], [
                'field' => 'model_number',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/models/table.modelnumber'),
                'formatter' => 'accessoriesLinkFormatter',
            ], [
                'field' => 'manufacturer',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.manufacturer'),
                'visible' => false,
                'formatter' => 'manufacturersLinkObjFormatter',
            ], [
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
                'field' => 'min_amt',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.min_amt'),
                'formatter' => 'minAmtFormatter',
                'class' => 'text-right text-padding-number-cell',
            ], [
                'field' => 'qty',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/accessories/general.total'),
                'footerFormatter' => 'qtySumFormatter',
                'class' => 'text-right text-padding-number-cell',
            ], [
                'field' => 'remaining',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                // Kept hidden by default so the primary "% Remaining"
                // progress bar column stays the headline signal; users
                // who want raw-count sorting can toggle this on via
                // the bootstrap-table column picker (issue #18505).
                'visible' => false,
                'title' => trans('admin/accessories/general.remaining'),
                'footerFormatter' => 'qtySumFormatter',
                'class' => 'text-right text-padding-number-cell',
            ], [
                'field' => 'checkouts_count',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'visible' => true,
                'title' => trans('general.checked_out'),
                'footerFormatter' => 'qtySumFormatter',
                'class' => 'text-right text-padding-number-cell',
            ], [
                'field' => 'percent_remaining',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => '% '.trans('general.remaining'),
                'visible' => true,
                'formatter' => 'progressBarFormatter',
            ],
            [
                // "Last" prefix reflects that purchase_date now lives
                // on Orders — an accessory can have many, so displaying
                // an unqualified "Purchase Date" on the index page is
                // misleading. The API's purchase_date sort routes
                // through OrderByLastPurchaseDate accordingly.
                'field' => 'purchase_date',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.last_purchase_date'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'purchase_cost',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.last_unit_cost'),
                'class' => 'text-right text-padding-number-cell',
            ], [
                // Field name matches the transformer key
                // ('orders') and the HasOrders relation name so advanced
                // search resolves straight through $searchableRelations.
                'field' => 'orders',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => false,
                'switchable' => true,
                'visible' => true,
                'title' => trans('general.order_number'),
                'formatter' => 'ordersSummaryFormatter',
            ], [
                'field' => 'total_cost',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.total_cost'),
                'footerFormatter' => 'sumFormatterQuantity',
                'class' => 'text-right text-padding-number-cell',
            ], [
                'field' => 'notes',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.notes'),
                'formatter' => 'notesFormatter',
            ], [
                'field' => 'requestable',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'visible' => false,
                'title' => trans('admin/hardware/general.requestable'),
                'formatter' => 'trueFalseFormatter',
            ], [
                'field' => 'archived',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'visible' => false,
                'title' => trans('general.archived'),
                'formatter' => 'trueFalseFormatter',
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
                'field' => 'deleted_at',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'visible' => true,
                'title' => trans('general.deleted_at'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'change',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'visible' => true,
                'title' => trans('general.change'),
                'formatter' => 'accessoriesInOutFormatter',
                'printIgnore' => true,
            ], [
                'field' => 'available_actions',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'formatter' => 'accessoriesActionsFormatter',
                'printIgnore' => true,
                'class' => 'hidden-print',
            ],
        ];

        if (! empty($hide_fields)) {
            $layout = array_values(array_filter($layout, function ($col) use ($hide_fields) {
                return ! in_array($col['field'], $hide_fields, true);
            }));
        }

        return json_encode($layout);
    }

    public static function assignedDataTableLayout()
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
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.id'),
                'visible' => false,
            ],
            [
                'field' => 'assigned_to',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.checked_out_to'),
                'visible' => true,
                'formatter' => 'polymorphicItemFormatter',
            ],
            [
                'field' => 'assigned_to.companies',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.companies'),
                'visible' => true,
                'formatter' => 'companiesArrayLinkFormatter',
            ],
            [
                'field' => 'note',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.notes'),
                'visible' => true,
            ],
            [
                'field' => 'created_at',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/hardware/table.checkout_date'),
                'visible' => true,
                'formatter' => 'dateDisplayFormatter',
            ],
            [
                'field' => 'created_by',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('general.created_by'),
                'visible' => false,
                'formatter' => 'usersLinkObjFormatter',
            ],
            [
                'field' => 'available_actions',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'formatter' => 'accessoriesInOutFormatter',
                'printIgnore' => true,
                'class' => 'hidden-print',
            ],
        ];

        return json_encode($layout);
    }

    public static function assignedDataTableLayoutForObject()
    {
        $layout = [
            [
                'field' => 'id',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.id'),
                'visible' => false,
            ],

            [
                'field' => 'accessory',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('general.name'),
                'visible' => true,
                'formatter' => 'accessoriesLinkObjFormatter',
            ],
            [
                'field' => 'image',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.image'),
                'visible' => true,
                'formatter' => 'imageFormatter',
            ],
            [
                'field' => 'note',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.notes'),
                'visible' => true,
            ],
            [
                'field' => 'created_at',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/hardware/table.checkout_date'),
                'visible' => true,
                'formatter' => 'dateDisplayFormatter',
            ],
            [
                'field' => 'created_by',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('general.created_by'),
                'visible' => false,
                'formatter' => 'usersLinkObjFormatter',
            ],
            [
                'field' => 'available_actions',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'formatter' => 'accessoriesInOutFormatter',
                'printIgnore' => true,
                'class' => 'hidden-print',
            ],
        ];

        return json_encode($layout);
    }

    /**
     * Pregenerated link to this accessories view page.
     *
     * @return string
     */
    public function nameUrl()
    {
        if (auth()->user()->can('view', ['\App\Models\Accessory', $this])) {
            return '<a href="'.route('accessories.show', $this->id).'">'.e($this->display_name).'</a>';
        } else {
            return e($this->display_name);
        }
    }

    /**
     * Url to view this item.
     *
     * @return string
     */
    public function viewUrl()
    {
        return route('accessories.show', $this->id);
    }

    public function name()
    {
        return $this->model->name;
    }

    /**
     * Column layout for the accessories tab on /account/requestable.
     * Feeds <x-table> via api.accessories.requestable. Row shape
     * comes from AccessoriesTransformer with assigned_to_self +
     * available_actions.request/cancel populated so the
     * accessoryRequestable*Formatter JS helpers can render the
     * request/cancel button-swap.
     */
    public static function dataTableLayoutRequestable(): string
    {
        return json_encode([
            [
                'field' => 'image',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('general.image'),
                'formatter' => 'imageFormatter',
            ], [
                'field' => 'name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/accessories/general.accessory_name'),
                'formatter' => 'accessoryRequestableNameFormatter',
            ], [
                'field' => 'category',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => false,
                'title' => trans('general.category'),
                'formatter' => 'categoriesLinkObjFormatter',
            ], [
                'field' => 'company.name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => false,
                'title' => trans('general.company'),
            ], [
                'field' => 'location.name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => false,
                'title' => trans('admin/hardware/table.location'),
            ], [
                'field' => 'remaining',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('admin/accessories/general.remaining'),
            ], [
                'field' => 'actions',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'formatter' => 'accessoryRequestableActionsFormatter',
                'printIgnore' => true,
                'class' => 'hidden-print',
            ],
        ]);
    }
}
