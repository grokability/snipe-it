<?php

namespace App\Models;

use App\Enums\FileStorage;
use App\Http\Traits\TwoColumnUniqueUndeletedTrait;
use App\Models\Builders\AssetModelQueryBuilder;
use App\Models\Contracts\Importable;
use App\Models\Traits\HasImageUpload;
use App\Models\Traits\HasUploads;
use App\Models\Traits\Loggable;
use App\Models\Traits\Requestable;
use App\Models\Traits\Searchable;
use App\Presenters\AssetModelPresenter;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Watson\Validating\ValidatingTrait;

/**
 * Model for Asset Models. Asset Models contain higher level
 * attributes that are common among the same type of asset.
 *
 * @version v1.0
 *
 * @mixin IdeHelperAssetModel
 */
class AssetModel extends SnipeModel implements Importable
{
    use HasFactory;
    use HasImageUpload;
    use HasUploads;
    use Loggable, Presentable, Requestable;
    use SoftDeletes;
    use TwoColumnUniqueUndeletedTrait;

    public static function fileStorage(): FileStorage
    {
        return FileStorage::Models;
    }

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
        'name' => 'string|required|min:1|max:255|two_column_unique_undeleted:model_number',
        'model_number' => 'string|max:255|nullable|two_column_unique_undeleted:name',
        'min_amt' => 'integer|min:0|nullable',
        'category_id' => 'required|integer|exists:categories,id',
        'manufacturer_id' => 'integer|exists:manufacturers,id|nullable',
        'eol' => 'integer:min:0|max:240|nullable',
        'default_purchase_cost' => 'numeric|min:0|nullable',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'default_purchase_cost',
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
        'require_serial',
    ];

    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = [
        'created_at',
        'eol',
        'min_amt',
        'model_number',
        'name',
        'notes',
    ];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
        'depreciation' => ['name'],
        'category' => ['name'],
        'manufacturer' => ['name'],
        'fieldset' => ['name'],
        'adminuser' => ['first_name', 'last_name', 'display_name'],
    ];

    /**
     * Computed aliases (withCount/withSum) that can be searched via TextSearch filters.
     *
     * @var array
     */
    protected $searchableCounts = [
        'assets_count',
        'remaining',
        'assets_assigned_count',
        'assets_archived_count',
    ];

    protected static function booted(): void
    {
        static::forceDeleted(function (AssetModel $assetModel) {
            $assetModel->requests()->forceDelete();

            // Image + Files-tab attachments wipe on hard-delete only, so
            // a restored soft-deleted model keeps its image and files.
            // Attachment action_log rows get soft-deleted (not
            // hard-deleted) so the audit trail of what was attached-
            // and-when survives even after the parent is gone.
            if ($assetModel->image) {
                try {
                    Storage::disk('public')->delete(FileStorage::Models->publicPath().$assetModel->image);
                } catch (\Exception $e) {
                    Log::info($e->getMessage());
                }
            }

            foreach ($assetModel->uploads as $upload) {
                if (($path = $upload->uploads_file_path()) !== null) {
                    try {
                        Storage::delete($path);
                    } catch (\Exception $e) {
                        Log::info($e->getMessage());
                    }
                }
                $upload->delete();
            }
        });

        static::softDeleted(function (AssetModel $assetModel) {
            $assetModel->requests()->delete();
        });
    }

    /**
     * Establishes the model -> assets relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v1.0]
     *
     * @return Relation
     */
    public function assets()
    {
        return $this->hasMany(Asset::class, 'model_id');
    }

    public function availableAssets()
    {
        return $this->hasMany(Asset::class, 'model_id')->RTD();
    }

    /**
     * Bulk-fulfillment eligibility hook. Overrides the Requestable
     * trait's default (which probes numRemaining) since AssetModel
     * fulfills by handing out concrete assets of the model rather
     * than a qty count. Returns true when at least one available
     * (RTD) asset of this model exists.
     */
    protected function hasStockForBulkFulfillment(): bool
    {
        return $this->availableAssets()->exists();
    }

    public function assignedAssets()
    {
        return $this->hasMany(Asset::class, 'model_id')->Deployed();
    }

    /**
     * How many distinct Orders this asset model has appeared on across
     * all of its Asset instances. Since each Asset is 1:1 with a
     * transaction via AssetObserver::created, this is the count of
     * distinct order_ids on order_items linked to any of this model's
     * assets. Useful for the info-panel "how many times has this model
     * been ordered" hint.
     */
    public function ordersCount(): int
    {
        // Purchases only (positive qty) — see HasOrders::ordersCount for
        // the rationale on filtering out corrections/consumption events.
        return (int) OrderItem::query()
            ->where('item_type', Asset::class)
            ->whereIn('item_id', $this->assets()->select('id'))
            ->where('qty', '>', 0)
            ->distinct()
            ->count('order_id');
    }

    public function archivedAssets()
    {
        return $this->hasMany(Asset::class, 'model_id')->Archived();
    }

    public function percentRemaining()
    {
        // Prefer the pre-loaded withCount attributes — Api\AssetModelsController
        // ::index already eager-loads these as `remaining` (available count)
        // and `assets_count` (total) via correlated subqueries on the parent
        // models SELECT. Reading them here avoids re-running the same counts
        // as N+1 queries per model in the transformer loop. Read straight off
        // getAttributes() rather than via __get so we don't accidentally
        // trigger a relation load when the attribute isn't there.
        // Cast to int at read time: PDO with MySQL default emulated prepares
        // returns COUNT() values as string, and Eloquent doesn't auto-cast
        // withCount aliases (no $casts entry for `remaining` / `assets_count`).
        // A string "0" then slips past a strict === 0 guard AND — because PHP
        // 8's arithmetic operators auto-convert numeric strings — still hits
        // DivisionByZeroError at the ratio below.
        $raw = $this->getAttributes();
        $available = (int) ($raw['remaining'] ?? $this->availableAssets()->count());
        if ($available === 0) {
            return 0;
        }

        // Also guard the divisor. In principle available > 0 implies
        // total > 0 (available is a subset of total via the RTD scope), but
        // a data anomaly — an asset counted by availableAssets but not by
        // assets, or a race between the two correlated withCount subqueries
        // — has been observed in production. Return 0 rather than throw.
        $total = (int) ($raw['assets_count'] ?? $this->assets()->count());
        if ($total === 0) {
            return 0;
        }

        return $available / $total * 100;
    }

    /**
     * Establishes the model -> category relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v1.0]
     *
     * @return Relation
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function requireAcceptance(): bool
    {
        return (bool) ($this->category?->require_acceptance ?? false);
    }

    /**
     * Establishes the model -> depreciation relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v1.0]
     *
     * @return Relation
     */
    public function depreciation()
    {
        return $this->belongsTo(Depreciation::class, 'depreciation_id');
    }

    /**
     * Establishes the model -> manufacturer relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v1.0]
     *
     * @return Relation
     */
    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class, 'manufacturer_id');
    }

    /**
     * Establishes the model -> fieldset relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v2.0]
     *
     * @return Relation
     */
    public function fieldset()
    {
        return $this->belongsTo(CustomFieldset::class, 'fieldset_id');
    }

    public function customFields()
    {
        return $this->fieldset()->first()->fields();
    }

    /**
     * Establishes the model -> custom field default values relationship
     *
     * @author hannah tinkler
     *
     * @since  [v4.3]
     *
     * @return Relation
     */
    public function defaultValues()
    {
        return $this->belongsToMany(CustomField::class, 'models_custom_fields')->withPivot('default_value');
    }

    /**
     * Gets the full url for the image
     *
     * @todo this should probably be moved
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v2.0]
     *
     * @return Relation
     */
    public function getImageUrl($path = null)
    {
        if ($this->image) {
            return Storage::disk('public')->url(FileStorage::Models->publicPath().$this->image);
        }

        return false;
    }

    /**
     * Checks if the model is deletable
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v6.3.4]
     *
     * @return bool
     */
    public function isDeletable()
    {
        return Gate::allows('delete', $this)
            && ((int) ($this->assets_count ?? $this->assets()->count()) === 0)
            && ($this->deleted_at == '');
    }

    public function newEloquentBuilder($query): AssetModelQueryBuilder
    {
        return new AssetModelQueryBuilder($query);
    }
}
