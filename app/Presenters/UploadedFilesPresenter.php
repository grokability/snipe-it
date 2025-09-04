<?php

namespace App\Presenters;

/**
 * Class AccessoryPresenter
 */
class UploadedFilesPresenter extends Presenter
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
                'searchable' => true,
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
                'visible' => true,
                'formatter' => 'iconFormatter',
            ],
            [
                'field' => 'image',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.image'),
                'visible' => true,
                'formatter' => 'filePreviewFormatter',
            ],
            [
                'field' => 'filename',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.file_name'),
                'visible' => true,
                'formatter' => 'fileNameFormatter',
            ],
            [
                'field' => 'download',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('general.download'),
                'visible' => true,
                'formatter' => 'fileDownloadButtonsFormatter',
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
                'searchable' => true,
                'sortable' => true,
                'title' => trans('general.created_by'),
                'visible' => true,
                'formatter' => 'usersLinkObjFormatter',
            ],
            [
                'field' => 'created_at',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.created_at'),
                'visible' => true,
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'available_actions',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'visible' => true,
                'formatter' => 'deleteUploadFormatter',
            ],
        ];

        return json_encode($layout);
    }

}