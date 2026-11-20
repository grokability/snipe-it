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
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.id'),
                'visible' => false,
            ],
            [
                'field' => 'name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => false,
                'title' => trans('general.name'),
                'visible' => true,
                'formatter' => 'groupsAdminLinkFormatter',
            ],
            [
                'field' => 'users_count',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/groups/table.users'),
                'visible' => true,
                'class' => 'css-users',
            ],  [
                'field' => 'notes',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'visible' => true,
                'title' => trans('general.notes'),
            ], [
                'field' => 'created_by',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.created_by'),
                'visible' => true,
                'formatter' => 'usersLinkObjFormatter',
            ],  [
                'field' => 'updated_at',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.updated_at'),
                'visible' => true,
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'actions',
                'scope' => 'col',
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

    public function formattedNameLink()
    {

        if (auth()->user()->can('view', ['\App\Models\Groups', $this])) {
            return '<a href="'.route('groups.show', e($this->id)).'" class="label label-light" style="padding-left: 5px;"> '.e($this->display_name).'</a>';
        }

        return '<span class="label label-light" style="padding-left: 5px;"> '.e($this->display_name).'</span>';
    }
}
