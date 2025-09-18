<?php

namespace App\Presenters;

/**
 * Class PredefinedFilterPresenter
 */

class PredefinedFilterPresenter extends Presenter
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
                'formatter' => 'predefinedFiltersFormatter',
            ], [
                'field' => 'is_public',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('is_public'),
                'visible' => true,
                'formatter' => 'predefinedFiltersFormatter',
            ], [
                'field' => 'object_type',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('object_type'),
                'visible' => false,
                'formatter' => 'predefinedFiltersFormatter',
            ], [
                'field' => 'filter_data',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('filter_data'),
                'visible' => false,
                'formatter' => 'predefinedFiltersFormatter',
            ], [
                'field' => 'created_by',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.created_by'),
                'visible' => true,
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
                'formatter' => 'predefinedFiltersActionsFormatter',
                'printIgnore' => true,
            ]
        ];

        return json_encode($layout);
    }
}