<?php

namespace App\Models;

use App\Models\Traits\HasUploads;
use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Watson\Validating\ValidatingTrait;
use \App\Presenters\AssetModelPresenter;
use App\Http\Traits\TwoColumnUniqueUndeletedTrait;

/**
 * Model for Asset Models. Asset Models contain higher level
 * attributes that are common among the same type of asset.
 *
 * @version v1.0
 */
class AssetModel extends SnipeModel
{
    use HasFactory;
    use SoftDeletes;
    use Loggable, Requestable, Presentable;
    use TwoColumnUniqueUndeletedTrait;
    use HasUploads;

    /**
     * Whether the model should inject its identifier to the unique
     * validation rules before attempting validation. If this property
     * is not set in the model it will default to true.
     *
     * @var bool
     */

    protected $injectUniqueIdentifier = true;
    use ValidatingTrait;
    protected $table = 'models';
    protected $presenter = AssetModelPresenter::class;

    // Declare the rules for the model validation


    protected $rules = [
        'name'              => 'string|required|min:1|max:255|two_column_unique_undeleted:model_number',
        'model_number'      => 'string|max:255|nullable|two_column_unique_undeleted:name',
        'min_amt'           => 'integer|min:0|nullable',
        'category_id'       => 'required|integer|exists:categories,id',
        'manufacturer_id'   => 'integer|exists:manufacturers,id|nullable',
        'eol'               => 'integer:min:0|max:240|nullable',
    ];



    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'depreciation_id',
        'eol',
        'fieldset_id',
        'image',
        'manufacturer_id',
        'min_amt',
        'model_number',
        'name',
        'notes',
        'requestable',
        'require_serial'
    ];

    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = [
        'name',
        'model_number',
        'notes',
        'eol'
    ];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
        'depreciation' => ['name'],
        'category'     => ['name'],
        'manufacturer' => ['name'],
    ];

    protected static function booted(): void
    {
        static::forceDeleted(function (AssetModel $assetModel) {
            $assetModel->requests()->forceDelete();
        });

        static::softDeleted(function (AssetModel $assetModel) {
            $assetModel->requests()->delete();
        });
    }

    /**
     * Establishes the model -> assets relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v1.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function assets()
    {
        return $this->hasMany(\App\Models\Asset::class, 'model_id');
    }


    public function availableAssets()
    {
        return $this->hasMany(\App\Models\Asset::class, 'model_id')->RTD();
    }

    public function assignedAssets()
    {
        return $this->hasMany(\App\Models\Asset::class, 'model_id')->Deployed();
    }

    public function archivedAssets()
    {
        return $this->hasMany(\App\Models\Asset::class, 'model_id')->Archived();
    }

    /**
     * Establishes the model -> category relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v1.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class, 'category_id');
    }

    /**
     * Establishes the model -> depreciation relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v1.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function depreciation()
    {
        return $this->belongsTo(\App\Models\Depreciation::class, 'depreciation_id');
    }

    /**
     * Establishes the model -> manufacturer relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v1.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function manufacturer()
    {
        return $this->belongsTo(\App\Models\Manufacturer::class, 'manufacturer_id');
    }

    /**
     * Establishes the model -> fieldset relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function fieldset()
    {
        return $this->belongsTo(\App\Models\CustomFieldset::class, 'fieldset_id');
    }
   
    public function customFields()
    {
        return $this->fieldset()->first()->fields(); 
    }

    /**
     * Establishes the model -> custom field default values relationship
     *
     * @author hannah tinkler
     * @since  [v4.3]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function defaultValues()
    {
        return $this->belongsToMany(\App\Models\CustomField::class, 'models_custom_fields')->withPivot('default_value');
    }

    /**
     * Gets the full url for the image
     *
     * @todo this should probably be moved
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function getImageUrl()
    {
        if ($this->image) {
            return Storage::disk('public')->url(app('models_upload_path').$this->image);
        }

        return false;
    }


    /**
     * Checks if the model is deletable
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v6.3.4]
     * @return bool
     */
    public function isDeletable()
    {
        return Gate::allows('delete', $this)
            && ($this->assets_count == 0)
            && ($this->deleted_at == '');
    }


    /**
     * Get user who created the item
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v1.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function adminuser()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by')->withTrashed();
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
                        $query->where('models.name', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'notes') {
                        $query->where('models.notes', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'model_number') {
                        $query->where('models.model_number', 'LIKE', '%' . $search_val . '%');
                    }

                    if ($fieldname == 'category') {
                        $query->whereHas(
                            'category', function ($query) use ($search_val) {
                            $query->where('categories.name', 'LIKE', '%'.$search_val.'%');
                        }
                        );
                    }

                    if ($fieldname == 'manufacturer') {
                        $query->whereHas(
                            'manufacturer', function ($query) use ($search_val) {
                            $query->where('manufacturers.name', 'LIKE', '%'.$search_val.'%');
                        }
                        );
                    }



                }


            }
        );
    }

    /**
     * scopeInCategory
     * Get all models that are in the array of category ids
     *
     * @param $query
     * @param array $categoryIdListing
     *
     * @return  mixed
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     * @version v1.0
     */
    public function scopeInCategory($query, array $categoryIdListing)
    {
        return $query->whereIn('category_id', $categoryIdListing);
    }

    /**
     * scopeRequestable
     * Get all models that are requestable by a user.
     *
     * @param $query
     *
     * @return  $query
     * @author  Daniel Meltzer <dmeltzer.devel@gmail.com>
     * @version v3.5
     */
    public function scopeRequestableModels($query)
    {
        return $query->where('requestable', '1');
    }

    /**
     * Query builder scope to search on text, including catgeory and manufacturer name
     *
     * @param Illuminate\Database\Query\Builder $query  Query builder instance
     * @param text                              $search Search term
     *
     * @return Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeSearchByManufacturerOrCat($query, $search)
    {
        return $query->where('models.name', 'LIKE', "%$search%")
            ->orWhere('model_number', 'LIKE', "%$search%")
            ->orWhere(
                function ($query) use ($search) {
                    $query->whereHas(
                        'category', function ($query) use ($search) {
                            $query->where('categories.name', 'LIKE', '%'.$search.'%');
                        }
                    );
                }
            )
            ->orWhere(
                function ($query) use ($search) {
                    $query->whereHas(
                        'manufacturer', function ($query) use ($search) {
                            $query->where('manufacturers.name', 'LIKE', '%'.$search.'%');
                        }
                    );
                }
            );
    }

    /**
     * Query builder scope to order on manufacturer
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param text                               $order Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderManufacturer($query, $order)
    {
        return $query->leftJoin('manufacturers', 'models.manufacturer_id', '=', 'manufacturers.id')->orderBy('manufacturers.name', $order);
    }

    /**
     * Query builder scope to order on category name
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param text                               $order Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderCategory($query, $order)
    {
        return $query->leftJoin('categories', 'models.category_id', '=', 'categories.id')->orderBy('categories.name', $order);
    }

    public function scopeOrderFieldset($query, $order)
    {
        return $query->leftJoin('custom_fieldsets', 'models.fieldset_id', '=', 'custom_fieldsets.id')->orderBy('custom_fieldsets.name', $order);
    }

    /**
     * Query builder scope to order on created_by name
     */
    public function scopeOrderByCreatedByName($query, $order)
    {
        return $query->leftJoin('users as admin_sort', 'models.created_by', '=', 'admin_sort.id')->select('models.*')->orderBy('admin_sort.first_name', $order)->orderBy('admin_sort.last_name', $order);
    }

}
