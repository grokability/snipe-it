<?php

namespace App\Presenters;

class HistoryPresenter extends Presenter
{
    /**
     * Json Column Layout for bootstrap table
     *
     * @return string
     */
    public static function dataTableLayout($hide_fields = [])
    {
        $layout = [];

        if (! in_array('id', $hide_fields)) {
            array_push($layout,
                [
                    'id' => 'id',
                    'searchable' => false,
                    'sortable' => true,
                    'switchable' => true,
                    'title' => trans('general.id'),
                    'visible' => false,
                    'class' => 'hidden-xs',
                ]);
        }

        if (! in_array('icon', $hide_fields)) {
            array_push($layout,
                [
                    'field' => 'icon',
                    'searchable' => false,
                    'sortable' => true,
                    'switchable' => true,
                    'title' => trans('admin/hardware/table.icon'),
                    'visible' => true,
                    'class' => 'hidden-xs',
                    'formatter' => 'iconFormatter',
                ]);
        }

        if (! in_array('created_at', $hide_fields)) {
            array_push($layout,
                [
                    'field' => 'created_at',
                    'searchable' => true,
                    'sortable' => true,
                    'switchable' => true,
                    'title' => trans('general.created_at'),
                    'visible' => true,
                    'formatter' => 'dateDisplayFormatter',
                ]);
        }
        if (! in_array('created_by', $hide_fields)) {
            array_push($layout,
                [
                    'field' => 'created_by',
                    'searchable' => true,
                    'sortable' => true,
                    'title' => trans('general.created_by'),
                    'visible' => true,
                    'formatter' => 'usersLinkObjFormatter',
                ]);
        }

        if (! in_array('action_type', $hide_fields)) {
            array_push($layout,
                [
                    'field' => 'action_type',
                    'searchable' => true,
                    'sortable' => true,
                    'switchable' => true,
                    'title' => trans('general.action'),
                    'visible' => true,
                ]);
        }

        if (! in_array('action_date', $hide_fields)) {
            array_push($layout,
                [
                    'field' => 'action_date',
                    'searchable' => false,
                    'sortable' => true,
                    'title' => trans('general.action_date'),
                    'visible' => false,
                    'formatter' => 'dateDisplayFormatter',
                ]);
        }

        if (! in_array('item', $hide_fields)) {
            array_push($layout,
                [
                    'field' => 'item',
                    'searchable' => true,
                    'sortable' => true,
                    'switchable' => true,
                    'title' => trans('general.item'),
                    'visible' => true,
                    'formatter' => 'polymorphicItemFormatter',
                ]);
        }

        if (! in_array('serial', $hide_fields)) {
            array_push($layout,
                [
                    'field' => 'item.serial',
                    'title' => trans('admin/hardware/table.serial'),
                    'visible' => false,
                ]);
        }

        if (! in_array('target', $hide_fields)) {
            array_push($layout,
                [
                    'field' => 'target',
                    'searchable' => true,
                    'sortable' => true,
                    'switchable' => true,
                    'title' => trans('general.target'),
                    'visible' => true,
                    'formatter' => 'polymorphicItemFormatter',
                ]);
        }

        if (! in_array('file', $hide_fields)) {
            array_push($layout,
                [
                    'field' => 'file',
                    'searchable' => true,
                    'sortable' => true,
                    'switchable' => true,
                    'title' => trans('general.file_name'),
                    'visible' => true,
                    'formatter' => 'fileNameFormatter',
                ]);
        }

        if (! in_array('file_download', $hide_fields)) {
            array_push($layout,
                [
                    'field' => 'file_download',
                    'searchable' => false,
                    'sortable' => true,
                    'switchable' => true,
                    'title' => trans('general.download'),
                    'visible' => true,
                    'formatter' => 'fileDownloadButtonsFormatter',
                ]);
        }

        if (! in_array('quantity', $hide_fields)) {
            array_push($layout,
                [
                    'field' => 'quantity',
                    'searchable' => false,
                    'sortable' => true,
                    'visible' => true,
                    'title' => trans('general.quantity'),
                ]);
        }

        if (! in_array('note', $hide_fields)) {
            array_push($layout,
                [
                    'field' => 'note',
                    'searchable' => true,
                    'sortable' => true,
                    'visible' => true,
                    'title' => trans('general.notes'),
                    'formatter' => 'notesFormatter',
                ]);
        }

        if (! in_array('signature_file', $hide_fields)) {
            array_push($layout,
                [
                    'field' => 'signature_file',
                    'searchable' => true,
                    'sortable' => true,
                    'switchable' => true,
                    'title' => trans('general.signature'),
                    'visible' => false,
                    'formatter' => 'imageFormatter',
                ]);
        }

        if (! in_array('log_meta', $hide_fields)) {
            array_push($layout,
                [
                    'field' => 'log_meta',
                    'searchable' => false,
                    'sortable' => false,
                    'visible' => true,
                    'title' => trans('admin/hardware/table.changed'),
                    'formatter' => 'changeLogFormatter',
                ]);
        }

        if (! in_array('remote_ip', $hide_fields)) {
            array_push($layout,
                [
                    'field' => 'remote_ip',
                    'searchable' => true,
                    'sortable' => true,
                    'visible' => false,
                    'title' => trans('admin/settings/general.login_ip'),
                ]);
        }

        if (! in_array('user_agent', $hide_fields)) {
            array_push($layout,
                [
                    'field' => 'user_agent',
                    'searchable' => true,
                    'sortable' => true,
                    'visible' => false,
                    'title' => trans('admin/settings/general.login_user_agent'),
                ]);
        }

        if (! in_array('action_source', $hide_fields)) {
            array_push($layout,
                [
                    'field' => 'action_source',
                    'searchable' => true,
                    'sortable' => true,
                    'visible' => false,
                    'title' => trans('general.action_source'),
                ]);
        }

        return json_encode($layout);
    }
}
