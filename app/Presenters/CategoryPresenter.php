<?php

namespace App\Presenters;

/**
 * Class CategoryPresenter
 */
class CategoryPresenter extends Presenter
{
    /**
     * Json Column Layout for bootstrap table
     * @return string
     */
    public static function dataTableLayout()
    {
        $layout = [
            [
                'field'        => 'checkbox',
                'checkbox'     => true,
                'titleTooltip' => trans('general.select_all_none'),
            ],
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
                'formatter' => 'categoriesLinkFormatter',
            ], [
                'field' => 'image',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.image'),
                'visible' => true,
                'formatter' => 'imageFormatter',
            ], [
                'field' => 'category_type',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.type'),
                'visible' => true,
            ], [
                'field' => 'item_count',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.qty'),
                'visible' => true,
            ], [
                'field' => 'has_eula',
                'searchable' => false,
                'sortable' => false,
                'title' => trans('admin/categories/table.eula_text'),
                'visible' => false,
                'formatter' => 'trueFalseFormatter',
            ], [
                'field' => 'checkin_email',
                'searchable' => false,
                'sortable' => true,
                'class' => 'css-envelope',
                'title' => 'Send Email',
                'visible' => true,
                'formatter' => 'trueFalseFormatter',
            ], [
                'field' => 'require_acceptance',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('admin/categories/table.require_acceptance'),
                'visible' => true,
                'formatter' => 'trueFalseFormatter',
            ], [
                "field" => "use_default_eula",
                "searchable" => false,
                "sortable" => true,
                "title" => trans('admin/categories/general.use_default_eula_column'),
                'visible' => true,
                "formatter" => 'trueFalseFormatter',
            ], [
                'field' => 'notes',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.notes'),
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
                'switchable' => true,
                'title' => trans('general.created_at'),
                'visible' => false,
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'updated_at',
                'searchable' => false,
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
		        'formatter' => 'categoriesActionsFormatter',
                'printIgnore' => true,
            ],
        ];

        return json_encode($layout);
    }

    /**
     * Link to this categories name
     * @return string
     */
    public function nameUrl()
    {
        return (string) link_to_route('categories.show', $this->name, $this->id);
    }

    /**
     * Url to view this item.
     * @return string
     */
    public function viewUrl()
    {
        return route('categories.show', $this->id);
    }
}
