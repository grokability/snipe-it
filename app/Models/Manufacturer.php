<?php

namespace App\Models;

use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;
use Watson\Validating\ValidatingTrait;

class Manufacturer extends SnipeModel
{
    use HasFactory;

    protected $presenter = \App\Presenters\ManufacturerPresenter::class;
    use Presentable;
    use SoftDeletes;

    protected $table = 'manufacturers';

    // Declare the rules for the form validation
    protected $rules = [
        'name'   => 'required|min:2|max:255|unique:manufacturers,name,NULL,id,deleted_at,NULL',
        'url'   => 'nullable|starts_with:http://,https://,afp://,facetime://,file://,irc://',
        'support_email'   => 'email|nullable',
        'support_url'   => 'nullable|starts_with:http://,https://,afp://,facetime://,file://,irc://',
        'warranty_lookup_url' => 'nullable|starts_with:http://,https://,afp://,facetime://,file://,irc://'
    ];

    protected $hidden = ['user_id'];

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
        'image',
        'support_email',
        'support_phone',
        'support_url',
        'url',
        'warranty_lookup_url',
        'tag_color',
        'notes',
    ];

    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = ['name', 'created_at', 'notes'];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [];

    public function isDeletable()
    {
        return Gate::allows('delete', $this)
            && (($this->assets_count ?? $this->assets()->count()) === 0)
            && (($this->licenses_count ?? $this->licenses()->count()) === 0)
            && (($this->consumables_count ?? $this->consumables()->count()) === 0)
            && (($this->accessories_count ?? $this->accessories()->count()) === 0)
            && (($this->components_count ?? $this->components()->count()) === 0)
            && ($this->deleted_at == '');
    }

    public function assets()
    {
        return $this->hasManyThrough(\App\Models\Asset::class, \App\Models\AssetModel::class, 'manufacturer_id', 'model_id');
    }

    public function models()
    {
        return $this->hasMany(\App\Models\AssetModel::class, 'manufacturer_id');
    }

    public function licenses()
    {
        return $this->hasMany(\App\Models\License::class, 'manufacturer_id');
    }

    public function accessories()
    {
        return $this->hasMany(\App\Models\Accessory::class, 'manufacturer_id');
    }

    public function consumables()
    {
        return $this->hasMany(\App\Models\Consumable::class, 'manufacturer_id');
    }

    public function components()
    {
        return $this->hasMany(\App\Models\Component::class, 'manufacturer_id');
    }

    public function adminuser()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }


    /**
     * Query builder scope to order on the user that created it
     */
    public function scopeOrderByCreatedBy($query, $order)
    {
        return $query->leftJoin('users as admin_sort', 'manufacturers.created_by', '=', 'admin_sort.id')->select('manufacturers.*')->orderBy('admin_sort.first_name', $order)->orderBy('admin_sort.last_name', $order);
    }
}
