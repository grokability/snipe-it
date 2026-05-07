<?php

namespace App\Models;

use App\Http\Traits\UniqueUndeletedTrait;
use App\Models\Traits\HasUploads;
use App\Models\Traits\Loggable;
use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use App\Presenters\SupplierPresenter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;
use Watson\Validating\ValidatingTrait;

class Supplier extends SnipeModel
{
    use HasFactory;
    use HasUploads;
    use Presentable;
    use SoftDeletes;

    protected $presenter = SupplierPresenter::class;

    protected $table = 'suppliers';

    protected $rules = [
        'name' => 'required|max:255|unique_undeleted',
        'fax' => 'min:7|max:35|nullable',
        'phone' => 'min:7|max:35|nullable',
        'contact' => 'max:100|nullable',
        'notes' => 'max:191|nullable', // Default string length is 191 characters..
        'email' => 'email|max:150|nullable',
        'address' => 'max:250|nullable',
        'address2' => 'max:250|nullable',
        'city' => 'max:191|nullable',
        'state' => 'min:2|max:191|nullable',
        'country' => 'min:2|max:191|nullable',
        'zip' => 'max:10|nullable',
        'url' => 'sometimes|url|nullable|string|max:250',
    ];

    /**
     * Whether the model should inject it's identifier to the unique
     * validation rules before attempting validation. If this property
     * is not set in the model it will default to true.
     *
     * @var bool
     */
    protected $injectUniqueIdentifier = true;

    use Loggable;
    use Searchable;
    use UniqueUndeletedTrait;
    use ValidatingTrait;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = ['name', 'notes', 'phone', 'fax', 'url', 'email', 'contact', 'address', 'address2', 'city', 'state', 'country', 'zip'];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'address', 'address2', 'city', 'state', 'country', 'zip', 'phone', 'fax', 'email', 'contact', 'url', 'tag_color', 'notes'];

    public function isDeletable()
    {
        return Gate::allows('delete', $this)
            && (($this->assets_count ?? $this->assets()->count()) === 0)
            && (($this->licenses_count ?? $this->licenses()->count()) === 0)
            && (($this->consumables_count ?? $this->consumables()->count()) === 0)
            && (($this->accessories_count ?? $this->accessories()->count()) === 0)
            && (($this->components_count ?? $this->components()->count()) === 0)
            && (($this->maintenances_count ?? $this->maintenances()->count()) === 0)
            && ($this->deleted_at == '');
    }

    /**
     * Eager load counts
     *
     * We do this to eager load the "count" of seats from the controller.
     * Otherwise calling "count()" on each model results in n+1.
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v4.0]
     *
     * @return Relation
     */
    public function assetsRelation()
    {
        return $this->hasMany(Asset::class)->whereNull('deleted_at')->selectRaw('supplier_id, count(*) as count')->groupBy('supplier_id');
    }

    /**
     * Establishes the supplier -> assets relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v1.0]
     *
     * @return Relation
     */
    public function assets()
    {
        return $this->hasMany(Asset::class, 'supplier_id');
    }

    /**
     * Establishes the supplier -> accessories relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v1.0]
     *
     * @return Relation
     */
    public function accessories()
    {
        return $this->hasMany(Accessory::class, 'supplier_id');
    }

    /**
     * Establishes the supplier -> component relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v6.1.1]
     *
     * @return Relation
     */
    public function components()
    {
        return $this->hasMany(Component::class, 'supplier_id');
    }

    /**
     * Establishes the supplier -> component relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v6.1.1]
     *
     * @return Relation
     */
    public function consumables()
    {
        return $this->hasMany(Consumable::class, 'supplier_id');
    }

    /**
     * Establishes the supplier -> admin user relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @return Relation
     */
    public function adminuser()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    /**
     * Establishes the supplier -> asset maintenances relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v1.0]
     */
    public function maintenances(): Relation
    {
        return $this->hasMany(Maintenance::class, 'supplier_id');
    }

    /**
     * Return the number of assets by supplier
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v1.0]
     *
     * @return int
     */
    public function num_assets()
    {
        if ($this->assetsRelation->first()) {
            return $this->assetsRelation->first()->count;
        }

        return 0;
    }

    /**
     * Establishes the supplier -> license relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v1.0]
     *
     * @return Relation
     */
    public function licenses()
    {
        return $this->hasMany(License::class, 'supplier_id');
    }

    /**
     * Return the number of licenses by supplier
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v1.0]
     *
     * @return int
     */
    public function num_licenses()
    {
        return $this->licenses()->count();
    }

    /**
     * Add http to the url in suppliers if the user didn't give one
     *
     * @todo this should be handled via validation, no?
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function addhttp($url)
    {
        if (($url != '') && (! preg_match('~^(?:f|ht)tps?://~i', $url))) {
            $url = 'http://'.$url;
        }

        return $url;
    }

    /**
     * Query builder scope to order on the user that created it
     */
    public function scopeOrderByCreatedByName($query, $order)
    {
        return $query->leftJoin('users as admin_sort', 'suppliers.created_by', '=', 'admin_sort.id')->select('suppliers.*')->orderBy('admin_sort.first_name', $order)->orderBy('admin_sort.last_name', $order);
    }
}
