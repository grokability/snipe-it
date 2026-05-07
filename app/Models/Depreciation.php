<?php

namespace App\Models;

use App\Models\Traits\Searchable;
use App\Presenters\DepreciationPresenter;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Watson\Validating\ValidatingTrait;

class Depreciation extends SnipeModel
{
    use HasFactory;

    protected $presenter = DepreciationPresenter::class;

    use Presentable;

    // Declare the rules for the form validation
    protected $rules = [
        'name' => 'required|max:255|unique:depreciations,name',
        'months' => 'required|max:3600|integer',
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

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'months',
    ];

    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = [
        'name',
        'months',
    ];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
        'adminuser' => ['first_name', 'last_name', 'display_name'],
    ];

    public function isDeletable()
    {
        return Gate::allows('delete', $this)
            && (($this->assets_count ?? $this->assets()->count()) === 0)
            && (($this->licenses_count ?? $this->licenses()->count()) === 0)
            && (($this->models_count ?? $this->models()->count()) === 0)
            && ($this->deleted_at == '');
    }

    /**
     * Establishes the depreciation -> models relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v5.0]
     *
     * @return Relation
     */
    public function models()
    {
        return $this->hasMany(AssetModel::class, 'depreciation_id');
    }

    /**
     * Establishes the depreciation -> licenses relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v5.0]
     *
     * @return Relation
     */
    public function licenses()
    {
        return $this->hasMany(License::class, 'depreciation_id');
    }

    /**
     * Establishes the depreciation -> assets relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v5.0]
     *
     * @return Relation
     */
    public function assets()
    {
        return $this->hasManyThrough(Asset::class, AssetModel::class, 'depreciation_id', 'model_id');
    }

    /**
     * Get the user that created the depreciation
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v7.0.13]
     *
     * @return Relation
     */
    public function adminuser()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    /**
     * -----------------------------------------------
     * BEGIN QUERY SCOPES
     * -----------------------------------------------
     **/
    public function scopeOrderByCreatedBy($query, $order)
    {
        return $query->leftJoin('users as admin_sort', 'depreciations.created_by', '=', 'admin_sort.id')->select('depreciations.*')->orderBy('admin_sort.first_name', $order)->orderBy('admin_sort.last_name', $order);
    }
}
