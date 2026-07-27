<?php

namespace App\Providers;

use App\Models\CustomField;
use App\Models\Location;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

/**
 * This service provider handles a few custom validation rules.
 *
 * PHP version 5.5.9
 *
 * @version    v3.0
 */
class ValidationServiceProvider extends ServiceProvider
{
    /**
     * Custom email array validation
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v3.0]
     *
     * @return void
     */
    public function boot()
    {

        // Email array validator
        Validator::extend('email_array', function ($attribute, $value, $parameters, $validator) {
            $value = str_replace(' ', '', $value);
            $array = explode(',', $value);
            $email_to_validate = [];

            foreach ($array as $email) { // loop over values
                $email_to_validate['alert_email'][] = $email;
            }

            $rules = ['alert_email.*' => 'email'];
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
                if (($parameters[0] == 'assets') && ($attribute == 'serial') && (Setting::getSettings()->unique_serial != '1')) {
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
         * Exists if undeleted
         *
         * Validates that a value points at a row that exists AND is not
         * soft-deleted. Companion to unique_undeleted, used to gate places
         * where an ID from user input is looked up (checkout targets, etc.).
         *
         * Laravel's built-in `exists` rule runs on the raw DB table and does
         * not filter soft-deleted rows unless the caller adds a `whereNull`
         * chain by hand. This custom rule bakes that in.
         *
         * $parameters[0] is the TABLE NAME to query
         * $parameters[1] is the COLUMN to match against (defaults to id)
         *
         * Usage: `exists_undeleted:users,id`
         */
        Validator::extend('exists_undeleted', function ($attribute, $value, $parameters, $validator) {
            if (count($parameters) < 1) {
                return false;
            }
            $column = $parameters[1] ?? 'id';

            return DB::table($parameters[0])
                ->where($column, '=', $value)
                ->whereNull('deleted_at')
                ->exists();
        });

        /**
         * Unique-if-undeleted, scoped to one or more sibling columns on the same row.
         *
         * Where `unique_undeleted` enforces global uniqueness on a column,
         * `unique_undeleted_in_scope` enforces uniqueness only within a bucket
         * defined by the values of one or more OTHER columns on the same row.
         * Used for tree-structured tables where a child name only needs to be
         * unique among its siblings, and (under FMCS) among its siblings in
         * the same company.
         *
         * NULL is treated as its own bucket per standard SQL semantics: two
         * top-level locations (parent_id IS NULL) named "HQ" collide with
         * each other, but "HQ" at the top level does not collide with "HQ"
         * that has a parent.
         *
         * $parameters[0] is the TABLE NAME being queried
         * $parameters[1] is the ID of the row being edited (0 for creates)
         * $parameters[2..N] are the sibling COLUMN NAMES that make up the scope
         *
         * The UniqueUndeletedTrait's prepareUniqueUndeletedInScopeRule method
         * prepends the table + id for you, so on the model you just declare:
         *   'name' => 'unique_undeleted_in_scope:parent_id,company_id'
         */
        Validator::extend('unique_undeleted_in_scope', function ($attribute, $value, $parameters, $validator) {
            if (count($parameters) < 2) {
                return true;
            }

            $table = $parameters[0];
            $ignoreId = (int) $parameters[1];
            $scopeColumns = array_slice($parameters, 2);
            $data = $validator->getData();

            $query = DB::table($table)
                ->whereNull('deleted_at')
                ->where($attribute, '=', $value);

            if ($ignoreId > 0) {
                $query->where('id', '!=', $ignoreId);
            }

            foreach ($scopeColumns as $column) {
                $scopeValue = $data[$column] ?? null;
                if ($scopeValue === null || $scopeValue === '') {
                    $query->whereNull($column);
                } else {
                    $query->where($column, '=', $scopeValue);
                }
            }

            return $query->count() < 1;
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
         */
        Validator::extend('two_column_unique_undeleted', function ($attribute, $value, $parameters, $validator) {

            if (count($parameters)) {

                $count = DB::table($parameters[0])
                    ->select('id')
                    ->where($attribute, '=', $value)
                    ->where('id', '!=', $parameters[1]);

                if ($parameters[3] != '') {
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
        Validator::replacer('two_column_unique_undeleted', function ($message, $attribute, $rule, $parameters) {
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

        // Together, these two validators enforce a one-level-deep self-
        // referential hierarchy. They're split (rather than combined into one
        // rule) so each failure produces a message that actually describes
        // the cause — "the parent you picked isn't top-level" reads very
        // differently from "this row already has children of its own."
        //
        // Example usage on a parent_id column self-referencing the same table:
        //   'parent_id' => 'nullable|integer|exists:companies,id|parent_must_be_top_level:companies,id|must_have_no_children:companies,id'

        // The chosen parent_id (1) must not be the row itself (no self-parent),
        // and (2) must itself be a top-level row (its own parent_id IS NULL).
        // Either failure means saving would create depth > 1.
        Validator::extend('parent_must_be_top_level', function ($attribute, $value, $parameters, $validator) {
            if (is_null($value) || $value === '') {
                return true;
            }

            if (count($parameters) < 2) {
                throw new \Exception('Required validator parameters: <table>,<primary key>');
            }

            $table = $parameters[0];
            $pk = $parameters[1];

            $data = $validator->getData();
            $modelId = $data[$pk] ?? null;

            if ($modelId && (int) $modelId === (int) $value) {
                return false;
            }

            $chosenParentParentId = DB::table($table)->where($pk, $value)->value('parent_id');

            return is_null($chosenParentParentId);
        });

        // If the row being saved already has children of its own, it can't be
        // assigned a parent — doing so would push those children to depth 2.
        // Only checked on update (a brand-new row has no children yet).
        Validator::extend('must_have_no_children', function ($attribute, $value, $parameters, $validator) {
            if (is_null($value) || $value === '') {
                return true;
            }

            if (count($parameters) < 2) {
                throw new \Exception('Required validator parameters: <table>,<primary key>');
            }

            $table = $parameters[0];
            $pk = $parameters[1];

            $data = $validator->getData();
            $modelId = $data[$pk] ?? null;

            if (! $modelId) {
                return true;
            }

            return ! DB::table($table)->where('parent_id', $modelId)->exists();
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

        /**
         * Check that the 'name' field is unique in the table while within both company_id and location_id
         * This is only used by Departments right now, but could be used elsewhere in the future.
         */
        Validator::extend('is_unique_across_company_and_location', function ($attribute, $value, $parameters, $validator) {
            $data = $validator->getData();
            $table = array_get($parameters, 0);

            $count = DB::table($table)->select($attribute)
                ->where($attribute, $value)
                ->whereNull('deleted_at');

            if (array_key_exists('id', $data) && $data['id'] !== null) {
                $count = $count->where('id', '!=', $data['id']);
            }

            if (array_key_exists('location_id', $data) && $data['location_id'] !== null) {
                $count = $count->where('location_id', $data['location_id']);
            }

            if (array_key_exists('company_id', $data) && $data['company_id'] !== null) {
                $count = $count->where('company_id', $data['company_id']);
            }

            $count = $count->count('name');

            return $count < 1;

        });

        Validator::extend('not_array', function ($attribute, $value, $parameters, $validator) {
            return ! is_array($value);
        });

        // This is only used in Models/CustomFieldset.php - it does automatic validation for checkboxes by making sure
        // that the submitted values actually exist in the options.
        Validator::extend('checkboxes', function ($attribute, $value, $parameters, $validator) {
            $field = CustomField::where('db_column', $attribute)->first();
            $options = $field->formatFieldValuesAsArray();

            if (is_array($value)) {
                $invalid = array_diff($value, $options);
                if (count($invalid) > 0) {
                    return false;
                }
            }

            // for legacy, allows users to submit a comma separated string of options
            elseif (! is_array($value)) {
                $exploded = array_map('trim', explode(',', $value));
                $invalid = array_diff($exploded, $options);
                if (count($invalid) > 0) {
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

        Validator::replacer('fmcs_location', function ($message, $attribute, $rule, $parameters, $validator) {
            $locationId = $validator->getData()[$attribute] ?? null;
            $location = $locationId ? Location::find($locationId) : null;

            return str_replace(
                [':location_company', ':location'],
                [
                    $location?->company?->name ?? trans('general.unassigned'),
                    $location?->name ?? '?',
                ],
                $message
            );
        });

        // Enforces "Company must be picked" when FMCS is on AND
        // null_company_is_floater is disabled (strict mode). Without this
        // rule a companied non-superuser can save a form with an unset
        // company dropdown, land a row with company_id=NULL, and then
        // have that row instantly filtered out of their own view by the
        // strict-mode scope. See #19192. Passes when:
        //  - FMCS is off (nothing to enforce)
        //  - null_company_is_floater is on (nulls are legal floaters)
        //  - value is present (form was filled in)
        //  - no auth context (CLI, seeders, or importers bypass, matching
        //    the SaveUserRequest cannot_make_floater gate posture)
        //  - acting user is a superuser (they see everything, so a null
        //    is an explicit choice, not an accident)
        //  - acting user has NO company memberships. In strict mode
        //    such users legitimately operate in the null "pseudo-company"
        //    namespace, where Company::scopeCompanyablesDirectly scopes
        //    them to whereNull($company_id) and null IS a valid company
        //    id for them. Forcing them to pick a non-null company would
        //    both lock them out of their normal workflow and produce a
        //    row they wouldn't be able to see afterward.
        // extendImplicit (not extend) because Laravel skips "explicit"
        // rules when the field is null or absent. Since the whole point
        // of fmcs_company is to fire ON blank submissions, it must run
        // implicitly. Same reason built-in rules like `required`,
        // `filled`, `present`, `accepted` are all registered implicit.
        Validator::extendImplicit('fmcs_company', function ($attribute, $value, $parameters, $validator) {
            $settings = Setting::getSettings();
            if (! $settings->full_multiple_companies_support) {
                return true;
            }
            if ((bool) $settings->null_company_is_floater) {
                return true;
            }
            if (! empty($value)) {
                return true;
            }
            if (! auth()->check()) {
                return true;
            }
            $actor = auth()->user();
            if ($actor->isSuperUser()) {
                return true;
            }
            if (! $actor->companies()->exists()) {
                return true;
            }

            return false;
        });

        Validator::replacer('fmcs_company', function ($message) {
            return str_replace(':attribute', trans('general.company'), $message);
        });

        // Validates that the company of the validated object matches the company of the location in case of scoped locations
        Validator::extend('fmcs_location', function ($attribute, $value, $parameters, $validator) {
            $settings = Setting::getSettings();
            if ($settings->full_multiple_companies_support == '1' && $settings->scope_locations_fmcs == '1') {
                $data = $validator->getData();
                // Support both multi-company (company_ids[]) and single-company (company_id) requests
                $companyIds = array_filter(array_unique(array_merge(
                    (array) ($data['company_ids'] ?? []),
                    [$data['company_id'] ?? null]
                )));
                // No company context available (e.g. model-level validation before companies are
                // persisted) — nothing to compare against, so skip the check.
                if (empty($companyIds)) {
                    return true;
                }

                $location = Location::find($value);

                if ($location) {
                    $effectiveCompanyId = $location->effectiveFmcsCompanyId();

                    if ($effectiveCompanyId === null) {
                        // No company found anywhere in the parent chain — floater rule applies.
                        return (bool) $settings->null_company_is_floater;
                    }

                    if (! in_array($effectiveCompanyId, $companyIds)) {
                        return false;
                    }
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
    public function register() {}
}
