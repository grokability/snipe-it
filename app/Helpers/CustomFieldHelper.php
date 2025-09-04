<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Gate;

/*********************
 * These two helper methods are more designed for being re-used with the new HasCustomFields Trait
 *
 * The 'transform' method is designed for BlahTransformer things that need to return custom field values.
 *
 * The 'present' method is designed for when you're trying to generate fieldlists for use in Bootstrap tables
 *  - typically the 'dataTableLayout' method
 *
 *********************/
class CustomFieldHelper {

    static function transform($fieldset, $item) {
        if ($fieldset && ($fieldset->fields->count() > 0)) {
            $fields_array = [];

            foreach ($fieldset->fields as $field) {
                if ($field->isFieldDecryptable($item->{$field->db_column})) {
                    $decrypted = Helper::gracefulDecrypt($field, $item->{$field->db_column});
                    $value = (Gate::allows('assets.view.encrypted_custom_fields')) ? $decrypted : strtoupper(trans('admin/custom_fields/general.encrypted'));

                    if ($field->format == 'DATE'){
                        if (Gate::allows('assets.view.encrypted_custom_fields')){
                            $value = Helper::getFormattedDateObject($value, 'date', false);
                        } else {
                            $value = strtoupper(trans('admin/custom_fields/general.encrypted'));
                        }
                    }

                    $fields_array[$field->name] = [
                        'field' => e($field->db_column),
                        'value' => e($value),
                        'field_format' => $field->format,
                        'element' => $field->element,
                    ];

                } else {
                    $value = $item->{$field->db_column};

                    if (($field->format == 'DATE') && (!is_null($value)) && ($value!='')){
                        $value = Helper::getFormattedDateObject($value, 'date', false);
                    }

                    $fields_array[$field->name] = [
                        'field' => e($field->db_column),
                        'value' => e($value),
                        'field_format' => $field->format,
                        'element' => $field->element,
                    ];
                }

                return $fields_array;
            }
        } else {
            return new \stdClass; // HACK to force generation of empty object instead of empty list
        }
    }

    static function present($field) {
        return [
            'field' => 'custom_fields.'.$field->db_column,
            'searchable' => true,
            'sortable' => true,
            'switchable' => true,
            'title' => $field->name,
            'formatter'=> 'customFieldsFormatter',
            'escape' => true,
            'class' => ($field->field_encrypted == '1') ? 'css-padlock' : '',
            'visible' => ($field->show_in_listview == '1') ? true : false,
        ];
    }
}