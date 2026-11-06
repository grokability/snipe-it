<?php

namespace App\Presenters;

use App\Models\CustomField;

final class CustomFieldPresenter
{
    /**
     * Column layout for the fields bootstrap-table on the Custom Fields
     * index. The boolean-flag columns ride on css-* icon-only headers
     * (overrides.less) so the title stays readable in the column-chooser
     * while the header renders as a glyph. Matches the locations
     * presenter pattern.
     */
    public static function dataTableLayout(): string
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
            ], [
                'field' => 'help_text',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/custom_fields/general.help_text'),
                'visible' => true,
            ], [
                'field' => 'db_column_name',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/custom_fields/general.db_field'),
                'visible' => false,
                'formatter' => 'customFieldDbColumnFormatter',
            ], [
                'field' => 'format',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/custom_fields/general.field_format'),
                'visible' => true,
            ], [
                'field' => 'field_encrypted',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/custom_fields/general.encrypted'),
                'titleTooltip' => trans('admin/custom_fields/general.encrypted'),
                'visible' => true,
                'class' => 'css-field-encrypted text-center',
                'formatter' => 'trueFalseFormatter',
            ], [
                'field' => 'show_in_listview',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/custom_fields/general.show_in_listview_short'),
                'titleTooltip' => trans('admin/custom_fields/general.show_in_listview_short'),
                'visible' => true,
                'class' => 'css-list text-center',
                'formatter' => 'trueFalseFormatter',
            ], [
                'field' => 'display_in_user_view',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/custom_fields/general.display_in_user_view_table'),
                'titleTooltip' => trans('admin/custom_fields/general.display_in_user_view_table'),
                'visible' => false,
                'class' => 'css-eye text-center',
                'formatter' => 'trueFalseFormatter',
            ], [
                'field' => 'show_in_email',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/custom_fields/general.show_in_email_short'),
                'titleTooltip' => trans('admin/custom_fields/general.show_in_email_short'),
                'visible' => true,
                'class' => 'css-envelope text-center',
                'formatter' => 'trueFalseFormatter',
            ], [
                'field' => 'show_in_requestable_list',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/custom_fields/general.show_in_requestable_list_short'),
                'titleTooltip' => trans('admin/custom_fields/general.show_in_requestable_list_short'),
                'visible' => true,
                'class' => 'css-bell-concierge text-center',
                'formatter' => 'trueFalseFormatter',
            ], [
                'field' => 'is_unique',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/custom_fields/general.unique'),
                'titleTooltip' => trans('admin/custom_fields/general.unique'),
                'visible' => true,
                'class' => 'css-fingerprint text-center',
                'formatter' => 'trueFalseFormatter',
            ], [
                'field' => 'display_checkin',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/custom_fields/general.display_checkin'),
                'titleTooltip' => trans('admin/custom_fields/general.display_checkin'),
                'visible' => false,
                'class' => 'css-rotate-right text-center',
                'formatter' => 'trueFalseFormatter',
            ], [
                'field' => 'display_checkout',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/custom_fields/general.display_checkout'),
                'titleTooltip' => trans('admin/custom_fields/general.display_checkout'),
                'visible' => false,
                'class' => 'css-rotate-left text-center',
                'formatter' => 'trueFalseFormatter',
            ], [
                'field' => 'display_audit',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/custom_fields/general.display_audit'),
                'titleTooltip' => trans('admin/custom_fields/general.display_audit'),
                'visible' => false,
                'class' => 'css-clipboard-check text-center',
                'formatter' => 'trueFalseFormatter',
            ], [
                'field' => 'type',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('admin/custom_fields/general.field_element_short'),
                'visible' => true,
            ], [
                'field' => 'fieldsets',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => true,
                'title' => trans('admin/custom_fields/general.fieldsets'),
                'visible' => true,
                'formatter' => 'customFieldsFieldsetsFormatter',
            ], [
                'field' => 'created_by',
                'scope' => 'col',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
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
                'field' => 'actions',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'visible' => true,
                'formatter' => 'customFieldsActionsFormatter',
                'printIgnore' => true,
                'class' => 'hidden-print',
            ],
        ];

        return json_encode($layout);
    }

    /**
     * @return string[]
     */
    public static function visibilityIconsArray(CustomField $field): array
    {
        $icons = [];

        if ($field->display_checkout) {
            $label = e(trans('admin/custom_fields/general.display_checkout'));
            $icons[] = '<span title="'.$label.'" data-tooltip="true"><i class="fa-solid fa-rotate-left text-muted" aria-hidden="true"></i><span class="sr-only">'.$label.'</span></span>';
        }

        if ($field->display_checkin) {
            $label = e(trans('admin/custom_fields/general.display_checkin'));
            $icons[] = '<span title="'.$label.'" data-tooltip="true"><i class="fa-solid fa-rotate-right text-muted" aria-hidden="true"></i><span class="sr-only">'.$label.'</span></span>';
        }

        if ($field->display_audit) {
            $label = e(trans('admin/custom_fields/general.display_audit'));
            $icons[] = '<span title="'.$label.'" data-tooltip="true"><i class="fas fa-clipboard-check text-muted" aria-hidden="true"></i><span class="sr-only">'.$label.'</span></span>';
        }

        if ($field->display_in_user_view) {
            $label = e(trans('admin/custom_fields/general.display_in_user_view_table'));
            $icons[] = '<span title="'.$label.'" data-tooltip="true"><i class="fas fa-user text-muted" aria-hidden="true"></i><span class="sr-only">'.$label.'</span></span>';
        }

        if ($field->show_in_listview) {
            $label = e(trans('admin/custom_fields/general.show_in_listview_short'));
            $icons[] = '<span title="'.$label.'" data-tooltip="true"><i class="fas fa-list text-muted" aria-hidden="true"></i><span class="sr-only">'.$label.'</span></span>';
        }

        if ($field->show_in_email) {
            $label = e(trans('admin/custom_fields/general.show_in_email_short'));
            $icons[] = '<span title="'.$label.'" data-tooltip="true"><i class="fas fa-envelope text-muted" aria-hidden="true"></i><span class="sr-only">'.$label.'</span></span>';
        }

        if ($field->show_in_requestable_list) {
            $label = e(trans('admin/custom_fields/general.show_in_requestable_list_short'));
            $icons[] = '<span title="'.$label.'" data-tooltip="true"><i class="fa-solid fa-bell-concierge text-muted" aria-hidden="true"></i><span class="sr-only">'.$label.'</span></span>';
        }

        return $icons;
    }

    public static function visibilityIcons(CustomField $field): string
    {
        return implode(' ', self::visibilityIconsArray($field));
    }
}
