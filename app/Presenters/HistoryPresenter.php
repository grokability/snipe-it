<?php

namespace App\Presenters;

/**
 * Class AccessoryPresenter
 */
class HistoryPresenter extends Presenter
{
    /**
     * Json Column Layout for bootstrap table
     * @return string
     */
    public static function dataTableLayout($serial = false)
    {
        $extra = [];
        $layout_start = [
            [
                'id' => 'id',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.id'),
                'visible' => false,
                'class' => 'hidden-xs',
            ],
            [
                'field' => 'icon',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/hardware/table.icon'),
                'visible' => true,
                'class' => 'hidden-xs',
                'formatter' => 'iconFormatter',
            ],
            [
                'field' => 'created_at',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.created_at'),
                'visible' => true,
                'formatter' => 'dateDisplayFormatter',
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
                'field' => 'action_date',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.action_date'),
                'visible' => false,
                'formatter' => 'dateDisplayFormatter',
            ],
            [
                'field' => 'action_type',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.action'),
                'visible' => true,
            ],
            [
                'field' => 'item',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.item'),
                'visible' => true,
                'formatter' => 'polymorphicItemFormatter',
            ],
        ];


        if ($serial) {
            $extra =  [
                [
                'field' => 'item.serial',
                'title' => trans('admin/hardware/table.serial'),
                'visible' => false,
            ]
            ];
        }

        $layout_end = [
            [
                'field' => 'target',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.target'),
                'visible' => true,
                'formatter' => 'polymorphicItemFormatter',
            ],
            [
                'field' => 'file',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.file_name'),
                'visible' => true,
                'formatter' => 'fileNameFormatter',
            ],
            [
                'field' => 'file_download',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.download'),
                'visible' => true,
                'formatter' => 'fileDownloadButtonsFormatter',
            ],
            [
                'field' => 'note',
                'searchable' => true,
                'sortable' => true,
                'visible' => true,
                'title' => trans('general.notes'),
                'formatter' => 'notesFormatter'
            ],
            [
                'field' => 'signature_file',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.signature'),
                'visible' => false,
                'formatter' => 'imageFormatter',
            ],
            [
                'field' => 'log_meta',
                'searchable' => false,
                'sortable' => false,
                'visible' => true,
                'title' => trans('admin/hardware/table.changed'),
                'formatter' => 'changeLogFormatter',
            ],
            [
                'field' => 'remote_ip',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('admin/settings/general.login_ip'),
            ],
            [
                'field' => 'user_agent',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('admin/settings/general.login_user_agent'),
            ],
            [
                'field' => 'action_source',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.action_source'),
            ],
        ];

        $merged = array_merge($layout_start, $extra, $layout_end);
        return json_encode($merged);
    }

}