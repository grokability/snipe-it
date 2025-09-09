<?php

namespace App\Models;

use App\Rules\AlphaEncrypted;
use App\Rules\BooleanEncrypted;
use App\Rules\DateEncrypted;
use App\Rules\EmailEncrypted;
use App\Rules\IPEncrypted;
use App\Rules\IPv4Encrypted;
use App\Rules\IPv6Encrypted;
use App\Rules\MacEncrypted;
use App\Rules\NumericEncrypted;
use App\Rules\RegexEncrypted;
use App\Rules\UrlEncrypted;
use Gate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Watson\Validating\ValidatingTrait;

class CustomFieldset extends Model
{
    use HasFactory;
    use ValidatingTrait;

    protected $guarded = ['id'];

    /**
     * Validation rules
     *
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
     * @since  [v3.0]
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
     * @since  [v3.0]
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
     * @since  [v3.0]
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
     * @since  [v3.0]
     * @return array
     */
    public function validation_rules(): array
    {
        $rules = [];
        foreach ($this->fields as $field) {
            $rule = [];

            if (($field->field_encrypted != '1') 
                || (($field->field_encrypted == '1') && (Gate::allows('admin')))
            ) {
                    $rule[] = ($field->pivot->required == '1') ? 'required' : 'nullable';
            }

            if ($field->is_unique == '1') {
                    $rule[] = 'unique_undeleted';
            }

            if ($field->attributes['format']!='') {
                array_push($rule, $field->attributes['format']);
            }

            $rules[$field->db_column_name()] = $rule;

            // this is to switch the rules to rules specially made for encrypted custom fields that decrypt the value before validating
            if ($field->field_encrypted) {
                $numericKey = array_search('numeric', $rules[$field->db_column_name()]);
                $alphaKey = array_search('alpha', $rules[$field->db_column_name()]);
                $emailKey = array_search('email', $rules[$field->db_column_name()]);
                $dateKey = array_search('date', $rules[$field->db_column_name()]);
                $urlKey = array_search('url', $rules[$field->db_column_name()]);
                $ipKey = array_search('ip', $rules[$field->db_column_name()]);
                $ipv4Key = array_search('ipv4', $rules[$field->db_column_name()]);
                $ipv6Key = array_search('ipv6', $rules[$field->db_column_name()]);
                $macKey = array_search('regex:/^[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}$/', $rules[$field->db_column_name()]);
                $booleanKey = array_search('boolean', $rules[$field->db_column_name()]);
                // find objects in array that start with "regex:"
                // collect because i couldn't figure how to do this
                // with array filter and get keys out of it
                $regexCollect = collect($rules[$field->db_column_name()]);
                $regexKeys = $regexCollect->filter(function ($value, $key) {
                    return starts_with($value, 'regex:');
                })->keys()->values()->toArray();

                switch ($field->format) {
                    case 'NUMERIC':
                        $rules[$field->db_column_name()][$numericKey] = new NumericEncrypted;
                        break;
                    case 'ALPHA':
                        $rules[$field->db_column_name()][$alphaKey] = new AlphaEncrypted;
                        break;
                    case 'EMAIL':
                        $rules[$field->db_column_name()][$emailKey] = new EmailEncrypted;
                        break;
                    case 'DATE':
                        $rules[$field->db_column_name()][$dateKey] = new DateEncrypted;
                        break;
                    case 'URL':
                        $rules[$field->db_column_name()][$urlKey] = new UrlEncrypted;
                        break;
                    case 'IP':
                        $rules[$field->db_column_name()][$ipKey] = new IPEncrypted;
                        break;
                    case 'IPV4':
                        $rules[$field->db_column_name()][$ipv4Key] = new IPv4Encrypted;
                        break;
                    case 'IPV6':
                        $rules[$field->db_column_name()][$ipv6Key] = new IPv6Encrypted;
                        break;
                    case 'MAC':
                        $rules[$field->db_column_name()][$macKey] = new MacEncrypted;
                        break;
                    case 'BOOLEAN':
                        $rules[$field->db_column_name()][$booleanKey] = new BooleanEncrypted;
                        break;
                    case starts_with($field->format, 'regex'):
                        foreach ($regexKeys as $regexKey) {
                            $rules[$field->db_column_name()][$regexKey] = new RegexEncrypted;
                        }
                        break;
                }
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
