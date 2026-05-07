<?php

namespace App\Presenters;

/**
 * Class GroupPresenter
 */
class GroupPresenter extends Presenter
{
    /**
     * Json Column Layout for bootstrap table
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
            ],
            [
                'field' => 'name',
                'searchable' => true,
                'sortable' => true,
                'switchable' => false,
                'title' => trans('general.name'),
                'visible' => true,
                'formatter' => 'groupsAdminLinkFormatter',
            ],
            [
                'field' => 'users_count',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/groups/table.users'),
                'visible' => true,
                'class' => 'css-users',
            ],  [
                'field' => 'notes',
                'searchable' => true,
                'sortable' => true,
                'visible' => true,
                'title' => trans('general.notes'),
            ], [
                'field' => 'created_by',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.created_by'),
                'visible' => true,
                'formatter' => 'usersLinkObjFormatter',
            ],  [
                'field' => 'updated_at',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.updated_at'),
                'visible' => true,
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'actions',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'visible' => true,
                'formatter' => 'groupsActionsFormatter',
                'printIgnore' => true,
                'class' => 'hidden-print',
            ],
        ];

        return json_encode($layout);
    }
}
