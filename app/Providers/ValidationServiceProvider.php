<?php

namespace App\Providers;

use App\Models\CustomField;
use App\Models\Department;
use App\Models\Location;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

/**
 * This service provider handles a few custom validation rules.
 *
 * PHP version 5.5.9
 * @version    v3.0
 */
class ValidationServiceProvider extends ServiceProvider
{
    /**
     * Custom email array validation
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return void
     */
    public function boot()
    {

        // Email array validator
        Validator::extend('email_array', function ($attribute, $value, $parameters, $validator) {
            $value = str_replace(' ', '', $value);
            $array = explode(',', $value);
            $email_to_validate = [];

            foreach ($array as $email) { //loop over values
                $email_to_validate['alert_email'][] = $email;
            }

            $rules = ['alert_email.*'=>'email'];
            $messages = [
                'alert_email.*' => trans('validation.custom.email_array'),
            ];

            $validator = Validator::make($email_to_validate, $rules, $messages);

            return $validator->passes();
        });


        /**
         * Unique only if undeleted.
         *
         * This works around the use case where multiple deleted items have the same unique attribute.
         * (I think this is a bug in Laravel's validator?)
         *
         * $attribute is the FIELDNAME you're checking against
         * $value is the VALUE of the item you're checking against the existing values in the fieldname
         * $parameters[0] is the TABLE NAME you're querying
         * $parameters[1] is the ID of the item you're querying - this makes it work on saving, checkout, etc,
         *   since it defaults to 0 if there is no item created yet (new item), but populates the ID if editing
         *
         * The UniqueUndeletedTrait prefills these parameters, so you can just use
         * `unique_undeleted:table,fieldname` in your rules out of the box
         */
        Validator::extend('unique_undeleted', function ($attribute, $value, $parameters, $validator) {
            if (count($parameters)) {

                // This is a bit of a shim, but serial doesn't have any other rules around it other than that it's nullable
                if (($parameters[0]=='assets') && ($attribute == 'serial') && (Setting::getSettings()->unique_serial != '1')) {
                    return true;
                }

                $count = DB::table($parameters[0])
                    ->select('id')
                    ->where($attribute, '=', $value)
                    ->whereNull('deleted_at')
                    ->where('id', '!=', $parameters[1])->count();

                return $count < 1;
            }
        });
        
        /**
         * Unique if undeleted for two columns
         *
         * Same as unique_undeleted but taking the combination of two columns as unique constrain.
         * This uses the Validator::replacer('two_column_unique_undeleted') below for nicer translations.
         *
         * $parameters[0] - the name of the first table we're looking at
         * $parameters[1] - the ID (this will be 0 on new creations)
         * $parameters[2] - the name of the second field we're looking at
         * $parameters[3] - the value that the request is passing for the second table we're
         *                  checking for uniqueness across
         *
         */
        Validator::extend('two_column_unique_undeleted', function ($attribute, $value, $parameters, $validator) {

            if (count($parameters)) {
                
                $count = DB::table($parameters[0])
                    ->select('id')
                    ->where($attribute, '=', $value)
                    ->where('id', '!=', $parameters[1]);

                if ($parameters[3]!='') {
                    $count = $count->where($parameters[2], $parameters[3]);
                }

                $count = $count->whereNull('deleted_at')
                    ->count();

                return $count < 1;
            }
        });


        /**
         * This is the validator replace static method that allows us to pass the $parameters of the table names
         * into the translation string in validation.two_column_unique_undeleted for two_column_unique_undeleted
         * validation messages.
         *
         * This is invoked automatically by Validator::extend('two_column_unique_undeleted') above and
         * produces a translation like: "The name value must be unique across categories and category type."
         *
         * The $parameters passed coincide with the ones the two_column_unique_undeleted custom validator above
         * uses, so $parameter[0] is the first table and so $parameter[2] is the second table.
         */
        Validator::replacer('two_column_unique_undeleted', function($message, $attribute, $rule, $parameters) {
            $message = str_replace(':table1', $parameters[0], $message);
            $message = str_replace(':table2', $parameters[2], $message);

            // Change underscores to spaces for a friendlier display
            $message = str_replace('_', ' ', $message);
            return $message;
        });


        // Prevent circular references
        //
        // Example usage in Location model where parent_id references another Location:
        //
        //   protected $rules = array(
        //     'parent_id' => 'non_circular:locations,id,10'
        //   );
        //
        Validator::extend('non_circular', function ($attribute, $value, $parameters, $validator) {
            if (count($parameters) < 2) {
                throw new \Exception('Required validator parameters: <table>,<primary key>[,depth]');
            }

            // Parameters from the rule implementation ($pk will likely be 'id')
            $table = array_get($parameters, 0);
            $pk = array_get($parameters, 1);
            $depth = (int) array_get($parameters, 2, 50);

            // Data from the edited model
            $data = $validator->getData();

            // The primary key value from the edited model
            $data_pk = array_get($data, $pk);
            $value_pk = $value;

            // If we’re editing an existing model and there is a parent value set…
            while ($data_pk && $value_pk) {

                // It’s not valid for any parent id to be equal to the existing model’s id
                if ($data_pk == $value_pk) {
                    return false;
                }

                // Avoid accidental infinite loops
                if (--$depth < 0) {
                    return false;
                }

                // Get the next parent id
                $value_pk = DB::table($table)->select($attribute)->where($pk, '=', $value_pk)->value($attribute);
            }

            return true;
        });

        // Yo dawg. I heard you like validators.
        // This validates the custom validator regex in custom fields.
        // We're just checking that the regex won't throw an exception, not
        // that it's actually correct for what the user intended.

        Validator::extend('valid_regex', function ($attribute, $value, $parameters, $validator) {

            // Make sure it's not just an ANY format
            if ($value != '') {

                //  Check that the string starts with regex:
                if (strpos($value, 'regex:') === false) {
                    return false;
                }

                $test_string = 'My hovercraft is full of eels';

                // We have to stip out the regex: part here to check with preg_match
                $test_pattern = str_replace('regex:', '', $value);

                try {
                    preg_match($test_pattern, $test_string);

                    return true;
                } catch (\Exception $e) {
                    return false;
                }
            }

            return true;
        });

        // This ONLY works for create/update user forms, since the Update Profile Password form doesn't
        // include any of these additional validator fields
        Validator::extend('disallow_same_pwd_as_user_fields', function ($attribute, $value, $parameters, $validator) {
            $data = $validator->getData();

            if (array_key_exists('username', $data)) {
                if ($data['username'] == $data['password']) {
                    return false;
                }
            }

            if (array_key_exists('email', $data)) {
                if ($data['email'] == $data['password']) {
                    return false;
                }
            }

            if (array_key_exists('first_name', $data)) {
                if ($data['first_name'] == $data['password']) {
                    return false;
                }
            }

            if (array_key_exists('last_name', $data)) {
                if ($data['last_name'] == $data['password']) {
                    return false;
                }
            }

            return true;
        });

        Validator::extend('letters', function ($attribute, $value, $parameters) {
            return preg_match('/\pL/', $value);
        });

        Validator::extend('numbers', function ($attribute, $value, $parameters) {
            return preg_match('/\pN/', $value);
        });

        Validator::extend('case_diff', function ($attribute, $value, $parameters) {
            return preg_match('/(\p{Ll}+.*\p{Lu})|(\p{Lu}+.*\p{Ll})/u', $value);
        });

        Validator::extend('symbols', function ($attribute, $value, $parameters) {
            return preg_match('/\p{Z}|\p{S}|\p{P}/', $value);
        });

        Validator::extend('cant_manage_self', function ($attribute, $value, $parameters, $validator) {
            // $value is the actual *value* of the thing that's being validated
            // $attribute is the name of the field that the validation is running on - probably manager_id in our case
            // $parameters are the optional parameters - an array for everything, split on commas. But we don't take any params here.
            // $validator gives us proper access to the rest of the actual data
            $data = $validator->getData();

            if (array_key_exists('id', $data)) {
                if ($value && $value == $data['id']) {
                    // if you definitely have an ID - you're saving an existing user - and your ID matches your manager's ID - fail.
                    return false;
                } else {
                    return true;
                }
            } else {
                // no 'id' key to compare against (probably because this is a new user)
                // so it automatically passes this validation
                return true;
            }
        });

        Validator::extend('is_unique_department', function ($attribute, $value, $parameters, $validator) {
            $data = $validator->getData();

            if (
                array_key_exists('location_id', $data) && $data['location_id'] !== null &&
                array_key_exists('company_id', $data) && $data['company_id'] !== null
            ) {
                //for updating existing departments
                if(array_key_exists('id', $data) && $data['id'] !== null){
                    $count = Department::where('name', $data['name'])
                        ->where('location_id', $data['location_id'])
                        ->where('company_id', $data['company_id'])
                        ->whereNotNull('company_id')
                        ->whereNotNull('location_id')
                        ->where('id', '!=', $data['id'])
                        ->count('name');

                    return $count < 1;
                }else // for entering in new departments
                {
                $count = Department::where('name', $data['name'])
                    ->where('location_id', $data['location_id'])
                    ->where('company_id', $data['company_id'])
                    ->whereNotNull('company_id')
                    ->whereNotNull('location_id')
                    ->count('name');

                return $count < 1;
            }
        }
            else {
                return true;
        }
        });

        Validator::extend('not_array', function ($attribute, $value, $parameters, $validator) {
            return !is_array($value);
        });

        // This is only used in Models/CustomFieldset.php - it does automatic validation for checkboxes by making sure
        // that the submitted values actually exist in the options.
        Validator::extend('checkboxes', function ($attribute, $value, $parameters, $validator){
            $field = CustomField::where('db_column', $attribute)->first();
            $options = $field->formatFieldValuesAsArray();

            if(is_array($value)) {
                $invalid = array_diff($value, $options);
                if(count($invalid) > 0) {
                    return false;
                }
            }

            // for legacy, allows users to submit a comma separated string of options
            elseif(!is_array($value)) {
                $exploded = array_map('trim', explode(',', $value));
                $invalid = array_diff($exploded, $options);
                if(count($invalid) > 0) {
                    return false;
                }
            }

            return true;
        });

        // Validates that a radio button option exists
        Validator::extend('radio_buttons', function ($attribute, $value) {
            $field = CustomField::where('db_column', $attribute)->first();
            $options = $field->formatFieldValuesAsArray();

            return in_array($value, $options);
        });

        // Validates that the company of the validated object matches the company of the location in case of scoped locations
        Validator::extend('fmcs_location', function ($attribute, $value, $parameters, $validator){
            $settings = Setting::getSettings();
            if ($settings->full_multiple_companies_support == '1' && $settings->scope_locations_fmcs == '1') {
                $company_id = array_get($validator->getData(), 'company_id');
                $location = Location::find($value);

                if (($location) && ($company_id != $location->company_id)) {
                    return false;
                }
            }
            return true;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
