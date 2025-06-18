<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\CustomField;
use Illuminate\Database\Eloquent\Collection;

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
     * @param  Collection $fields
     * @param  int     $modelId
     * @param  int     $total
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
        $array = [
            'id' => $field->id,
            'name' => e($field->name),
            'db_column_name' => e($field->db_column_name()),
            'format'   =>  e($field->format),
            'field_values'   => ($field->field_values) ? e($field->field_values) : null,
            'field_values_array'   => ($field->field_values) ? explode("\r\n", e($field->field_values)) : null,
            'type'   =>  e($field->element),
            'required'   =>  (($field->pivot) && ($field->pivot->required=='1')) ? true : false,
            'display_in_user_view' =>  ($field->display_in_user_view =='1') ? true : false,
            'auto_add_to_fieldsets' =>  ($field->auto_add_to_fieldsets == '1') ? true : false,
            'show_in_listview'  => ($field->show_in_listview == '1') ? true : false,
            'display_checkin'  => ($field->display_checkin == '1') ? true : false,
            'display_checkout'  => ($field->display_checkout == '1') ? true : false,
            'display_audit'  => ($field->display_audit == '1') ? true : false,
            'created_at' => Helper::getFormattedDateObject($field->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($field->updated_at, 'datetime'),
        ];

        return $array;
    }

    /**
     * Returns the core data for a field, including the default value it has
     * when attributed to a certain model
     *
     * @param  CustomField $field
     * @param  int      $modelId
     * @return array
     */
    public function transformCustomFieldWithDefaultValue(CustomField $field, $modelId)
    {
        return [
            'id' => $field->id,
            'name' => e($field->name),
            'type'   =>  e($field->element),
            'field_values_array'   => ($field->field_values) ? explode("\r\n", e($field->field_values)) : null,
            'default_value' => $field->defaultValue($modelId),
        ];
    }
}
