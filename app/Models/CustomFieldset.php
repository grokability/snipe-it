<?php

namespace App\Models;

use App\Rules\AlphaEncrypted;
use App\Rules\NumericEncrypted;
use Gate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Watson\Validating\ValidatingTrait;

class CustomFieldset extends Model
{
    use HasFactory;
    use ValidatingTrait;

    protected $guarded = ['id'];

    /**
     * Validation rules
     * @var array
     */
    public $rules = [
        'name' => 'required|unique:custom_fieldsets',
    ];

    /**
     * Whether the model should inject it's identifier to the unique
     * validation rules before attempting validation. If this property
     * is not set in the model it will default to true.
     *
     * @var bool
     */
    protected $injectUniqueIdentifier = true;

    /**
     * Establishes the fieldset -> field relationship
     *
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     * @since [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function fields()
    {
        return $this->belongsToMany(\App\Models\CustomField::class)->withPivot(['required', 'order'])->orderBy('pivot_order');
    }

    /**
     * Establishes the fieldset -> models relationship
     *
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     * @since [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function models()
    {
        return $this->hasMany(\App\Models\AssetModel::class, 'fieldset_id');
    }

    /**
     * Establishes the fieldset -> admin user relationship
     *
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     * @since [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class); //WARNING - not all CustomFieldsets have a User!!
    }

    public function displayAnyFieldsInForm($form_type = null)
    {
        if ($this->fields) {

            switch ($form_type) {
                case 'audit':
                    return $this->fields->where('display_audit', '1')->count() > 0;
                case 'checkin':
                    return $this->fields->where('display_checkin', '1')->count() > 0;
                case 'checkout':
                    return $this->fields->where('display_checkout', '1')->count() > 0;
                default:
                    return true;
            }
        }

        return false;
    }

    /**
     * Determine the validation rules we should apply based on the
     * custom field format
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return array
     */
    public function validation_rules()
    {
        $rules = [];
        foreach ($this->fields as $field) {
            $rule = [];

            if (($field->field_encrypted != '1') ||
                  (($field->field_encrypted == '1') && (Gate::allows('admin')))) {
                    $rule[] = ($field->pivot->required == '1') ? 'required' : 'nullable';
            }

            if ($field->is_unique == '1') {
                    $rule[] = 'unique_undeleted';
            }

            if ($field->attributes['format']!='') {
                array_push($rule, $field->attributes['format']);
            }

            $rules[$field->db_column_name()] = $rule;


            // these are to replace the standard 'numeric' and 'alpha' rules if the custom field is also encrypted.
            // the values need to be decrypted first, because encrypted strings are alphanumeric
            if ($field->format === 'NUMERIC' && $field->field_encrypted) {
                $numericKey = array_search('numeric', $rules[$field->db_column_name()]);
                $rules[$field->db_column_name()][$numericKey] = new NumericEncrypted;
            }

            if ($field->format === 'ALPHA' && $field->field_encrypted) {
                $alphaKey = array_search('alpha', $rules[$field->db_column_name()]);
                $rules[$field->db_column_name()][$alphaKey] = new AlphaEncrypted;
            }

            // add not_array to rules for all fields but checkboxes
            if ($field->element != 'checkbox') {
                $rules[$field->db_column_name()][] = 'not_array';
            }

            if ($field->element == 'checkbox') {
                $rules[$field->db_column_name()][] = 'checkboxes';
            }

            if ($field->element == 'radio') {
                $rules[$field->db_column_name()][] = 'radio_buttons';
            }
        }

        return $rules;
    }
}
