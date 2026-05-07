<?php

namespace App\Presenters;

/**
 * Class ComponentPresenter
 */
class ConsumablePresenter extends Presenter
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
                'switchable' => false,
                'title' => trans('general.name'),
                'visible' => true,
                'formatter' => 'consumablesLinkFormatter',
            ], [
                'field' => 'company',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.company'),
                'visible' => false,
                'formatter' => 'companiesLinkObjFormatter',
            ],

            [
                'field' => 'image',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.image'),
                'visible' => false,
                'formatter' => 'imageFormatter',
            ], [
                'field' => 'category',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.category'),
                'formatter' => 'categoriesLinkObjFormatter',
            ], [
                'field' => 'supplier',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.supplier'),
                'visible' => false,
                'formatter' => 'suppliersLinkObjFormatter',
            ], [
                'field' => 'model_number',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.model_no'),
            ], [
                'field' => 'location',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.location'),
                'formatter' => 'locationsLinkObjFormatter',
            ], [
                'field' => 'item_no',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/consumables/general.item_no'),
            ], [

                'field' => 'manufacturer',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.manufacturer'),
                'visible' => false,
                'formatter' => 'manufacturersLinkObjFormatter',
            ], [
                'field' => 'order_number',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.order_number'),
                'visible' => true,
            ], [
                'field' => 'purchase_date',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.purchase_date'),
                'visible' => true,
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'min_amt',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.min_amt'),
                'visible' => true,
                'formatter' => 'minAmtFormatter',
                'class' => 'text-right text-padding-number-cell',
            ], [
                'field' => 'qty',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('admin/components/general.total'),
                'visible' => true,
                'class' => 'text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ], [
                'field' => 'remaining',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('admin/components/general.remaining'),
                'visible' => true,
                'class' => 'text-right text-padding-number-cell',
                'footerFormatter' => 'qtySumFormatter',
            ], [
                'field' => 'percent_remaining',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => '% ' . trans('general.remaining'),
                'visible' => true,
                'formatter' => 'progressBarFormatter',
            ], [
                'field' => 'purchase_cost',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.unit_cost'),
                'visible' => true,
                'class' => 'text-right text-padding-number-cell',
                'footerFormatter' => 'sumFormatter',
            ], [
                'field' => 'total_cost',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.total_cost'),
                'footerFormatter' => 'sumFormatterQuantity',
                'class' => 'text-right text-padding-number-cell',
            ], [
                'field' => 'notes',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.notes'),
                'formatter' => 'notesFormatter',
            ], [
                'field' => 'created_by',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.created_by'),
                'visible' => false,
                'formatter' => 'usersLinkObjFormatter',
            ], [
                'field' => 'created_at',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.created_at'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'updated_at',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.updated_at'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'change',
                'searchable' => false,
                'sortable' => false,
                'visible' => true,
                'title' => trans('general.change'),
                'formatter' => 'consumablesInOutFormatter',
                'printIgnore' => true,
            ], [
                'field' => 'actions',
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

        return json_encode($layout);
    }

    public static function checkedOut()
    {
        $layout = [

            [
                'field' => 'avatar',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('general.image'),
                'visible' => true,
                'formatter' => 'imageFormatter',
            ],
            [
                'field' => 'user',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('general.name'),
                'visible' => true,
                'formatter' => 'usersLinkObjFormatter',
            ],
            [
                'field' => 'created_at',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('general.date'),
                'visible' => true,
                'formatter' => 'dateDisplayFormatter',
            ],

            [
                'field' => 'note',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('general.notes'),
                'visible' => true,
            ],

            [
                'field' => 'created_by',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('general.created_by'),
                'visible' => true,
                'formatter' => 'usersLinkObjFormatter',
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
}
