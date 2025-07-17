<?php

namespace App\Presenters;

/**
 * Class AccessoryPresenter
 */
class UploadsPresenter extends Presenter
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
                'field' => 'id',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.id'),
                'visible' => false,
            ],
            [
                'field' => 'icon',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('general.type'),
                'formatter' => 'iconFormatter',
            ],
            [
                'field' => 'image',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.image'),
                'formatter' => 'inlineImageFormatter',
            ],
            [
                'field' => 'filename',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.file_name'),
                'visible' => true,
                'formatter' => 'fileUploadNameFormatter',
            ],
            [
                'field' => 'download',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.download'),
                'visible' => true,
                'formatter' => 'downloadOrOpenInNewWindowFormatter',
            ],
            [
                'field' => 'note',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.notes'),
                'visible' => true,
            ],
            [
                'field' => 'created_by',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.created_by'),
                'visible' => false,
                'formatter' => 'usersLinkObjFormatter',
            ],
            [
                'field' => 'created_at',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.created_at'),
                'visible' => false,
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'available_actions',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'formatter' => 'deleteUploadFormatter',
            ],
        ];

        return json_encode($layout);
    }

}