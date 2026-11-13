<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\CustomField;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;

class CustomFieldsTransformer
{
    public function transformCustomFields(Collection $fields, $total)
    {
        $array = [];
        foreach ($fields as $field) {
            $array[] = self::transformCustomField($field);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    /**
     * Builds up an array of formatted custom fields
     *
     * @param  int  $modelId
     * @param  int  $total
     * @return array
     */
    public function transformCustomFieldsWithDefaultValues(Collection $fields, $modelId, $total)
    {
        $array = [];

        foreach ($fields as $field) {
            $array[] = self::transformCustomFieldWithDefaultValue($field, $modelId);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformCustomField(CustomField $field)
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
            'link_template' => ($field->link_template) ? e($field->link_template) : null,
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

        return $array;
    }

    /**
     * Returns the core data for a field, including the default value it has
     * when attributed to a certain model
     *
     * @param  int  $modelId
     * @return array
     */
    public function transformCustomFieldWithDefaultValue(CustomField $field, $modelId)
    {
        return [
            'id' => $field->id,
            'name' => e($field->name),
            'element' => e($field->element),
            // Back-compat alias for 'element'. See transformCustomField() above.
            'type' => e($field->element),
            'field_values_array' => ($field->field_values) ? preg_split('/\r\n|\r|\n/', e($field->field_values)) : null,
            'default_value' => $field->defaultValue($modelId),
        ];
    }
}
