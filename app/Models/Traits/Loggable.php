<?php

namespace App\Models\Traits;

use App\Enums\ActionType;
use App\Exceptions\MissingLogTarget;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\CompanyableScope;
use App\Models\Contracts\CompanyableChild;
use App\Models\Contracts\HasCustomFields;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\Maintenance;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\AuditNotification;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Osama\LaravelTeamsNotification\TeamsNotification;
use Throwable;

trait Loggable
{
    // an attribute for setting whether or not the item was imported
    public ?bool $imported = false;

    protected ?ActionType $action_type = null;

    /**
     * Lifecycle log written by the latest save, if any.
     */
    protected ?Actionlog $currentActionLog = null;

    protected ?OrderItem $order_item = null;

    public function setOrderItem(?OrderItem $order_item): self
    {
        $this->order_item = $order_item;
        return $this;
    }

    public static function bootLoggable(): void
    {
        self::updating(function (self $item) {
            if (!$item->action_type) {
                \Log::debug('UPDATING! Because action_type was blank');
                $item->action_type = ActionType::Update;
            } else {
                \Log::debug('NOT updating because action_type is: ' . $item->action_type->name);
            }
        });

        self::creating(function (self $item) {
            $item->action_type = ActionType::Create;
        });

        self::deleting(function (self $item) {
            $item->action_type = ActionType::Delete;
        });

        self::restoring(function (self $item) {
            $item->action_type = ActionType::Restore;
        });

        // self::restored(function ($item) {
        //     \Log::error("RESTORED!!");
        //     $item->real_log("restored");
        // });

        self::deleted(function (self $item) {
            $item->log_lifecycle_events();
        });

        self::saved(function (self $item) {
            $item->log_lifecycle_events();
        });
    }

    public function log_lifecycle_events(): void
    {
        $this->currentActionLog = null;

        // This is where the magic happens!
        try {
            \Log::debug('ActionType: ' . ($this->action_type ? $this->action_type->name : 'NULL'));
        } catch (\Exception $e) {
            \Log::debug('Exception: ' . $e->getMessage());
        }

        if (!$this->action_type) {
            \Log::debug('No action type, aborting save.');

            return;
        }

        if ($this->action_type === ActionType::Update && $this instanceof Asset
            && $this->isDirty(['assigned_to', 'last_checkout', 'checkout_counter', 'checkin_counter', 'requests_counter', 'deleted_at'])) {
            $this->action_type = null;

            return;
        }

        $changed = $this->get_changes();

        if (empty($changed) && $this->action_type == ActionType::Update) {
            // empty changeset, and this is an 'update' - don't log it
            \Log::debug("Not saving an action log because there's no changes, and no 'interesting' action type");
            $this->action_type = null;

            return;
        }

        $logAction = $this->makeLifecycleLog($changed);
        $logAction->logaction($this->action_type);
        $this->currentActionLog = $logAction;
        // That inherently did a save, yeah?
        $this->action_type = null;
        \Log::debug('Finished logging now, resetting action_type to null');
    }

    /**
     * @param  array<string, array{old: mixed, new: mixed}>  $changed
     */
    protected function makeLifecycleLog(array $changed): Actionlog
    {
        $logAction = new Actionlog;
        $logAction->item_type = $this::class;
        $logAction->item_id = $this->id;
        $logAction->action_date = date('Y-m-d H:i:s');
        $logAction->created_at = now();
        $logAction->created_by = auth()->id();
        $this->setLifecycleLogTarget($logAction);
        // Fallback to the model's own created_by so seeder / artisan
        // paths (no authenticated user) still attribute the create log
        // to a real user. On web requests auth()->id() is always set,
        // so the fallback never engages there.
        if ($this->action_type === ActionType::Create) {
            $logAction->created_by = auth()->id() ?? $this->created_by;
        }
        if ($this->imported) {
            $logAction->setActionSource('importer');
        }
        $logAction->log_meta = json_encode($changed);

        $this->setLifecycleLogQuantity($logAction);

        return $logAction;
    }

    protected function setLifecycleLogTarget(Actionlog $logAction): void
    {
        if ($this instanceof User && $this->action_type !== ActionType::Create) {
            // Users are a weird exception where they target themselves on changes (but not on Create)
            $logAction->target()->associate($this);
        }
        if ($this instanceof Maintenance) {
            // a Maintenance action has a *real* target of whatever is being maintained (->item)
            $logAction->target()->associate($this->item);
            if (in_array($this->action_type, [ActionType::Create, ActionType::Delete], true)) {
                // Another oddity - actionlog 'note' apparently propagates the maintenance note on Create/Delete?
                $logAction->note = $this->notes;
            }
        }
    }

    protected function setLifecycleLogQuantity(Actionlog $logAction): void
    {
        if ($this->order_item) {
            $logAction->order_item_id = $this->order_item->id;
            $logAction->quantity = $this->order_item->qty;
            $this->order_item = null;
        } elseif ($this->action_type === ActionType::Create
            && array_key_exists('qty', $this->getAttributes())) {
            $logAction->quantity = (int) $this->getAttributes()['qty'];
        }
    }

    /**
     * @return list<string>
     */
    protected function maskedLogFields(): array
    {
        return [];
    }

    /**
     * @return list<string>|null
     */
    protected function allowedLogFields(): ?array
    {
        return null;
    }

    /**
     * @param  list<string>|null  $limit_keys
     * @return array<string, array{old: mixed, new: mixed}>
     */
    public function get_changes(?array $limit_keys = null): array
    {
        $masked_fields_hash = array_flip($this->maskedLogFields());
        $changed = [];

        $originalValues = $this->getLoggableOriginalValues($limit_keys);
        foreach ($originalValues as $key => $oldValue) {
            $newValue = $this->getAttributes()[$key];

            // special-case handling for 'permissions' key of Users
            if ($key === 'permissions' and $this instanceof User) {
                $permissionChanges = $this->getPermissionChanges($oldValue, $newValue);
                if ($permissionChanges !== null) {
                    $changed['permissions'] = $permissionChanges;
                }

                continue;
            }

            if (array_key_exists($key, $this->getAttributes()) && $oldValue != $this->getAttributes()[$key]) {
                if (array_key_exists($key, $masked_fields_hash)) {
                    // Do not store the hashed password (or any other 'masked' field) in changes
                    $changed[$key]['old'] = '*************';
                    $changed[$key]['new'] = '*************';
                } else {
                    $changed[$key]['old'] = $oldValue;
                    $changed[$key]['new'] = $newValue;
                }
            }
            // NOTE - if the attribute exists in $originalValues, but *not* in ->getAttributes(), it isn't added to $changed
        }

        return $changed;
    }

    /**
     * @param  list<string>|null  $limitKeys
     * @return array<string, mixed>
     */
    protected function getLoggableOriginalValues(?array $limitKeys): array
    {
        $originalValues = $this->getRawOriginal();
        $allowedFields = $this->allowedLogFields();

        if ($allowedFields) {
            // "disallowed" field at the _model_ level; do not log it
            $originalValues = array_intersect_key($originalValues, array_flip($allowedFields));
        }

        if ($limitKeys) {
            // key limited at the *call site* - for checkin, checkout
            // - things where you want to limit the changeable fields
            $originalValues = array_intersect_key($originalValues, array_flip($limitKeys));
        }

        return $originalValues;
    }

    /**
     * @return array{old: string|false, new: string|false}|null
     */
    protected function getPermissionChanges(?string $oldValue, ?string $newValue): ?array
    {
        // Compare decoded to avoid spurious diffs from key reordering or type coercion.
        $oldDecoded = json_decode($oldValue ?? '{}', true) ?: [];
        $newDecoded = json_decode($newValue ?? '{}', true) ?: [];
        if ($oldDecoded == $newDecoded) {
            return null;
        }

        // Only log the permission keys that actually changed.
        $diffOld = [];
        $diffNew = [];
        foreach (array_unique(array_merge(array_keys($oldDecoded), array_keys($newDecoded))) as $permKey) {
            $oldPerm = $oldDecoded[$permKey] ?? null;
            $newPerm = $newDecoded[$permKey] ?? null;
            $normalizedOld = $this->normalizePermissionForLog($oldPerm);
            $normalizedNew = $this->normalizePermissionForLog($newPerm);
            if ($normalizedOld !== $normalizedNew) {
                $diffOld[$permKey] = $oldPerm;
                $diffNew[$permKey] = $newPerm;
            }
        }

        if (!empty($diffOld) || !empty($diffNew)) {
            return ['old' => json_encode($diffOld), 'new' => json_encode($diffNew)];
        }

        return null;
    }

    protected function normalizePermissionForLog(mixed $value): mixed
    {
        // null and "0" are both "inherit" — treat them as equivalent
        return in_array($value, [null, '0', 0], true) ? null : $value;
    }

    /**
     * @return MorphMany
     *
     * @since  [v3.4]
     *
     * @author Daniel Meltzer <dmeltzer.devel@gmail.com>
     */
    public function log()
    {
        return $this->morphMany(Actionlog::class, 'item');
    }

    public function history()
    {
        // Bypass FMCS company scoping: access is already gated by the policy on the
        // parent object. Objects like AssetModel and Company have no company_id, so
        // their history logs always have company_id = null, which the scope would hide.
        return $this->morphMany(Actionlog::class, 'item')
            ->withoutGlobalScope(CompanyableScope::class)
            ->orWhere(function ($query) {
                $query->where('target_type', '=', static::class)
                    ->where('target_id', '=', $this->getKey());
            });
    }

    public function getHistory(Request $request)
    {
        $allowed_columns = [
            'id',
            'created_at',
            'target_id',
            'created_by',
            'accept_signature',
            'action_type',
            'note',
            'remote_ip',
            'user_agent',
            'target_type',
            'item_type',
            'action_source',
            'action_date',
        ];

        // Start with the polymorphic history relation so all filters and
        // ordering are applied to the same query instance.
        //
        // 'orderItem.order' eager-loaded because ActionlogsTransformer
        // renders order_number / purchase_order via the OrderItem line
        // to its parent Order. Without this the transformer fires an
        // N+1 subquery per row in the history tab.
        $history = $this->history()->with('orderItem.order');

        if ($request->filled('search')) {
            $history = $history->TextSearch(e($request->input('search')));
        }

        if ($request->filled('action_type')) {
            $history = $history->where('action_type', '=', $request->input('action_type'));
        }

        if ($request->filled('created_by')) {
            $history = $history->where('created_by', '=', $request->input('created_by'));
        }

        if ($request->filled('action_source')) {
            $history = $history->where('action_source', '=', $request->input('action_source'));
        }

        if ($request->filled('remote_ip')) {
            $history = $history->where('remote_ip', '=', $request->input('remote_ip'));
        }

        if ($request->filled('uploads')) {
            $history = $history->whereNotNull('filename');
        }

        $order = ($request->input('order') == 'asc') ? 'asc' : 'desc';

        switch ($request->input('sort')) {
            case 'created_by':
                $history = $history->OrderByCreatedBy($order);
                break;
            default:
                $sort = in_array($request->input('sort'), $allowed_columns) ? e($request->input('sort')) : 'action_logs.created_at';
                $history = $history->orderBy($sort, $order);
                break;
        }

        return $history->forApiHistory();

    }

    public function setImported(bool $bool): void
    {
        $this->imported = $bool;
    }

    /**
     * @author Daniel Meltzer <dmeltzer.devel@gmail.com>
     *
     * @since  [v3.4]
     *
     * @return Actionlog
     *
     * @throws MissingLogTarget When the caller couldn't hand us a valid
     *                          target. Deliberately typed so callers wrapping
     *                          their work in a DB::transaction can catch it
     *                          specifically, roll the transaction back, and
     *                          return a proper 4xx response body instead of
     *                          letting an unhandled 500 leak into the caller.
     */
    public function logCheckout($note, $target, $action_date = null, $originalValues = [], $quantity = 1)
    {

        $log = new Actionlog;

        $fields_array = [];

        $log = $this->determineLogItemType($log);
        if (auth()->user()) {
            $log->created_by = auth()->id();
        }

        if (! isset($target)) {
            throw new MissingLogTarget('All checkout logs require a target.');
        }

        if (! isset($target->id)) {
            throw new MissingLogTarget('That target seems invalid (no target ID available).');
        }

        $log->target_type = get_class($target);
        $log->target_id = $target->id;

        // Figure out what the target is
        if ($log->target_type == Location::class) {
            $log->location_id = $target->id;
        } elseif ($log->target_type == Asset::class) {
            $log->location_id = $target->location_id;
        } else {
            $log->location_id = $target->location_id;
        }

        if ($this instanceof HasCustomFields) {
            $item = static::whereKey($log->item_id)->first();
            // add the custom fields that were changed
            $fields_array = $this->customFieldValuesForLog($item, 'display_checkout');
        }

        $log->note = $note;
        $log->action_date = $action_date;
        $log->quantity = $quantity;
        $log->company_id = $this->resolveLoggableCompanyId();

        $changed = [];
        $array_to_flip = array_keys($fields_array);
        $array_to_flip = array_merge($array_to_flip, ['name', 'status_id', 'location_id', 'expected_checkin', 'requestable']);
        $originalValues = array_intersect_key($originalValues, array_flip($array_to_flip));

        foreach ($originalValues as $key => $value) {
            // TODO - action_date isn't a valid attribute of any first-class object, so we might want to remove this?
            if ($key == 'action_date' && $value != $action_date) {
                $changed[$key]['old'] = $value;
                $changed[$key]['new'] = is_string($action_date) ? $action_date : $action_date->format('Y-m-d H:i:s');
            } elseif (array_key_exists($key, $this->getAttributes()) && $value != $this->getAttributes()[$key]) {
                $changed[$key]['old'] = $value;
                $changed[$key]['new'] = $this->getAttributes()[$key];
            }
            // NOTE - if the attribute exists in $originalValues, but *not* in ->getAttributes(), it isn't added to $changed
        }

        if (! empty($changed)) {
            $log->log_meta = json_encode($changed);
        }

        $log->logaction('checkout');

        return $log;
    }

    /**
     * Helper method to determine the log item type
     */
    private function determineLogItemType($log)
    {
        // We need to special case licenses because of license_seat vs license.  So much for clean polymorphism :
        if (static::class == LicenseSeat::class) {
            $log->item_type = License::class;
            $log->item_id = $this->license_id;
        } else {
            $log->item_type = static::class;
            $log->item_id = $this->id;
        }

        return $log;
    }

    /**
     * Resolve the company_id that should be stamped on an action log entry.
     *
     * LicenseSeat does not carry a company_id directly — it belongs to a License,
     * so we fetch the parent license's company_id in that case.  All other models
     * that use the Loggable trait have a company_id column directly.
     */
    private function resolveLoggableCompanyId(): ?int
    {
        if (static::class === LicenseSeat::class) {
            return $this->license?->company_id;
        }

        if (isset($this->company_id)) {
            return $this->company_id;
        }

        // Companyable children (like Maintenance) inherit company visibility from parents.
        if ($this instanceof CompanyableChild) {
            foreach ((array) $this->getCompanyableParents() as $parentRelation) {
                $parent = $this->{$parentRelation} ?? null;

                if (isset($parent?->company_id)) {
                    return $parent->company_id;
                }
            }
        }

        return null;
    }

    /**
     * @author Daniel Meltzer <dmeltzer.devel@gmail.com>
     *
     * @since  [v3.4]
     *
     * @return Actionlog
     */
    public function logCheckin($target, $note, $action_date = null, $originalValues = [])
    {
        $log = new Actionlog;

        $fields_array = [];

        if ($target != null) {
            $log->target_type = get_class($target);
            $log->target_id = $target->id;

        }

        if (static::class == LicenseSeat::class) {
            $log->item_type = License::class;
            $log->item_id = $this->license_id;
        } else {
            $log->item_type = static::class;
            $log->item_id = $this->id;

            if ($this instanceof HasCustomFields) {
                $item = static::whereKey($log->item_id)->first();
                if ($item !== null) {
                    if (static::class == Asset::class) {
                        $item->increment('checkin_counter', 1);
                    }

                    // add the custom fields that were changed
                    $fields_array = $this->customFieldValuesForLog($item, 'display_checkin');
                }
            }
        }

        $log->location_id = null;
        $log->note = $note;
        $log->action_date = $action_date;
        $log->company_id = $this->resolveLoggableCompanyId();

        if (! $action_date) {
            $log->action_date = date('Y-m-d H:i:s');
        }

        if (auth()->user()) {
            $log->created_by = auth()->id();
        }

        $changed = [];

        $array_to_flip = array_keys($fields_array);
        $array_to_flip = array_merge($array_to_flip, ['name', 'status_id', 'location_id', 'expected_checkin', 'requestable']);

        $originalValues = array_intersect_key($originalValues, array_flip($array_to_flip));

        foreach ($originalValues as $key => $value) {

            if ($key == 'action_date' && $value != $action_date) {
                $changed[$key]['old'] = $value;
                $changed[$key]['new'] = is_string($action_date) ? $action_date : $action_date->format('Y-m-d H:i:s');
            } elseif ($value != $this->getAttributes()[$key]) {
                $changed[$key]['old'] = $value;
                $changed[$key]['new'] = $this->getAttributes()[$key];
            }
        }

        if (! empty($changed)) {
            $log->log_meta = json_encode($changed);
        }

        $log->logaction('checkin from');

        return $log;
    }

    /**
     * Logs a force checkin action for orphaned assignments.
     *
     * Force checkin only records an explicit action log entry and intentionally
     * skips checkin counters and changed-field metadata.
     *
     * @return Actionlog
     */
    public function logForceCheckin($note = null)
    {
        $log = new Actionlog;

        $log = $this->determineLogItemType($log);
        $log->location_id = null;
        $log->note = $note;
        $log->action_date = date('Y-m-d H:i:s');

        if (auth()->user()) {
            $log->created_by = auth()->id();
        }

        $log->logaction('force checkin');

        return $log;
    }

    /** @return array<string, mixed> */
    protected function customFieldValuesForLog(?Model $item, string $filter): array
    {
        if (! ($item instanceof HasCustomFields)) {
            return [];
        }

        $values = [];
        $attributes = $item->getAttributes();
        foreach ($item->custom_fieldset->fields ?? [] as $field) {
            if ($field->{$filter}) {
                $values[$field->db_column] = $attributes[$field->db_column] ?? null;
            }
        }

        return $values;
    }

    /**
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v4.0]
     *
     * @return Actionlog
     */
    public function logAudit($note, $location_id, $filename = null, $originalValues = [])
    {

        $log = new Actionlog;

        $fields_array = $this->customFieldValuesForLog($this, 'display_audit');

        $changed = [];

        unset($originalValues['updated_at'], $originalValues['last_audit_date']);
        foreach ($originalValues as $key => $value) {

            if ($value != $this->getAttributes()[$key]) {
                $changed[$key]['old'] = $value;
                $changed[$key]['new'] = $this->getAttributes()[$key];
            }
        }

        if (! empty($fields_array)) {
            $changed['_audit_snapshot'] = $fields_array;
        }

        if (! empty($changed)) {
            $log->log_meta = json_encode($changed);
        }

        $location = Location::find($location_id);
        if (static::class == LicenseSeat::class) {
            $log->item_type = License::class;
            $log->item_id = $this->license_id;
        } else {
            $log->item_type = static::class;
            $log->item_id = $this->id;
        }
        $log->location_id = ($location_id) ? $location_id : null;
        $log->note = $note;
        $log->created_by = auth()->id();
        $log->filename = $filename;
        $log->action_date = date('Y-m-d H:i:s');
        // Explicitly stamp company_id from the item being audited so FMCS scoping works correctly.
        $log->company_id = $this->resolveLoggableCompanyId();
        $log->logaction('audit');

        $params = [
            'item' => $log->item,
            'filename' => $log->filename,
            'admin' => $log->adminuser,
            'location' => ($location) ? $location->name : '',
            'note' => $note,
        ];

        if (Setting::getSettings()->webhook_selected === 'microsoft' && Str::contains(Setting::getSettings()->webhook_endpoint, 'workflows')) {

            $endpoint = Setting::getSettings()->webhook_endpoint;

            try {
                $message = AuditNotification::toMicrosoftTeams($params);
                $notification = new TeamsNotification($endpoint);
                $notification->success()->sendMessage($message[0], $message[1]);

            } catch (ConnectException $e) {
                Log::warning('Teams webhook connection failed', [
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage(),
                ]);

            } catch (ServerException $e) {

                Log::warning('Teams webhook server error', [
                    'endpoint' => $endpoint,
                    'status' => $e->getResponse()?->getStatusCode(),
                    'error' => $e->getMessage(),
                ]);

            } catch (ClientException $e) {

                Log::warning('Teams webhook client error', [
                    'endpoint' => $endpoint,
                    'status' => $e->getResponse()?->getStatusCode(),
                    'error' => $e->getMessage(),
                ]);
            } catch (RequestException $e) {

                Log::warning('Teams webhook request failure', [
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage(),
                ]);
            } catch (Throwable $e) {
                Log::warning('Teams webhook failed unexpectedly', [
                    'endpoint' => $endpoint,
                    'exception' => get_class($e),
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            try {
                Setting::getSettings()->notify(new AuditNotification($params));
            } catch (Throwable $e) {
                Log::warning('Audit webhook notification failed', [
                    'endpoint' => Setting::getSettings()->webhook_endpoint,
                    'channel' => Setting::getSettings()->webhook_selected,
                    'exception' => get_class($e),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $log;
    }

    /**
     * @author Daniel Meltzer <dmeltzer.devel@gmail.com>
     *
     * @since  [v3.4]
     *
     * @return Actionlog
     */
    public function logUpload($filename, $note)
    {
        $log = new Actionlog;
        if (static::class == LicenseSeat::class) {
            $log->item_type = License::class;
            $log->item_id = $this->license_id;
        } else {
            $log->item_type = static::class;
            $log->item_id = $this->id;
        }
        $log->created_by = auth()->id();
        $log->note = $note;
        $log->target_id = null;
        $log->company_id = $this->resolveLoggableCompanyId();
        $log->created_at = now();
        $log->action_date = date('Y-m-d H:i:s');
        $log->filename = $filename;
        $log->logaction('uploaded');

        return $log;
    }

    /**
     * Get latest signature from a specific user
     *
     * This just makes the print view a bit cleaner
     * Returns the latest acceptance ActionLog that contains a signature
     * from $user or null if there is none
     *
     * @return null|Actionlog
     **/
    public function getLatestSignedAcceptance(User $user)
    {
        return $this->log->where('target_type', User::class)
            ->where('target_id', $user->id)
            ->where('action_type', 'accepted')
            ->where('accept_signature', '!=', null)
            ->sortByDesc('created_at')
            ->first();
    }
}
