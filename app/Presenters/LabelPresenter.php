<?php

namespace App\Presenters;

/**
 * Class LabelPresenter
 */
class LabelPresenter extends Presenter
{
    /**
     * Json Column Layout for bootstrap table
     * @return string
     */
    public static function dataTableLayout()
    {
        $layout = [
            [
                'field' => 'radio',
                'radio' => true,
                'formatter' => 'labelRadioFormatter'
            ], [
                'field' => 'name',
                'searchable' => true,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.name'),
                'visible' => true,
            ], [
                'field' => 'size',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/settings/table.size'),
                'visible' => true,
                'formatter' => 'labelSizeFormatter'
            ], [
                'field' => 'labels_per_page',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/labels/table.labels_per_page'),
                'visible' => true,
                'formatter' => 'labelPerPageFormatter'
            ], [
                'field' => 'support_fields',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/labels/table.support_fields'),
                'visible' => true
            ], [
                'field' => 'support_asset_tag',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/labels/table.support_asset_tag'),
                'visible' => true,
                'formatter' => 'trueFalseFormatter'
            ], [
                'field' => 'support_1d_barcode',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/labels/table.support_1d_barcode'),
                'visible' => true,
                'formatter' => 'trueFalseFormatter'
            ], [
                'field' => 'support_2d_barcode',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/labels/table.support_2d_barcode'),
                'visible' => true,
                'formatter' => 'trueFalseFormatter'
            ], [
                'field' => 'support_logo',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/labels/table.support_logo'),
                'visible' => true,
                'formatter' => 'trueFalseFormatter'
            ], [
                'field' => 'support_title',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/labels/table.support_title'),
                'visible' => true,
                'formatter' => 'trueFalseFormatter'
            ]
        ];

        return json_encode($layout);
    }
}
