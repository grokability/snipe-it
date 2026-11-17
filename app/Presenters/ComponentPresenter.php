<?php

namespace App\Presenters;

/**
 * Class ComponentPresenter
 */
class ComponentPresenter extends Presenter
{
    /**
     * JSON column layout for bootstrap-table.
     *
     * @param  bool  $showDeletedAt  Make the deleted_at column visible
     *                               by default. Passed from the Deleted-index view so admins
     *                               see when each row was deleted. Still switchable elsewhere.
     * @return string
     */
    public static function dataTableLayout(bool $showDeletedAt = false)
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
                'title' => trans('general.name'),
                'visible' => true,
                'formatter' => 'componentsLinkFormatter',
            ],
            [
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
                'field' => 'serial',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/form.serial'),
                'formatter' => 'componentsLinkFormatter',
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
                'title' => trans('admin/models/table.modelnumber'),
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
                'field' => 'location',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.location'),
                'formatter' => 'locationsLinkObjFormatter',
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
                'class' => 'text-right text-padding-number-cell',
                'formatter' => 'minAmtFormatter',
            ], [
                'field' => 'qty',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('admin/components/general.total'),
                'visible' => true,
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
                'class' => 'text-right',
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
                'searchable' => false,
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
            ],
        ];

        $layout[] = [
            'field' => 'checkincheckout',
            'scope' => 'col',
            'searchable' => false,
            'sortable' => false,
            'switchable' => false,
            'title' => trans('general.checkin').'/'.trans('general.checkout'),
            'visible' => true,
            'formatter' => 'componentsInOutFormatter',
            'printIgnore' => true,
        ];

        $layout[] = [
            'field' => 'actions',
            'scope' => 'col',
            'searchable' => false,
            'sortable' => false,
            'switchable' => false,
            'title' => trans('table.actions'),
            'formatter' => 'componentsActionsFormatter',
            'printIgnore' => true,
            'class' => 'hidden-print',
        ];

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
                'field' => 'name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.name'),
                'visible' => true,
                'formatter' => 'polymorphicItemFormatter',
            ],
            [
                'field' => 'assigned_qty',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.qty'),
                'visible' => true,
                'footerFormatter' => 'qtySumFormatter',
            ],
            [
                'field' => 'note',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'visible' => true,
                'title' => trans('general.notes'),
                'formatter' => 'notesFormatter',
            ], [
                'field' => 'created_at',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.created_at'),
                'formatter' => 'dateDisplayFormatter',
            ],
            [
                'field' => 'created_by',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
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
                'visible' => true,
                'formatter' => 'componentsInOutFormatter',
                'printIgnore' => true,
                'class' => 'hidden-print',

            ],
        ];

        return json_encode($layout);
    }

    /**
     * Generate html link to this items name.
     *
     * @return string
     */
    public function nameUrl()
    {
        if (auth()->user()->can('view', ['\App\Models\Component', $this])) {
            return '<a href="'.route('components.show', $this->id).'">'.e($this->display_name).'</a>';
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
        return route('components.show', $this->id);
    }

    /**
     * Column layout for the components tab on /account/requestable.
     * Same shape as AccessoryPresenter::dataTableLayoutRequestable;
     * paired with api.components.requestable + ComponentsTransformer.
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
                'formatter' => 'componentRequestableNameFormatter',
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
                'formatter' => 'componentRequestableActionsFormatter',
                'printIgnore' => true,
                'class' => 'hidden-print',
            ],
        ]);
    }
}
