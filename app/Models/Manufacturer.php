<?php

namespace App\Models;

use App\Enums\FileStorage;
use App\Models\Builders\ManufacturerQueryBuilder;
use App\Models\Contracts\Importable;
use App\Models\Traits\Searchable;
use App\Presenters\ManufacturerPresenter;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Watson\Validating\ValidatingTrait;

/**
 * @mixin IdeHelperManufacturer
 */
class Manufacturer extends SnipeModel implements Importable
{
    use HasFactory;

    protected $presenter = ManufacturerPresenter::class;

    use Presentable;
    use SoftDeletes;

    protected $table = 'manufacturers';

    // Declare the rules for the form validation
    protected $rules = [
        'name' => 'required|max:255|unique:manufacturers,name,NULL,id,deleted_at,NULL',
        'url' => 'nullable|starts_with:http://,https://,afp://,facetime://,file://,irc://',
        'support_email' => 'email|nullable',
        'support_url' => 'nullable|starts_with:http://,https://,afp://,facetime://,file://,irc://',
        'warranty_lookup_url' => 'nullable|starts_with:http://,https://,afp://,facetime://,file://,irc://',
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
     * @var list<string>
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
    protected $searchableAttributes = [
        'name',
        'created_at',
        'notes',
    ];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
        'adminuser' => ['first_name', 'last_name', 'display_name'],
    ];

    /**
     * Advanced-search filterable fields.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function filterableFields(): array
    {
        return [
            [
                'field' => 'created_by',
                'type' => 'fk',
                'column' => 'created_by',
                'endpoint' => 'users',
                'label' => trans('general.created_by'),
            ],
            [
                'field' => 'created_at',
                'type' => 'date-range',
                'label' => trans('general.created_at'),
            ],
            [
                'field' => 'updated_at',
                'type' => 'date-range',
                'label' => trans('general.updated_at'),
            ],
        ];
    }

    /**
     * On hard-delete, wipe the image file. Soft-delete leaves it alone so a
     * subsequent restore comes back with its image intact.
     */
    protected static function booted(): void
    {
        static::forceDeleted(function (self $manufacturer) {
            if ($manufacturer->image) {
                try {
                    Storage::disk('public')->delete(FileStorage::Manufacturers->publicPath().$manufacturer->image);
                } catch (\Exception $e) {
                    Log::info($e->getMessage());
                }
            }
        });
    }

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
        return $this->hasManyThrough(Asset::class, AssetModel::class, 'manufacturer_id', 'model_id');
    }

    public function models()
    {
        return $this->hasMany(AssetModel::class, 'manufacturer_id');
    }

    public function licenses()
    {
        return $this->hasMany(License::class, 'manufacturer_id');
    }

    public function accessories()
    {
        return $this->hasMany(Accessory::class, 'manufacturer_id');
    }

    public function consumables()
    {
        return $this->hasMany(Consumable::class, 'manufacturer_id');
    }

    public function components()
    {
        return $this->hasMany(Component::class, 'manufacturer_id');
    }

    public function newEloquentBuilder($query): ManufacturerQueryBuilder
    {
        return new ManufacturerQueryBuilder($query);
    }
}
