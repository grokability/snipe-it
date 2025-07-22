<?php

namespace App\Presenters;

/**
 * Class StatusLabelPresenter
 */
class StatusLabelPresenter extends Presenter
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
                'formatter' => 'statuslabelsLinkFormatter',
            ],[
                'field' => 'type',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('admin/statuslabels/table.status_type'),
                'visible' => true,
                'formatter' => 'statusLabelTypeFormatter',
            ], [
                'field' => 'assets_count',
                'searchable' => false,
                'sortable' => true,
                'switchable' => false,
                'title' => trans('general.assets'),
                'visible' => true,
            ], [
                'field' => 'color',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/statuslabels/table.color'),
                'visible' => true,
                'formatter' => 'colorSqFormatter',
            ],  [
                'field' => 'show_in_nav',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/statuslabels/table.show_in_nav'),
                'visible' => true,
                'formatter' => 'trueFalseFormatter',
            ], [
                'field' => 'default_label',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/statuslabels/table.default_label'),
                'visible' => true,
                'formatter' => 'trueFalseFormatter',
            ],[
                'field' => 'notes',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.notes'),
                'visible' => false,
            ], [
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
                'formatter' => 'statuslabelsActionsFormatter',
                'printIgnore' => true,
            ],
        ];

        return json_encode($layout);
    }


}
