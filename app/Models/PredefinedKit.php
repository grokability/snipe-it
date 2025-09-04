<?php

namespace App\Models;

use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Validation\Rule;
use Watson\Validating\ValidatingTrait;

/**
 * Model for predefined kits.
 *
 * @author  [D. Minaev.] [<dmitriy.minaev.v@gmail.com>]
 * @version v1.0
 */
class PredefinedKit extends SnipeModel
{
    protected $presenter = \App\Presenters\PredefinedKitPresenter::class;
    use HasFactory;
    use Presentable;
    protected $table = 'kits';

    /**
     * Category validation rules
     */
    public $rules = [
        'name' => 'required|min:1|max:255|unique',
    ];

    use ValidatingTrait;

    public $modelRules = [
        'model_id' => 'required|exists:models,id',
        'quantity' => 'required|integer|min:1',
        'pivot_id' => 'integer|exists:kits_models,id',
    ];

    /**
     * this rules use in edit an attached asset model form
     * see PredefinedKit::_makeRuleHelper function for details
     *
     * @param int  $model_id
     * @param bool $new      = true if append a new element to kit
     */
    public function makeModelRules($model_id, $new = false)
    {
        return $this->_makeRuleHelper('models', 'kits_models', 'model_id', $model_id, $new);
    }

    /**
     * this rules use in edit an attached license form
     * see PredefinedKit::_makeRuleHelper function for details
     *
     * @param int  $license_id
     * @param bool $new        = true if append a new element to kit
     */
    public function makeLicenseRules($license_id, $new = false)
    {
        return $this->_makeRuleHelper('licenses', 'kits_licenses', 'license_id', $license_id, $new);
    }

    /**
     * this rules use in edit an attached accessory form
     * see PredefinedKit::_makeRuleHelper function for details
     *
     * @param int  $accessoriy_id
     * @param bool $new           = true if append a new element to kit
     */
    public function makeAccessoryRules($accessory_id, $new = false)
    {
        return $this->_makeRuleHelper('accessories', 'kits_accessories', 'accessory_id', $accessory_id, $new);
    }

    /**
     * this rules use in edit an attached consumable form
     * see PredefinedKit::_makeRuleHelper function for details
     *
     * @param int  $consumable_id
     * @param bool $new           = true if append a new element to kit
     */
    public function makeConsumableRules($consumable_id, $new = false)
    {
        return $this->_makeRuleHelper('consumables', 'kits_consumables', 'consumable_id', $consumable_id, $new);
    }

    /**
     * Make rules for validation kit attached elements via Illuminate\Validation\Rule
     * checks:
     * uniqueness of the record in table for this kit
     * existence of record in table
     * and simple types check
     *
     * @param  string $table          element table name
     * @param  string $pivot_table    kit+element table name
     * @param  string $pivot_elem_key element key name inside pivot table
     * @param  int    $element_id
     * @param  bool   $new            = true if append a new element to kit
     * @return array
     */
    protected function _makeRuleHelper($table, $pivot_table, $pivot_elem_key, $element_id, $new)
    {
        $rule = [
            $pivot_elem_key => [
                'required',
                "exists:$table,id",
                Rule::unique($pivot_table)->whereNot($pivot_elem_key, $element_id)->where('kit_id', $this->id),
            ],
            'quantity' => 'required|integer|min:1',
        ];
        if (! $new) {
            $rule['pivot_id'] = "integer|exists:$pivot_table,id";
        }

        return $rule;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    use Searchable;

    /**
     * The attributes that should be included when searching the kit.
     *
     * @var array
     */
    protected $searchableAttributes = ['name'];

    /**
     * The relations and their attributes that should be included when searching the kit.
     *
     * @var array
     */
    protected $searchableRelations = [];


    public function adminuser()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }


    /**
     * Establishes the kits -> models relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function models()
    {
        return $this->belongsToMany(\App\Models\AssetModel::class, 'kits_models', 'kit_id', 'model_id')->withPivot('id', 'quantity');
    }

    public function assets()
    {
        return $this->hasManyThrough(\App\Models\Asset::class, \App\Models\AssetModel::class, 'country_id', 'user_id');
    }

    /**
     * Establishes the kits -> licenses relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function licenses()
    {
        return $this->belongsToMany(\App\Models\License::class, 'kits_licenses', 'kit_id', 'license_id')->withPivot('id', 'quantity');
    }

    /**
     * Establishes the kits -> licenses relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function consumables()
    {
        return $this->belongsToMany(\App\Models\Consumable::class, 'kits_consumables', 'kit_id', 'consumable_id')->withPivot('id', 'quantity');
    }

    /**
     * Establishes the kits -> licenses relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function accessories()
    {
        return $this->belongsToMany(\App\Models\Accessory::class, 'kits_accessories', 'kit_id', 'accessory_id')->withPivot('id', 'quantity');
    }

    /**
     * -----------------------------------------------
     * BEGIN QUERY SCOPES
     * -----------------------------------------------
     **/

    public function scopeOrderByCreatedBy($query, $order)
    {
        return $query->leftJoin('users as admin_sort', 'kits.created_by', '=', 'admin_sort.id')->select('kits.*')->orderBy('admin_sort.first_name', $order)->orderBy('admin_sort.last_name', $order);
    }
}
