<?php

namespace App\Models;

use App\Models\Traits\CompanyableTrait;
use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Model for the Actionlog (the table that keeps a historical log of
 * checkouts, checkins, and updates).
 *
 * @version v1.0
 */
class Actionlog extends SnipeModel
{
    use CompanyableTrait;
    use HasFactory;

    // This is to manually set the source (via setActionSource()) for determineActionSource()
    protected ?string $source = null;
    protected $with = ['adminuser'];

    protected $presenter = \App\Presenters\ActionlogPresenter::class;
    use SoftDeletes;
    use Presentable;

    protected $table = 'action_logs';
    public $timestamps = true;
    protected $fillable = [
        'created_at',
        'item_type',
        'created_by',
        'item_id',
        'action_type',
        'note',
        'target_id',
        'target_type',
        'stored_eula'
    ];

    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = [
        'action_type',
        'note',
        'log_meta',
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
        'company'     => ['name'],
        'adminuser'   => ['first_name','last_name','username', 'email', 'employee_num'],
        'user'        => ['first_name','last_name','username', 'email', 'employee_num'],
        'assets'      => ['asset_tag','name', 'serial', 'order_number', 'notes', 'purchase_date'],
        'assets.model'              => ['name', 'model_number', 'eol', 'notes'],
        'assets.model.category'     => ['name', 'notes'],
        'assets.location'           => ['name'],
        'assets.defaultLoc'         => ['name'],
        'assets.model.manufacturer' => ['name', 'notes'],
        'licenses'    => ['name', 'serial', 'notes', 'order_number', 'license_email', 'license_name', 'purchase_order', 'purchase_date'],
        'licenses.category'     => ['name', 'notes'],
        'licenses.supplier'     => ['name'],
        'consumables'    => ['name', 'notes', 'order_number', 'model_number', 'item_no', 'purchase_date'],
        'consumables.category'     => ['name', 'notes'],
        'consumables.location'     => ['name', 'notes'],
        'consumables.supplier'     => ['name', 'notes'],
        'components'     => ['name', 'notes', 'purchase_date'],
        'components.category'     => ['name', 'notes'],
        'components.location'     => ['name', 'notes'],
        'components.supplier'     => ['name', 'notes'],
        'accessories'     => ['name', 'purchase_date'],
        'accessories.category'     => ['name'],
        'accessories.location'     => ['name', 'notes'],
        'accessories.supplier'     => ['name', 'notes'],
    ];

    /**
     * Override from Builder to automatically add the company
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public static function boot()
    {
        parent::boot();
        static::creating(
            function (self $actionlog) {
                // If the admin is a superadmin, let's see if the target instead has a company.
                if (auth()->user() && auth()->user()->isSuperUser()) {
                    if ($actionlog->target) {
                        $actionlog->company_id = $actionlog->target->company_id;
                    } elseif ($actionlog->item) {
                        $actionlog->company_id = $actionlog->item->company_id;
                    }
                } elseif (auth()->user() && auth()->user()->company) {
                    $actionlog->company_id = auth()->user()->company_id;
                }

                if ($actionlog->action_date == '') {
                    $actionlog->action_date = Carbon::now();
                }

            }
        );
    }


    /**
     * Establishes the actionlog -> item relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function item()
    {
        return $this->morphTo('item')->withTrashed();
    }

    /**
     * Establishes the actionlog -> company relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function company()
    {
        return $this->hasMany(\App\Models\Company::class, 'id', 'company_id');
    }


    /**
     * Establishes the actionlog -> asset relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function assets()
    {
        return $this->hasMany(\App\Models\Asset::class, 'id', 'item_id');
    }

    /**
     * Establishes the actionlog -> license relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function licenses()
    {
        return $this->hasMany(\App\Models\License::class, 'id', 'item_id');
    }

    /**
     * Establishes the actionlog -> consumable relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function consumables()
    {
        return $this->hasMany(\App\Models\Consumable::class, 'id', 'item_id');
    }

    /**
     * Establishes the actionlog -> consumable relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function accessories()
    {
        return $this->hasMany(\App\Models\Accessory::class, 'id', 'item_id');
    }

    /**
     * Establishes the actionlog -> components relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function components()
    {
        return $this->hasMany(\App\Models\Component::class, 'id', 'item_id');
    }

    /**
     * Establishes the actionlog -> item type relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
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
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
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
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function userlog()
    {
        return $this->target();
    }

    /**
     * Establishes the actionlog -> admin user relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function adminuser()
    {
        return $this->belongsTo(User::class, 'created_by')
            ->withTrashed();
    }

    /**
     * Establishes the actionlog -> user relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
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
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function target()
    {
        return $this->morphTo('target')->withTrashed();
    }

    /**
     * Establishes the actionlog -> location relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function location()
    {
        return $this->belongsTo(\App\Models\Location::class, 'location_id')->withTrashed();
    }


    /**
     * Check if the file exists, and if it does, force a download
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v3.0]
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
     * @since  [v3.0]
     * @return bool
     */
    public function logaction($actiontype)
    {
        $this->action_type = $actiontype;
        $this->remote_ip =  request()->ip();
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
     * @since  [v4.0]
     * @return int
     */
    public function daysUntilNextAudit($monthInterval = 12, $asset = null)
    {
        $now = Carbon::now();
        $last_audit_date = $this->created_at; // this is the action log's created at, not the asset itself
        $next_audit = $last_audit_date->addMonth($monthInterval); // this actually *modifies* the $last_audit_date
        $next_audit_days = (int) round($now->diffInDays($next_audit, true));
        $override_default_next = $next_audit;

        // Override the default setting for interval if the asset has its own next audit date
        if (($asset) && ($asset->next_audit_date)) {
            $override_default_next = Carbon::parse($asset->next_audit_date);
            $next_audit_days = (int) round($override_default_next->diffInDays($now, true));
        }

        // Show as negative number if the next audit date is before the audit date we're looking at
        if ($this->created_at > $override_default_next) {
            $next_audit_days = '-'.$next_audit_days;
        }

        return $next_audit_days;
    }

    /**
     * Calculate the date of the next audit
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v4.0]
     * @return \Datetime
     */
    public function calcNextAuditDate($monthInterval = 12, $asset = null)
    {
        $last_audit_date = Carbon::parse($this->created_at);
        // If there is an asset-specific next date already given,
        if (($asset) && ($asset->next_audit_date)) {
            return \Carbon::parse($asset->next_audit_date);
        }

        return  \Carbon::parse($last_audit_date)->addMonths($monthInterval)->toDateString();
    }

    /**
     * Gets action logs in chronological order, excluding uploads
     *
     * @author Vincent Sposato <vincent.sposato@gmail.com>
     * @since  v1.0
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getListingOfActionLogsChronologicalOrder()
    {
        return $this->all()
            ->where('action_type', '!=', 'uploaded')
            ->orderBy('item_id', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Determines what the type of request is so we can log it to the action_log
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  v6.3.0
     * @return string
     */
    public function determineActionSource(): string
    {
        // This is a manually set source
        if($this->source) {
            return $this->source;
        }

        // This is an API call
        if (((request()->header('content-type') && (request()->header('accept'))=='application/json'))
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
     * @since [v8.0.4]
     * @return \App\Models\Actionlog
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
            return 'private_uploads/eula-pdfs/'.$this->filename;
        }

        if ($this->action_type == 'audit')  {
            return 'private_uploads/audits/'.$this->filename;
        }

        switch ($this->item_type) {
        case Accessory::class:
            return 'private_uploads/accessories/'.$this->filename;
        case Asset::class:
            return 'private_uploads/assets/'.$this->filename;
        case AssetModel::class:
            return 'private_uploads/models/'.$this->filename;
        case Consumable::class:
            return 'private_uploads/consumables/'.$this->filename;
        case Component::class:
            return 'private_uploads/components/'.$this->filename;
        case License::class:
            return 'private_uploads/licenses/'.$this->filename;
        case Location::class:
            return 'private_uploads/locations/'.$this->filename;
        case Maintenance::class:
             return 'private_uploads/maintenances/'.$this->filename;
        case User::class:
            return 'private_uploads/users/'.$this->filename;
        default:
            return null;
        }
    }


    // Manually sets $this->source for determineActionSource()
    public function setActionSource($source = null): void
    {
        $this->source = $source;
    }

    public function scopeOrderByCreatedBy($query, $order)
    {
        return $query->leftJoin('users as admin_sort', 'action_logs.created_by', '=', 'admin_sort.id')->select('action_logs.*')->orderBy('admin_sort.first_name', $order)->orderBy('admin_sort.last_name', $order);
    }
}