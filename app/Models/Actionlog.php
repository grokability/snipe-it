<?php

namespace App\Models;

use App\Enums\ActionType;
use App\Enums\FileStorage;
use App\Models\Builders\ActionlogQueryBuilder;
use App\Models\Traits\CompanyableTrait;
use App\Models\Traits\Searchable;
use App\Presenters\ActionlogPresenter;
use App\Presenters\Presentable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Model for the Actionlog (the table that keeps a historical log of
 * checkouts, checkins, and updates).
 *
 * @version v1.0
 *
 * @mixin IdeHelperActionlog
 */
class Actionlog extends SnipeModel
{
    use CompanyableTrait;
    use HasFactory;

    // This is to manually set the source (via setActionSource()) for determineActionSource()
    protected ?string $source = null;

    /**
     * Ambient action-source for every action_log written inside a
     * long-running operation (LDAP sync, sync-adapter pulls) that has
     * no HTTP context to infer `gui` / `api` from and that spawns many
     * observer-driven writes we can't reach into individually.
     *
     * Set + restored around a callback via withActionSource(). The
     * priority order in determineActionSource() is: explicit per-log
     * $this->source first, ambient second, then the request-shape
     * fallbacks.
     */
    protected static ?string $ambientSource = null;

    protected $with = ['adminuser'];

    protected $presenter = ActionlogPresenter::class;

    use Presentable;
    use SoftDeletes;

    protected $table = 'action_logs';

    public $timestamps = true;

    protected $fillable = [
        'created_at',
        'item_type',
        'created_by',
        'item_id',
        'action_type',
        'note',
        'order_item_id',
        'target_id',
        'target_type',
        'stored_eula',
    ];

    use Searchable;

    /**
     * Cache whether a model table has a company_id column.
     *
     * @var array<string, bool>
     */
    protected static array $companyColumnCache = [];

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    /*
     * 'log_meta' is intentionally not listed here. The column IS text-
     * searchable for every non-License row via advancedTextSearch() below,
     * which carries an explicit `item_type != License::class` guard. On
     * License rows log_meta carries the raw product-key diff and must
     * never be a search target, since the result-count differential
     * would otherwise be a timing oracle on the masked serial.
     * GHSA-68gh-qwvc-qccg.
     */
    protected $searchableAttributes = [
        'action_type',
        'note',
        'created_by',
        'remote_ip',
        'user_agent',
        'item_type',
        'target_type',
        'action_source',
        'created_at',
        'action_date',
    ];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
        'company' => ['name'],
        'location' => ['name'],
        'adminuser' => ['first_name', 'last_name', 'username', 'email', 'employee_num'],
        'user' => ['first_name', 'last_name', 'username', 'email', 'employee_num'],
        // Free-text search on QuantityAdjust logs walks through the
        // OrderItem line to its parent Order so an order-number
        // string still finds the right log rows after the parent
        // action_logs.order_number column moved to Orders.
        'orderItem.order' => ['order_number'],
        'assets' => ['asset_tag', 'name', 'serial', 'notes', 'purchase_date'],
        'assets.model' => ['name', 'model_number', 'eol', 'notes'],
        'assets.model.category' => ['name', 'notes'],
        'assets.location' => ['name'],
        'assets.defaultLoc' => ['name'],
        'assets.model.manufacturer' => ['name', 'notes'],
        // 'serial' intentionally omitted: the activity-report search would
        // otherwise be a timing / result-count oracle on the real product
        // key for callers without licenses.keys. GHSA-68gh-qwvc-qccg.
        'licenses' => ['name', 'notes', 'license_email', 'license_name', 'purchase_order', 'purchase_date'],
        'licenses.category' => ['name', 'notes'],
        'licenses.supplier' => ['name'],
        // consumables / components / accessories no longer expose a
        // supplier() or purchase_date accessor on the parent — those
        // moved to the Orders / OrderItems polymorphic data model per
        // acquisition event. The "default_supplier" template lives on
        // defaultSupplier() and is safe to walk for search.
        'consumables' => ['name', 'notes', 'model_number', 'item_no'],
        'consumables.category' => ['name', 'notes'],
        'consumables.location' => ['name', 'notes'],
        'consumables.defaultSupplier' => ['name', 'notes'],
        'components' => ['name', 'notes'],
        'components.category' => ['name', 'notes'],
        'components.location' => ['name', 'notes'],
        'components.defaultSupplier' => ['name', 'notes'],
        'accessories' => ['name'],
        'accessories.category' => ['name'],
        'accessories.location' => ['name', 'notes'],
        'accessories.defaultSupplier' => ['name', 'notes'],
    ];

    /**
     * Override from Builder to automatically add the company
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public static function boot()
    {
        parent::boot();
        static::creating(function (self $actionlog): void {
            // Only resolve company_id if it was never explicitly set by the caller.
            // Using array_key_exists on getRawOriginal() / getAttributes() lets us
            // distinguish "was set to null intentionally" from "was never set at all".
            if (! array_key_exists('company_id', $actionlog->getAttributes())) {
                $actionlog->company_id = static::resolveCompanyIdFromAttributes(
                    $actionlog->target_type,
                    $actionlog->target_id,
                    $actionlog->item_type,
                    $actionlog->item_id,
                );
            }

            if ($actionlog->action_date == '') {
                $actionlog->action_date = Carbon::now();
            }
        });
    }

    /**
     * Resolve the company_id for a new action log by querying the item model
     * directly, bypassing all global scopes to avoid FMCS filtering issues.
     *
     * We intentionally prefer the item (asset, license, etc.) over the target
     * (user, location) because FMCS visibility is based on who *owns* the item,
     * not who it was checked out to.  If the item has no company_id we fall back
     * to the target so that logs on unowned items still get a company stamp where
     * possible.
     *
     * This has to include an exception for the asset models table, since they are
     * not company-constrained (on purpose.)
     */
    protected static function resolveCompanyIdFromAttributes(
        ?string $targetType,
        ?int $targetId,
        ?string $itemType,
        ?int $itemId,
    ): ?int {
        // Prefer the item (the thing being acted upon) for FMCS ownership.
        $companyId = static::resolveCompanyIdFromModelClass($itemType, $itemId);

        if ($companyId !== null) {
            return $companyId;
        }

        // Fall back to target only when the item has no company_id.
        return static::resolveCompanyIdFromModelClass($targetType, $targetId);

    }

    /**
     * Resolve company_id from a model class and ID, but only if that model's
     * table has a company_id column.
     */
    protected static function resolveCompanyIdFromModelClass(?string $modelClass, ?int $id): ?int
    {
        if (! $modelClass || ! $id || ! class_exists($modelClass) || ! is_subclass_of($modelClass, Model::class)) {
            return null;
        }

        /** @var Model $instance */
        $instance = app($modelClass);
        $table = $instance->getTable();

        $hasCompanyColumn = static::$companyColumnCache[$table]
            ??= Schema::hasColumn($table, 'company_id');

        if (! $hasCompanyColumn) {
            return null;
        }

        return $modelClass::withoutGlobalScopes()
            ->whereKey($id)
            ->value('company_id');

    }

    /**
     * Establishes the actionlog -> item relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function item()
    {
        return $this->morphTo('item')->withTrashed();
    }

    /**
     * Establishes the actionlog -> company relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function company()
    {
        return $this->hasMany(Company::class, 'id', 'company_id');
    }

    /**
     * Establishes the actionlog -> asset relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function assets()
    {
        return $this->hasMany(Asset::class, 'id', 'item_id');
    }

    /**
     * Establishes the actionlog -> license relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function licenses()
    {
        return $this->hasMany(License::class, 'id', 'item_id');
    }

    /**
     * Establishes the actionlog -> consumable relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function consumables()
    {
        return $this->hasMany(Consumable::class, 'id', 'item_id');
    }

    /**
     * Establishes the actionlog -> consumable relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function accessories()
    {
        return $this->hasMany(Accessory::class, 'id', 'item_id');
    }

    /**
     * Establishes the actionlog -> components relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function components()
    {
        return $this->hasMany(Component::class, 'id', 'item_id');
    }

    /**
     * Establishes the actionlog -> item type relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function itemType()
    {
        if ($this->item_type == AssetModel::class) {
            return 'model';
        }

        return camel_case(class_basename($this->item_type));
    }

    /**
     * Establishes the actionlog -> target type relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function targetType()
    {
        if ($this->target_type == User::class) {
            return 'user';
        }

        return camel_case(class_basename($this->target_type));
    }

    /**
     * Establishes the actionlog -> userlog relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function userlog()
    {
        return $this->target();
    }

    /**
     * Establishes the actionlog -> admin user relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function adminuser()
    {
        return $this->belongsTo(User::class, 'created_by')
            ->withTrashed();
    }

    /**
     * QuantityAdjust log rows carry the specific OrderItem line this
     * replenishment produced via the order_item_id column. The parent
     * Order (with order_number, purchase_order, supplier, currency,
     * purchase_date) is reachable via `$log->orderItem->order`.
     *
     * Points at order_items rather than orders because a single Order
     * deduped across staggered receipts carries multiple lines, and the
     * log entry has to identify the exact line for its event.
     */
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    /**
     * Establishes the actionlog -> user relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'target_id')
            ->withTrashed();
    }

    /**
     * Establishes the actionlog -> target relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function target()
    {
        return $this->morphTo('target')->withTrashed();
    }

    /**
     * Extend the Searchable trait to also LIKE-search across the
     * polymorphic target column. The trait's built-in
     * searchRelations walks $searchableRelations, all of which are
     * keyed on item_id (Actionlog::assets, ::users, ::licenses etc.
     * hasMany the related model where id = item_id). That misses
     * every log where the row's TARGET carries the identifier the
     * caller is searching for, notably component checkouts whose
     * item is the Component and whose target is the Asset it was
     * checked out to.
     *
     * Mirrors the trait's searchAssignedToRelation helper: iterate
     * the same three assignee morph types (User, Asset, Location)
     * and LIKE across each type's identifier columns.
     */
    public function advancedTextSearch(Builder $outer, Builder $nested, array $terms): void
    {
        // log_meta is a JSON diff blob that admins legitimately search
        // across (who changed status to Lost, when the seat count moved),
        // so dropping it from search entirely is too destructive. On
        // License rows the same column carries the raw product-key diff
        // though, so matching log_meta there turns the result count into
        // a timing oracle on the masked serial. Scope the LIKE to
        // item_type != License to keep the admin workflows while shutting
        // the oracle. GHSA-68gh-qwvc-qccg.
        $nested->orWhere(function (Builder $metaQuery) use ($terms): void {
            $metaQuery->where('action_logs.item_type', '!=', License::class)
                ->where(function (Builder $likeQuery) use ($terms): void {
                    foreach ($terms as $term) {
                        $likeQuery->orWhere('action_logs.log_meta', 'LIKE', '%'.$term.'%');
                    }
                });
        });

        $nested->orWhereHasMorph(
            'target',
            [User::class, Asset::class, Location::class],
            function (Builder $targetQuery, string $targetType) use ($terms) {
                $columns = match ($targetType) {
                    User::class => ['first_name', 'last_name', 'username', 'email', 'employee_num'],
                    Asset::class => ['asset_tag', 'name', 'serial'],
                    Location::class => ['name'],
                    default => [],
                };

                if (empty($columns)) {
                    return;
                }

                $table = (new $targetType)->getTable();
                $firstConditionAdded = false;

                foreach ($columns as $column) {
                    foreach ($terms as $term) {
                        if (! $firstConditionAdded) {
                            $targetQuery->where($table.'.'.$column, 'LIKE', '%'.$term.'%');
                            $firstConditionAdded = true;

                            continue;
                        }

                        $targetQuery->orWhere($table.'.'.$column, 'LIKE', '%'.$term.'%');
                    }
                }

                // First+last concat so "John Smith" matches a
                // targeted user split across the two columns, same
                // treatment the trait applies for adminuser / user
                // relations and searchAssignedToRelation.
                if ($targetType === User::class) {
                    foreach ($terms as $term) {
                        $targetQuery->orWhereRaw(
                            $this->buildMultipleColumnSearch(['users.first_name', 'users.last_name']),
                            ["%{$term}%"]
                        );
                    }
                }
            }
        );
    }

    /**
     * Establishes the actionlog -> location relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id')->withTrashed();
    }

    /**
     * Check if the file exists, and if it does, force a download
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return string | false
     */
    public function get_src($type = 'assets', $fieldname = 'filename')
    {
        if ($this->filename != '') {
            $file = config('app.private_uploads').'/'.$type.'/'.$this->{$fieldname};

            return $file;
        }

        return false;
    }

    /**
     * Saves the log record with the action type
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return bool
     */
    public function logaction(string|ActionType $actiontype)
    {
        if (is_string($actiontype)) {
            $actiontype = ActionType::from($actiontype);
        }
        $this->action_type = $actiontype->value;
        $this->remote_ip = request()->ip();
        $this->user_agent = request()->header('User-Agent');
        $this->action_source = $this->determineActionSource();

        if ($this->save()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Calculate the number of days until the next audit
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v4.0]
     *
     * @return int
     */
    public function daysUntilNextAudit($monthInterval = 12, $asset = null)
    {
        $now = Carbon::now();
        $last_audit_date = $this->created_at; // this is the action log's created at, not the asset itself
        $next_audit = $last_audit_date->addMonth((int) $monthInterval); // this actually *modifies* the $last_audit_date
        $next_audit_days = (int) round($now->diffInDays($next_audit, true));
        $override_default_next = $next_audit;

        // Override the default setting for interval if the asset has its own next audit date
        if (($asset) && ($asset->next_audit_date)) {
            $override_default_next = Carbon::parse($asset->next_audit_date);
            $next_audit_days = (int) round($override_default_next->diffInDays($now, true));
        }

        // Show as negative number if the next audit date is before the audit date we're looking at
        if ($this->created_at->toDateString() > $override_default_next->toDateString()) {
            $next_audit_days = '-'.$next_audit_days;
        }

        return $next_audit_days;
    }

    /**
     * Calculate the date of the next audit
     *
     * @return Datetime | string
     *
     * @since  [v4.0]
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     */
    public function calcNextAuditDate($monthInterval = 12, $asset = null)
    {
        $last_audit_date = Carbon::parse($this->created_at);
        // If there is an asset-specific next date already given,
        if (($asset) && ($asset->next_audit_date)) {
            return Carbon::parse($asset->next_audit_date);
        }

        return Carbon::parse($last_audit_date)->addMonths($monthInterval)->toDateString();
    }

    /**
     * Determines what the type of request is so we can log it to the action_log
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  v6.3.0
     */
    /**
     * Run $callback with every action_log it writes stamped as $source.
     * Previous ambient value is restored on exit (including on
     * exception) so nested wraps don't leak out of their scope. Used by
     * LdapSync + the sync-adapter pull entry points to attribute
     * observer-driven writes to the owning operation.
     */
    public static function withActionSource(string $source, callable $callback): mixed
    {
        $previous = self::$ambientSource;
        self::$ambientSource = $source;
        try {
            return $callback();
        } finally {
            self::$ambientSource = $previous;
        }
    }

    public function determineActionSource(): string
    {
        // Explicit per-log override, set via setActionSource() on the
        // Actionlog instance before save.
        if ($this->source) {
            return $this->source;
        }

        // Ambient source set by a surrounding withActionSource() wrap.
        // Catches observer-driven writes from LDAP sync and sync-adapter
        // pulls that we can't reach to tag individually.
        if (self::$ambientSource !== null) {
            return self::$ambientSource;
        }

        // This is an API call
        if (((request()->header('content-type') && (request()->header('accept')) == 'application/json'))
            && (starts_with(request()->header('authorization'), 'Bearer '))
        ) {
            return 'api';
        }

        // This is probably NOT an API call
        if (request()->filled('_token')) {
            return 'gui';
        }

        // We're not sure, probably cli
        return 'cli/unknown';

    }

    /**
     * @author  Godfrey Martinez
     *
     * @since [v8.0.4]
     *
     * @return Actionlog
     */
    public function logUploadDelete($object, $filename)
    {
        $log = new Actionlog;
        $log->item_type = $object instanceof SnipeModel ? get_class($object) : $object;
        $log->item_id = $object->id;
        $log->created_by = auth()->id();
        $log->target_id = null;
        $log->filename = $filename;
        $log->created_at = date('Y-m-d H:i:s');
        $log->logaction('upload deleted');

        return $log;
    }

    public function uploads_file_url()
    {

        if (($this->action_type == 'accepted') || ($this->action_type == 'declined')) {
            return route('log.storedeula.download', ['filename' => $this->filename]);
        }

        $object = Str::snake(str_plural(str_replace("App\Models\\", '', $this->item_type)));

        if ($object == 'asset_models') {
            $object = 'models';
        }

        if ($this->action_type == 'audit') {
            $object = 'audits';
        }

        return route('ui.files.show', [
            'object_type' => $object,
            'id' => $this->item_id,
            'file_id' => $this->id,
        ]);

    }

    public function uploads_file_path()
    {

        if (($this->action_type == 'accepted') || ($this->action_type == 'declined')) {
            return FileStorage::EulaPdfs->privateStorageKey().$this->filename;
        }

        if ($this->action_type == 'audit') {
            return FileStorage::Audits->privateStorageKey().$this->filename;
        }

        $case = match ($this->item_type) {
            Accessory::class => FileStorage::Accessories,
            Asset::class => FileStorage::Assets,
            AssetModel::class => FileStorage::Models,
            Company::class => FileStorage::Companies,
            Consumable::class => FileStorage::Consumables,
            Department::class => FileStorage::Departments,
            Component::class => FileStorage::Components,
            License::class => FileStorage::Licenses,
            Location::class => FileStorage::Locations,
            Maintenance::class => FileStorage::Maintenances,
            Supplier::class => FileStorage::Suppliers,
            User::class => FileStorage::Users,
            default => null,
        };

        return $case ? $case->privateStorageKey().$this->filename : null;
    }

    // Manually sets $this->source for determineActionSource()
    public function setActionSource($source = null): void
    {
        $this->source = $source;
    }

    public function newEloquentBuilder($query): ActionlogQueryBuilder
    {
        return new ActionlogQueryBuilder($query);
    }
}
