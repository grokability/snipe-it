<?php

namespace App\Presenters;

/**
 * Class DepreciationPresenter
 */
class DepreciationPresenter extends Presenter
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
                'switchable' => false,
                'title' => trans('general.name'),
                'visible' => true,
                'formatter' => 'depreciationsLinkFormatter',
            ],

            [
                'field' => 'months',
                'searchable' => true,
                'sortable' => true,
                'title' =>  trans('admin/depreciations/table.term'),
                'visible' => true,
            ],

            [
                "field" => 'depreciation_min',
                "searchable" => false,
                "sortable" => true,
                "title" => trans('admin/depreciations/table.depreciation_min'),
                "visible" => true,
            ],
            [
                'field' => 'assets_count',
                'searchable' => false,
                'sortable' => true,
                'title' =>  trans('general.assets'),
                'visible' => true,
            ],
            [
                'field' => 'models_count',
                'searchable' => false,
                'sortable' => true,
                'title' =>  trans('general.asset_models'),
                'visible' => true,
            ], [
                'field' => 'licenses_count',
                'searchable' => false,
                'sortable' => true,
                'title' =>  trans('general.licenses'),
                'visible' => true,
            ],[
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
            ], [
                'field' => 'actions',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'visible' => true,
                'formatter' => 'depreciationsActionsFormatter',
                'printIgnore' => true,
            ],
        ];

        return json_encode($layout);
    }
}
