<?php

namespace App\Models;

use App\Enums\FileStorage;
use App\Http\Traits\UniqueUndeletedTrait;
use App\Models\Builders\SupplierQueryBuilder;
use App\Models\Contracts\Importable;
use App\Models\Traits\HasUploads;
use App\Models\Traits\Loggable;
use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use App\Presenters\SupplierPresenter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Watson\Validating\ValidatingTrait;

/**
 * @mixin IdeHelperSupplier
 */
class Supplier extends SnipeModel implements Importable
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
        'notes' => 'max:16383|nullable', // text is 65535 but each character can take up to 4 bytes in utf8mb4
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
     * Advanced-search filterable fields. Suppliers have no company_id or
     * other model-level FKs beyond created_by.
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
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['name', 'address', 'address2', 'city', 'state', 'country', 'zip', 'phone', 'fax', 'email', 'contact', 'url', 'tag_color', 'notes'];

    public function isDeletable()
    {
        // accessories / consumables / components no longer block delete:
        // their `default_supplier_id` reference is a soft template that
        // the deleting hook below nulls out on delete. assets, licenses,
        // and maintenances still use a per-record `supplier_id` column
        // (out of scope for the Orders refactor) — those remain hard
        // blockers to avoid stranding acquisition records mid-lifecycle.
        return Gate::allows('delete', $this)
            && (($this->assets_count ?? $this->assets()->count()) === 0)
            && (($this->licenses_count ?? $this->licenses()->count()) === 0)
            && (($this->maintenances_count ?? $this->maintenances()->count()) === 0)
            && ($this->deleted_at == '');
    }

    /**
     * Whether the sidebar info-panel disclosure for this supplier has
     * anything to reveal. Gates the "+" button so rows with only a name
     * don't offer an empty expander.
     */
    public function hasInfoPanelDetails(): bool
    {
        return (bool) ($this->contact
            || $this->phone
            || $this->email
            || $this->url
            || trim($this->present()->displayAddress()));
    }

    /**
     * On delete (soft or force), null out the `default_supplier_id`
     * pointers on any accessory / consumable / component that used this
     * supplier as its "typical supplier" template. Historical Orders
     * keep their `supplier_id` untouched — that's an acquisition record
     * of what actually happened, and the belongsTo relation returns null
     * transparently when the referenced Supplier is soft-deleted anyway.
     */
    protected static function booted(): void
    {
        static::deleting(function (self $supplier) {
            Accessory::where('default_supplier_id', $supplier->id)->update(['default_supplier_id' => null]);
            Consumable::where('default_supplier_id', $supplier->id)->update(['default_supplier_id' => null]);
            Component::where('default_supplier_id', $supplier->id)->update(['default_supplier_id' => null]);
        });

        // On hard-delete, wipe the image file and Files-tab attachments.
        // Soft-delete leaves everything alone so a restore comes back
        // with the image + files intact. The attachment action_log rows
        // get soft-deleted (not hard-deleted) so the audit trail of what
        // was attached-and-when survives even after the parent is gone.
        static::forceDeleted(function (self $supplier) {
            if ($supplier->image) {
                try {
                    Storage::disk('public')->delete(FileStorage::Suppliers->publicPath().$supplier->image);
                } catch (\Exception $e) {
                    Log::info($e->getMessage());
                }
            }

            foreach ($supplier->uploads as $upload) {
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
     * Accessories that use this supplier as their default template.
     * Post-Orders, "supplier of an accessory" is a per-transaction fact
     * (on Order.supplier_id); this relation reads the parent-level
     * `default_supplier_id` template instead — the field the show page
     * "N items from this supplier" counts and tabs actually reflect.
     */
    public function accessories()
    {
        return $this->hasMany(Accessory::class, 'default_supplier_id');
    }

    /**
     * Components that use this supplier as their default template.
     * See accessories() for rationale.
     */
    public function components()
    {
        return $this->hasMany(Component::class, 'default_supplier_id');
    }

    /**
     * Consumables that use this supplier as their default template.
     * See accessories() for rationale.
     */
    public function consumables()
    {
        return $this->hasMany(Consumable::class, 'default_supplier_id');
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

    public function newEloquentBuilder($query): SupplierQueryBuilder
    {
        return new SupplierQueryBuilder($query);
    }
}
