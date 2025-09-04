<?php

namespace App\Models\Traits;

use App\Models\CustomField;
use App\Models\CustomFieldset;
use Illuminate\Support\Collection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Event;
use App\Models\DefaultValuesForCustomFields;

/*********************************
 * Trait HasCustomFields
 * @package App\Models\Traits
 *
 * How to use: declare a PHP function getFieldset that will return your fieldset (not the ID, the actual set)
 *
 */

trait HasCustomFields
{
    protected static function bootHasCustomFields()
    {
        // https://tech.chrishardie.com/2022/define-fire-listen-custom-laravel-model-events-trait/

        static::registerModelEvent('validating', function ($model, $event) {
//            \Log::error("Uh, something happened? Something good, maybe?");
//            \Log::error("model: $model, event: $event");
//            \Log::error("WHATS MY NAME? " . HasCustomFields::class);
//            dump(class_uses_recursive($model));
            if (in_array(HasCustomFields::class, class_uses_recursive($model))) {
                \Log::error("!!!!!!!!!!!!! YOU ARE USING THE TRAIT!");
                self::augmentValidationRulesForCustomFields($model);
            } else {
                \Log::error("You aren't useing the trait so go away");
            }
        });
    }

    /***************
     * @return CustomFieldset|null
     *
     * This function by default will use the "getFieldsetKey()" method to
     * return the customFieldset (or null) for this particular item. If
     * necessary, you can override this method if your getFieldsetKey()
     * cannot respond to `->fieldset` or `->id`.
     */
    public function getFieldset(): ?CustomFieldset {
        $pivot = $this->getFieldsetKey();
        if(is_int($pivot)) { //why does this look just like the other thing? (below, look for is_int()
            return CustomFieldset::find($pivot);
        }
        return $pivot?->fieldset; //this is bonkers, why is this even firing?!
    }

    /**********************
     * @return Object|int|null
     * (if this is in PHP 8.0, can we just put that as the signature?)
     *
     * This is the main method you have to override. It should either return an
     * Object who you can call `->fieldset` on and get a fieldset object, and also
     * be able to call `->id` on to get a unique key to be able to show custom fields.
     * For example, for Assets, the element that is returned is the 'model' for the Asset.
     * For something like Users, which will probably have only one universal set of custom fields,
     * it should just return the Fieldset ID for it. Or, if there are no custom fields, it should
     * return null
     */
    abstract public function getFieldsetKey(): Object|int|null; // php v8 minimum, GOOD. TODO

    /***********************
     * @param int $fieldset_id
     * @return Collection
     *
     * This is the main method you need to override to return a list of things that are *using* this fieldset
     * The format is an array with keys: a URL, and values. So, for assets, it might return
     * {
     *    "models/14" => "MacBook Pro 13 (model no: 12345)"
     * }
     */
    abstract public static function getFieldsetUsers(int $fieldset_id): array;

    public static function augmentValidationRulesForCustomFields($model) {
        \Log::debug("Augmenting validation rules for custom fields!!!!!!");
        $fieldset = $model->getFieldset();
        if ($fieldset) {
            foreach ($fieldset->fields as $field){
                if($field->format == 'BOOLEAN'){ // TODO - this 'feels' like entanglement of concerns?
                    $model->{$field->db_column} = filter_var($model->{$model->db_column}, FILTER_VALIDATE_BOOLEAN);
                }
            }

            if(!$model->rules) {
                $model->rules = [];
            }
            $model->rules += $model->getFieldset()->validation_rules();
            \Log::debug("FINAL RULES ARE: ".print_r($model->rules,true));
        }

    }

    public function getDefaultValue(CustomField $field)
    {
        $pivot = $this->getFieldsetKey(); // TODO - feels copypasta-ish?
        $key_id = null;

        if( is_int($pivot) ) { // TODO: *WHY* does this code repeat?!
            $key_id = $pivot; // now we're done
        } elseif( is_object($pivot) ) {
            $key_id = $pivot?->id;
        }
        if(is_null($key_id)) {
            return;
        }

        // TODO - begninng to think my custom scope really should be just an integer :/
        return DefaultValuesForCustomFields::where('type',self::class)
            ->where('custom_field_id',$field->id)
            ->where('item_pivot_id',$key_id)->first()?->default_value;
    }

    public function customFill(Request $request, User $user, bool $shouldSetDefaults = false) {
        $success = true;
        if ($this->getFieldset()) {
            foreach ($this->getFieldset()->fields as $field) {
                if (is_array($request->input($field->db_column))) {
                    $field_value = implode(', ', $request->input($field->db_column));
                } else {
                    $field_value = $request->input($field->db_column);
                }

                if ($shouldSetDefaults && (is_null($field_value) || $field_value === '')) {
                    $field_value = $this->getDefaultValue($field);
                }
                if ($field->field_encrypted == '1') {
                    if ($user->can('admin')) {
                        $this->{$field->db_column} = Crypt::encrypt($field_value);
                    } else {
                        $success = false;
                        continue; //may not be necessary? I'm not sure. I like the other way of doing this TODO
                    }
                } else {
                    $this->{$field->db_column} = $request->input($field->db_column);
                }
            }
        }
        return $success;
    }
}