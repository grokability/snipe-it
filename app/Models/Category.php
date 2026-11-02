<?php

namespace App\Models;

use App\Enums\FileStorage;
use App\Http\Traits\TwoColumnUniqueUndeletedTrait;
use App\Models\Builders\CategoryQueryBuilder;
use App\Models\Contracts\Importable;
use App\Models\Traits\Searchable;
use App\Presenters\CategoryPresenter;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Watson\Validating\ValidatingTrait;

/**
 * Model for Categories. Categories are a higher-level group
 * than Asset Models, and handle things like whether or not
 * to require acceptance from the user, whether or not to
 * send a EULA to the user, etc.
 *
 * @version v1.0
 *
 * @mixin IdeHelperCategory
 */
class Category extends SnipeModel implements Importable
{
    use HasFactory;

    protected $presenter = CategoryPresenter::class;

    use Presentable;
    use SoftDeletes;

    protected $table = 'categories';

    protected $hidden = ['created_by', 'deleted_at'];

    protected $casts = [
        'alert_on_response' => 'boolean',
        'created_by' => 'integer',
    ];

    /**
     * Category validation rules
     */
    public $rules = [
        'created_by' => 'numeric|nullable',
        'name' => 'required|min:1|max:255|two_column_unique_undeleted:category_type',
        'require_acceptance' => 'boolean',
        'use_default_eula' => 'boolean',
        'category_type' => 'required|in:asset,accessory,consumable,component,license',
    ];

    /**
     * Whether the model should inject it's identifier to the unique
     * validation rules before attempting validation. If this property
     * is not set in the model it will default to true.
     *
     * @var bool
     */
    protected $injectUniqueIdentifier = true;

    use TwoColumnUniqueUndeletedTrait;
    use ValidatingTrait;

    /**
     * The attributes that are mass-assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_type',
        'checkin_email',
        'eula_text',
        'name',
        'require_acceptance',
        'alert_on_response',
        'use_default_eula',
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
        'category_type',
        'notes',
        'eula_text',
        'created_at',
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
                'field' => 'category_type',
                'type' => 'enum',
                'options' => [
                    ['value' => 'asset', 'label' => trans('general.asset')],
                    ['value' => 'accessory', 'label' => trans('general.accessory')],
                    ['value' => 'consumable', 'label' => trans('general.consumable')],
                    ['value' => 'component', 'label' => trans('general.component')],
                    ['value' => 'license', 'label' => trans('general.license')],
                ],
                'label' => trans('admin/categories/general.import_category_type'),
            ],
            [
                'field' => 'require_acceptance',
                'type' => 'boolean',
                'label' => trans('admin/categories/table.require_acceptance'),
            ],
            [
                'field' => 'use_default_eula',
                'type' => 'boolean',
                'label' => trans('admin/settings/general.default_eula_text'),
            ],
            [
                'field' => 'checkin_email',
                'type' => 'boolean',
                'label' => trans('general.send_email'),
            ],
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
            [
                'field' => 'item_count',
                'type' => 'numeric',
                'label' => trans('general.qty'),
            ],
        ];
    }

    /**
     * Override hook from Searchable. The displayed "qty" column
     * (item_count) is derived per row from the category_type and the
     * matching relation (assets via hasManyThrough models, everything
     * else via direct hasMany on category_id). Filtering has to use
     * the same CASE-dispatched correlated subquery so the gt/lt
     * bounds match what the user sees.
     */
    protected function applyCustomFilter(\Illuminate\Database\Eloquent\Builder $query, string $filterKey, string $filterValue, string $boolean = 'and'): ?\Illuminate\Database\Eloquent\Builder
    {
        if ($filterKey !== 'item_count') {
            return null;
        }

        $bounds = $this->parseGtLtBounds($filterValue);
        if ($bounds === null) {
            return null;
        }
        [$gt, $lt] = $bounds;

        $countExpr = "(CASE categories.category_type
            WHEN 'asset' THEN (SELECT COUNT(*) FROM models INNER JOIN assets ON assets.model_id = models.id WHERE models.category_id = categories.id AND assets.deleted_at IS NULL)
            WHEN 'accessory' THEN (SELECT COUNT(*) FROM accessories WHERE accessories.category_id = categories.id AND accessories.deleted_at IS NULL)
            WHEN 'consumable' THEN (SELECT COUNT(*) FROM consumables WHERE consumables.category_id = categories.id AND consumables.deleted_at IS NULL)
            WHEN 'component' THEN (SELECT COUNT(*) FROM components WHERE components.category_id = categories.id AND components.deleted_at IS NULL)
            WHEN 'license' THEN (SELECT COUNT(*) FROM licenses WHERE licenses.category_id = categories.id AND licenses.deleted_at IS NULL)
            ELSE 0
        END)";

        $outerBoolean = $boolean === 'or' ? 'orWhere' : 'where';
        $query->{$outerBoolean}(function ($nested) use ($countExpr, $gt, $lt) {
            if ($gt !== null) {
                $nested->whereRaw("{$countExpr} > ?", [$gt]);
            }
            if ($lt !== null) {
                $nested->whereRaw("{$countExpr} < ?", [$lt]);
            }
        });

        return $query;
    }

    protected $searchableCounts = [
        'accessories_count',
        'consumables_count',
        'components_count',
        'licenses_count',
        'models_count',
    ];

    /**
     * On hard-delete, wipe the image file. Soft-delete leaves it alone so a
     * subsequent restore comes back with its image intact.
     */
    protected static function booted(): void
    {
        static::forceDeleted(function (self $category) {
            if ($category->image) {
                try {
                    Storage::disk('public')->delete(FileStorage::Categories->publicPath().$category->image);
                } catch (\Exception $e) {
                    Log::info($e->getMessage());
                }
            }
        });
    }

    /**
     * Checks if category can be deleted
     *
     * @author [Dan Meltzer] [<dmeltzer.devel@gmail.com>]
     *
     * @since  [v5.0]
     *
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
     *
     * @since  [v2.0]
     *
     * @return Relation
     */
    public function accessories()
    {
        return $this->hasMany(Accessory::class);
    }

    /**
     * Establishes the category -> licenses relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v4.3]
     *
     * @return Relation
     */
    public function licenses()
    {
        return $this->hasMany(License::class);
    }

    /**
     * Establishes the category -> consumables relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function consumables()
    {
        return $this->hasMany(Consumable::class);
    }

    /**
     * Establishes the category -> consumables relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function components()
    {
        return $this->hasMany(Component::class);
    }

    /**
     * Get the number of items in the category. This should NEVER be used in
     * a collection of categories, as you'll end up with an n+1 query problem.
     *
     * It should only be used in a single category context.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v2.0]
     *
     * @return int
     */
    public function itemCount()
    {

        if (isset($this->{Str::plural($this->category_type).'_count'})) {
            return $this->{Str::plural($this->category_type).'_count'};
        }

        // Use the relation method (->assets()) instead of property access
        // (->assets) — the property form hydrates the entire collection
        // just to read its size. On an asset category with thousands of
        // rows that's thousands of model instances allocated for a single
        // count comparison (visible as huge "Retrieved Models" totals in
        // Debugbar). Method-style emits a single SELECT count(*).
        switch ($this->category_type) {
            case 'asset':
                return $this->assets()->count();
            case 'accessory':
                return $this->accessories()->count();
            case 'component':
                return $this->components()->count();
            case 'consumable':
                return $this->consumables()->count();
            case 'license':
                return $this->licenses()->count();
            default:
                return 0;
        }

    }

    /**
     * Establishes the category -> assets relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v2.0]
     *
     * @return Relation
     */
    public function assets()
    {
        return $this->hasManyThrough(Asset::class, AssetModel::class, 'category_id', 'model_id');
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
     *
     * @since  [v6.1.0]
     * @see    Asset::scopeAssetsForShow()
     *
     * @return Relation
     */
    public function showableAssets()
    {
        return $this->hasManyThrough(Asset::class, AssetModel::class, 'category_id', 'model_id')->AssetsForShow();
    }

    /**
     * Establishes the category -> models relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v2.0]
     *
     * @return Relation
     */
    public function models()
    {
        return $this->hasMany(AssetModel::class, 'category_id');
    }

    /**
     * Checks for a category-specific EULA, and if that doesn't exist,
     * checks for a settings level EULA
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v2.0]
     *
     * @return string | null
     */
    public function getEula()
    {
        // Route through SnipeModel::sanitizeEulaForRender (protected on
        // the parent) so this override matches SnipeModel::getEula's
        // rendered-output shape: parseEscapedMarkedown + <img> tag strip.
        // Without the strip, callsites that render this method's output
        // (users/print.blade.php, etc.) would emit <img> tags the parent
        // path deliberately blocks per the mail-context LFR/SSRF hardening.
        if ($this->eula_text) {
            return $this->sanitizeEulaForRender($this->eula_text);
        } elseif ((Setting::getSettings()->default_eula_text) && ($this->use_default_eula == '1')) {
            return $this->sanitizeEulaForRender(Setting::getSettings()->default_eula_text);
        }

        return null;
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
     * @return void
     */
    public function setCheckinEmailAttribute($value)
    {
        $this->attributes['checkin_email'] = (int) filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function newEloquentBuilder($query): CategoryQueryBuilder
    {
        return new CategoryQueryBuilder($query);
    }
}
