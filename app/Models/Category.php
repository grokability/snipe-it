<?php

namespace App\Models;

use App\Http\Traits\TwoColumnUniqueUndeletedTrait;
use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;
use Watson\Validating\ValidatingTrait;
use App\Helpers\Helper;
use Illuminate\Support\Str;

/**
 * Model for Categories. Categories are a higher-level group
 * than Asset Models, and handle things like whether or not
 * to require acceptance from the user, whether or not to
 * send a EULA to the user, etc.
 *
 * @version v1.0
 */
class Category extends SnipeModel
{
    use HasFactory;

    protected $presenter = \App\Presenters\CategoryPresenter::class;
    use Presentable;
    use SoftDeletes;

    protected $table = 'categories';
    protected $hidden = ['created_by', 'deleted_at'];

    protected $casts = [
        'alert_on_response' => 'boolean',
        'created_by'      => 'integer',
    ];

    /**
     * Category validation rules
     */
    public $rules = [
        'created_by' => 'numeric|nullable',
        'name'   => 'required|min:1|max:255|two_column_unique_undeleted:category_type',
        'require_acceptance'   => 'boolean',
        'use_default_eula'   => 'boolean',
        'category_type'   => 'required|in:asset,accessory,consumable,component,license',
    ];

    /**
     * Whether the model should inject it's identifier to the unique
     * validation rules before attempting validation. If this property
     * is not set in the model it will default to true.
     *
     * @var bool
     */
    protected $injectUniqueIdentifier = true;
    use ValidatingTrait;
    use TwoColumnUniqueUndeletedTrait;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_type',
        'checkin_email',
        'eula_text',
        'name',
        'require_acceptance',
        'alert_on_response',
        'use_default_eula',
        'created_by',
        'notes',
    ];

    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = ['name', 'category_type', 'notes'];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [];

    /**
     * Checks if category can be deleted
     *
     * @author [Dan Meltzer] [<dmeltzer.devel@gmail.com>]
     * @since  [v5.0]
     * @return bool
     */
    public function isDeletable()
    {

        // We have to check for models as well if the category type is asset
        if ($this->category_type == 'asset') {
            return Gate::allows('delete', $this)
                && ($this->itemCount() == 0)
                && ($this->models_count == 0)
                && ($this->deleted_at == '');
        }

        return Gate::allows('delete', $this)
                && ($this->itemCount() == 0)
                && ($this->deleted_at == '');
    }

    /**
     * Establishes the category -> accessories relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function accessories()
    {
        return $this->hasMany(\App\Models\Accessory::class);
    }

    /**
     * Establishes the category -> licenses relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v4.3]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function licenses()
    {
        return $this->hasMany(\App\Models\License::class);
    }

    /**
     * Establishes the category -> consumables relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function consumables()
    {
        return $this->hasMany(\App\Models\Consumable::class);
    }

    /**
     * Establishes the category -> consumables relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function components()
    {
        return $this->hasMany(\App\Models\Component::class);
    }

    /**
     * Get the number of items in the category. This should NEVER be used in
     * a collection of categories, as you'll end up with an n+1 query problem.
     *
     * It should only be used in a single category context.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v2.0]
     * @return int
     */
    public function itemCount()
    {

        if (isset($this->{Str::plural($this->category_type).'_count'})) {
            return $this->{Str::plural($this->category_type).'_count'};
        }

        switch ($this->category_type) {
        case 'asset':
            return $this->assets->count();
        case 'accessory':
            return $this->accessories->count();
        case 'component':
            return $this->components->count();
        case 'consumable':
            return $this->consumables->count();
        case 'license':
            return $this->licenses->count();
        default:
            return 0;
        }

    }

    /**
     * Establishes the category -> assets relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function assets()
    {
        return $this->hasManyThrough(Asset::class, \App\Models\AssetModel::class, 'category_id', 'model_id');
    }

    /**
     * Establishes the category -> assets relationship but also takes into consideration
     * the setting to show archived in lists.
     *
     * We could have complicated the assets() method above, but keeping this separate
     * should give us more flexibility if we need to return actually archived assets
     * by their category.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v6.1.0]
     * @see    \App\Models\Asset::scopeAssetsForShow()
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function showableAssets()
    {
        return $this->hasManyThrough(Asset::class, \App\Models\AssetModel::class, 'category_id', 'model_id')->AssetsForShow();
    }

    /**
     * Establishes the category -> models relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function models()
    {
        return $this->hasMany(\App\Models\AssetModel::class, 'category_id');
    }

    public function adminuser()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Checks for a category-specific EULA, and if that doesn't exist,
     * checks for a settings level EULA
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v2.0]
     * @return string | null
     */
    public function getEula()
    {

        if ($this->eula_text) {
            return Helper::parseEscapedMarkedown($this->eula_text);
        } elseif ((Setting::getSettings()->default_eula_text) && ($this->use_default_eula == '1')) {
            return Helper::parseEscapedMarkedown(Setting::getSettings()->default_eula_text);
        } else {
            return null;
        }
    }

    /**
     * -----------------------------------------------
     * BEGIN MUTATORS
     * -----------------------------------------------
     **/

    /**
     * This sets the checkin_value to a boolean 0 or 1. This accounts for forms or API calls that
     * explicitly pass the checkin_email field but it has a null or empty value.
     *
     * This will also correctly parse a 1/0 if "true"/"false" is passed.
     *
     * @param  $value
     * @return void
     */
    public function setCheckinEmailAttribute($value)
    {
        $this->attributes['checkin_email'] = (int) filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * -----------------------------------------------
     * BEGIN QUERY SCOPES
     * -----------------------------------------------
     **/

    /**
     * Query builder scope to search on text filters for complex Bootstrap Tables API
     *
     * @param \Illuminate\Database\Query\Builder $query  Query builder instance
     * @param text                               $filter JSON array of search keys and terms
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeByFilter($query, $filter)
    {
        return $query->where(
            function ($query) use ($filter) {
                foreach ($filter as $fieldname => $search_val) {

                    if ($fieldname == 'name') {
                        $query->where('categories.name', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'category_type') {
                        $query->where('categories.category_type', 'LIKE', '%' . $search_val . '%');
                    }

                }


            }
        );
    }

    /**
     * Query builder scope for whether or not the category requires acceptance
     *
     * @author Vincent Sposato <vincent.sposato@gmail.com>
     *
     * @param  \Illuminate\Database\Query\Builder $query Query builder instance
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeRequiresAcceptance($query)
    {
        return $query->where('require_acceptance', '=', true);
    }

    public function scopeOrderByCreatedBy($query, $order)
    {
        return $query->leftJoin('users as admin_sort', 'categories.created_by', '=', 'admin_sort.id')->select('categories.*')->orderBy('admin_sort.first_name', $order)->orderBy('admin_sort.last_name', $order);
    }
}
