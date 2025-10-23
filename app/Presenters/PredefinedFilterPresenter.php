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
                'formatter' => 'predefinedFiltersLinkFormatter',
            ], [
                'field' => 'is_public',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.is_public'),
                'visible' => true,
                'formatter' => 'trueFalseFormatter',
            ], [
                'field' => 'object_type',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('object_type'),
                'visible' => false,
                'formatter' => 'predefined-filtersFormatter',
            ], [
                'field' => 'filter_data',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('filter_data'),
                'visible' => false,
                'formatter' => 'predefined-filtersFormatter',
            ], [
                'field' => 'created_by',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.created_by'),
                'visible' => true,
                'formatter' => 'usersLinkObjFormatter',
            ], [
                'field' => 'groups',
                'searchable' => true,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.groups'),
                'visible' => true,
                'formatter' => 'groupsFormatter',
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
                'formatter' => 'predefined-filtersActionsFormatter',
                'printIgnore' => true,
            ]
        ];

        return json_encode($layout);
    }
}