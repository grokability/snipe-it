<?php

namespace App\Presenters;

/**
 * Class ComponentPresenter
 */
class ConsumablePresenter extends Presenter
{
    /**
     * JSON column layout for bootstrap-table.
     *
     * @param  bool  $showDeletedAt  Make the deleted_at column visible
     *                               by default. Passed from the Deleted-index view so admins
     *                               see when each row was deleted. Still switchable elsewhere.
     * @param  array<int, string>  $hide_fields  Column `field` names to drop
     *                               from the layout entirely. The Archived and Deleted index
     *                               views hide `remaining` + `percent_remaining` here because
     *                               neither applies to retired or soft-deleted rows.
     * @return string
     */
    public static function dataTableLayout(bool $showDeletedAt = false, array $hide_fields = [])
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
            ], [
                'field' => 'name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => false,
                'title' => trans('general.name'),
                'visible' => true,
                'formatter' => 'consumablesLinkFormatter',
            ], [
                'field' => 'company',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.company'),
                'visible' => false,
                'formatter' => 'companiesLinkObjFormatter',
            ],

            [
                'field' => 'image',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.image'),
                'visible' => false,
                'formatter' => 'imageFormatter',
            ], [
                'field' => 'category',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.category'),
                'formatter' => 'categoriesLinkObjFormatter',
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
                'field' => 'model_number',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.model_no'),
            ], [
                'field' => 'location',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.location'),
                'formatter' => 'locationsLinkObjFormatter',
            ], [
                'field' => 'item_no',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/consumables/general.item_no'),
            ], [

                'field' => 'manufacturer',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.manufacturer'),
                'visible' => false,
                'formatter' => 'manufacturersLinkObjFormatter',
            ], [
                // "Last" prefix — see AccessoryPresenter for the
                // rationale on why the index page can't show a single
                // Purchase Date once Orders can carry many per item.
                'field' => 'purchase_date',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.last_purchase_date'),
                'visible' => true,
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'min_amt',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.min_amt'),
                'visible' => true,
                'formatter' => 'minAmtFormatter',
                'class' => 'text-right text-padding-number-cell',
            ], [
                'field' => 'qty',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/components/general.total'),
                // Total-ever-stocked is nice-to-have but Remaining +
                // Outstanding carry the daily signal. Admins who want
                // it toggle it on via the column picker.
                'visible' => false,
                'class' => 'text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ], [
                'field' => 'remaining',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('admin/components/general.remaining'),
                'visible' => true,
                'class' => 'text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ], [
                'field' => 'checked_out',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.checked_out'),
                'visible' => true,
                'class' => 'text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ], [
                'field' => 'consumed',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.consumed'),
                'visible' => true,
                'class' => 'text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ], [
                'field' => 'percent_remaining',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => '% '.trans('general.remaining'),
                'visible' => true,
                'formatter' => 'progressBarFormatter',
            ], [
                'field' => 'purchase_cost',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.last_unit_cost'),
                'visible' => true,
                'class' => 'text-right text-padding-number-cell',
                'footerFormatter' => 'sumFormatter',
            ], [
                // Field name matches transformer key + HasOrders relation).
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
                'searchable' => false,
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
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.created_by'),
                'visible' => false,
                'formatter' => 'usersLinkObjFormatter',
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
                'field' => 'deleted_at',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'visible' => $showDeletedAt,
                'title' => trans('general.deleted_at'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'change',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'visible' => true,
                'title' => trans('general.change'),
                'formatter' => 'consumablesInOutFormatter',
                'printIgnore' => true,
            ], [
                'field' => 'actions',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'visible' => true,
                'formatter' => 'consumablesActionsFormatter',
                'printIgnore' => true,
                'class' => 'hidden-print',
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

    public static function checkedOut()
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
                'field' => 'avatar',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('general.image'),
                'visible' => true,
                'formatter' => 'imageFormatter',
            ],
            [
                'field' => 'user',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('general.name'),
                'visible' => true,
                'formatter' => 'usersLinkObjFormatter',
            ],
            [
                'field' => 'created_at',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('general.date'),
                'visible' => true,
                'formatter' => 'dateDisplayFormatter',
            ],
            [
                'field' => 'note',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('general.notes'),
                'visible' => true,
            ],
            [
                'field' => 'created_by',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('general.created_by'),
                'visible' => true,
                'formatter' => 'usersLinkObjFormatter',
            ],
            [
                'field' => 'available_actions',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'formatter' => 'consumablesConsumeActionFormatter',
                'printIgnore' => true,
                'class' => 'hidden-print',
            ],
        ];

        return json_encode($layout);
    }

    /**
     * Url to view this item.
     *
     * @return string
     */
    public function viewUrl()
    {
        return route('consumables.show', $this->id);
    }

    /**
     * Generate html link to this items name.
     *
     * @return string
     */
    public function nameUrl()
    {
        return '<a href="'.route('consumables.show', $this->id).'">'.e($this->name).'</a>';
    }

    /**
     * Column layout for the consumables tab on /account/requestable.
     * Same shape as AccessoryPresenter::dataTableLayoutRequestable;
     * paired with api.consumables.requestable + ConsumablesTransformer.
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
                'title' => trans('general.name'),
                'formatter' => 'consumableRequestableNameFormatter',
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
                'title' => trans('admin/components/general.remaining'),
            ], [
                'field' => 'actions',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'formatter' => 'consumableRequestableActionsFormatter',
                'printIgnore' => true,
                'class' => 'hidden-print',
            ],
        ]);
    }
}
