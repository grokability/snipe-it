<?php

namespace App\Presenters;

/**
 * Class DepreciationPresenter
 */
class DepreciationPresenter extends Presenter
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
                'formatter' => 'depreciationsLinkFormatter',
            ],

            [
                'field' => 'months',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/depreciations/table.term'),
                'visible' => true,
            ],

            [
                'field' => 'depreciation_min',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('admin/depreciations/table.depreciation_min'),
                'visible' => true,
            ],
            [
                'field' => 'assets_count',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.assets'),
                'visible' => true,
            ],
            [
                'field' => 'models_count',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.asset_models'),
                'visible' => true,
            ], [
                'field' => 'licenses_count',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.licenses'),
                'visible' => true,
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
                'field' => 'actions',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'visible' => true,
                'formatter' => 'depreciationsActionsFormatter',
                'printIgnore' => true,
                'class' => 'hidden-print',
            ],
        ];

        return json_encode($layout);
    }

    public function formattedNameLink()
    {
        return \App\Helpers\Helper::tagColorIcon($this->tag_color).e($this->display_name);
    }

    public function nameUrl()
    {
        if (auth()->user()->can('view', ['\App\Models\Depreciation', $this])) {
            return '<a href="'.route('depreciations.show', $this->id).'">'.e($this->display_name).'</a>';
        } else {
            return e($this->display_name);
        }
    }
}
