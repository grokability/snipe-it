<?php

namespace App\Models\Traits;

use App\Models\CustomFieldset;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Gate;
use Watson\Validating\ValidatingTrait;

trait ValidatesCustomFields
{
    use ValidatingTrait {
        getRules as watsonGetRules;
    }

    public static function bootValidatesCustomFields()
    {
        // if (method_exists(new (static::class)(), 'rules')) {
        //     throw new \LogicException("class " . static::class . " cannot have a rules() method and use this Trait");
        // }
        //
        // if (!static::class instanceof CustomFieldRelation) {
        //     throw new \LogicException("class " . static::class . " must implement CustomFieldRelation");
        // }
        if (!array_key_exists(self::class, CustomFieldset::FIELDSET_ASSIGNMENT_MODELS)) {
            throw new \LogicException(self::class . " must be in the CustomFieldset::FIELDSET_ASSIGNMENT_MODELS array");
        }
    }

    abstract public function custom_fieldset(): Relation;

    protected function customFieldRules()
    {
        if ($this->custom_fieldset) {

            foreach ($this->custom_fieldset->fields as $field) {

                // FIXME - this belongs in a CAST

                // this just casts booleans that may come through as strings to an actual boolean type
                // adding !$field->field_encrypted because when the encrypted value comes through it
                // screws things up for the encrypted validation rules (and the encrypted string
                // is not a valid boolean type)
                if ($field->format == 'BOOLEAN' && !$field->field_encrypted) {
                    $this->{$field->db_column} = filter_var($this->{$field->db_column}, FILTER_VALIDATE_BOOLEAN);
                }
            }
        }

        return $this->custom_fieldset?->validation_rules() ?? [];
    }

    public function getRules()
    {
        return $this->watsonGetRules() + $this->customFieldRules();
    }

    public function setCustomFieldsForPurpose($checkin_checkout, Request $request)
    {
        // Check to see if any of the custom fields were included on the form and if they have any values
        if (($this->model) && ($this->model->fieldset) && ($this->model->fieldset->fields)) {

            foreach ($this->model->fieldset->fields as $field) {

                if (($field->{$checkin_checkout} == 1) && (request()->has($field->db_column))) {

                    if ($field->field_encrypted == '1') {

                        if (Gate::allows('assets.view.encrypted_custom_fields')) {
                            if (is_array(request()->input($field->db_column))) {
                                $this->{$field->db_column} = Crypt::encrypt(implode(', ', request()->input($field->db_column)));
                            } else {
                                $this->{$field->db_column} = Crypt::encrypt(request()->input($field->db_column));
                            }
                        }

                    } else {

                        if (is_array(request()->input($field->db_column))) {
                            $this->{$field->db_column} = implode(', ', request()->input($field->db_column));
                        } else {
                            $this->{$field->db_column} = request()->input($field->db_column);
                        }

                    }
                }
            }
        }

    }

}