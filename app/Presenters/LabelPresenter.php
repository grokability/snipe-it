<?php

namespace App\Presenters;

/**
 * Class LabelPresenter
 */
class LabelPresenter extends Presenter
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
                'field' => 'radio',
                'scope' => 'col',
                'radio' => true,
                'formatter' => 'labelRadioFormatter',
            ], [
                'field' => 'name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.name'),
                'visible' => true,
                'formatter' => 'labelNameFormatter',
            ], [
                'field' => 'size',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/settings/table.size'),
                'visible' => true,
                'formatter' => 'labelSizeFormatter',
            ], [
                'field' => 'labels_per_page',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/labels/table.labels_per_page'),
                'visible' => true,
                'formatter' => 'labelPerPageFormatter',
            ], [
                'field' => 'support_fields',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/labels/table.support_fields'),
                'visible' => true,
            ], [
                'field' => 'support_asset_tag',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/labels/table.support_asset_tag'),
                'visible' => true,
                'formatter' => 'trueFalseFormatter',
            ], [
                'field' => 'support_1d_barcode',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/labels/table.support_1d_barcode'),
                'visible' => true,
                'formatter' => 'trueFalseFormatter',
            ], [
                'field' => 'support_2d_barcode',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/labels/table.support_2d_barcode'),
                'visible' => true,
                'formatter' => 'trueFalseFormatter',
            ], [
                'field' => 'support_logo',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/labels/table.support_logo'),
                'visible' => true,
                'formatter' => 'trueFalseFormatter',
            ], [
                'field' => 'support_title',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/labels/table.support_title'),
                'visible' => true,
                'formatter' => 'trueFalseFormatter',
            ], [
                'field' => 'actions',
                'title' => trans('table.actions'),
                'searchable' => false,
                'sortable' => false,
                'formatter' => 'labelActionsFormatter',
            ],
        ];

        return json_encode($layout);
    }
}
