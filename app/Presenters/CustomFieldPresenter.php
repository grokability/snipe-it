<?php

namespace App\Presenters;

use App\Models\CustomField;

final class CustomFieldPresenter
{
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
