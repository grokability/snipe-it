<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\CustomField;
use App\Models\CustomFieldDefault;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class CustomFieldsTransformer
{
    public function transformCustomFields(Collection $fields, int $total, ?Model $owner = null): array
    {
        $fields->loadMissing(['fieldset', 'adminuser']);
        $array = [];
        foreach ($fields as $field) {
            $array[] = self::transformCustomField($field, $owner);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformCustomField(CustomField $field, ?Model $owner = null): array
    {
        $fieldsetRows = [];
        foreach ($field->fieldset as $fs) {
            $fieldsetRows[] = [
                'id' => (int) $fs->id,
                'name' => e($fs->name),
            ];
        }

        $expectedSlug = $field->convertUnicodeDbSlug();
        $array = [
            'id' => $field->id,
            'name' => e($field->name),
            'help_text' => e($field->help_text),
            'db_column_name' => e($expectedSlug),
            'db_column_warning' => $expectedSlug !== $field->db_column
                ? trans('admin/custom_fields/general.db_convert_warning', [
                    'db_column' => e($field->db_column),
                    'expected' => e($expectedSlug),
                ])
                : null,
            'format' => e($field->format),
            'field_values' => ($field->field_values) ? e($field->field_values) : null,
            'field_encrypted' => ($field->field_encrypted == '1') ? true : false,
            'field_values_array' => ($field->field_values) ? preg_split('/\r\n|\r|\n/', e($field->field_values)) : null,
            'element' => e($field->element),
            // Back-compat alias for the 'element' key above. Older
            // integrations consume the field under this name. Keep both
            // in sync. Do not remove without a deprecation cycle.
            'type' => e($field->element),
            'required' => (($field->pivot) && ($field->pivot->required == '1')) ? true : false,
            'display_in_user_view' => ($field->display_in_user_view == '1') ? true : false,
            'auto_add_to_fieldsets' => ($field->auto_add_to_fieldsets == '1') ? true : false,
            'show_in_listview' => ($field->show_in_listview == '1') ? true : false,
            'show_in_email' => ($field->show_in_email == '1') ? true : false,
            'show_in_requestable_list' => ($field->show_in_requestable_list == '1') ? true : false,
            'is_unique' => ($field->is_unique == '1') ? true : false,
            'display_checkin' => ($field->display_checkin == '1') ? true : false,
            'display_checkout' => ($field->display_checkout == '1') ? true : false,
            'display_audit' => ($field->display_audit == '1') ? true : false,
            'fieldsets' => [
                'total' => count($fieldsetRows),
                'rows' => $fieldsetRows,
            ],
            'created_by' => ($field->adminuser) ? [
                'id' => (int) $field->adminuser->id,
                'name' => e($field->adminuser->display_name),
            ] : null,
            'created_at' => Helper::getFormattedDateObject($field->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($field->updated_at, 'datetime'),
        ];

        $deletable = $field->isDeletable();
        $array['available_actions'] = [
            'update' => Gate::allows('update', $field),
            'delete' => $deletable,
            // Consumed by checkboxEnabledFormatter on the fields index.
            // Keyed per bulk-dropdown action so adding e.g. bulk-archive later
            // just needs another key.
            'bulk_selectable' => [
                'delete' => $deletable,
            ],
        ];
        if ($owner !== null) {
            $defaults = CustomFieldDefault::valuesFor($owner);
            $array['default_value'] = $defaults[$field->db_column] ?? null;
        }

        return $array;
    }
}
