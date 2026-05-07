<?php

namespace App\Models\Traits;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\ICompanyableChild;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\AuditNotification;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
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

        return $this->morphMany(Actionlog::class, 'item')
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
        $history = $this->history();

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
            throw new \Exception('All checkout logs require a target.');

            return;
        }

        if (! isset($target->id)) {
            throw new \Exception('That target seems invalid (no target ID available).');

            return;
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

        if (static::class == Asset::class) {
            if ($asset = Asset::find($log->item_id)) {

                // add the custom fields that were changed
                if ($asset->model->fieldset) {
                    $fields_array = [];
                    foreach ($asset->model->fieldset->fields as $field) {
                        if ($field->display_checkout == 1) {
                            $fields_array[$field->db_column] = $asset->{$field->db_column};
                        }
                    }
                }
            }
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
        if ($this instanceof ICompanyableChild) {
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

            if (static::class == Asset::class) {
                if ($asset = Asset::find($log->item_id)) {
                    $asset->increment('checkin_counter', 1);

                    // add the custom fields that were changed
                    if ($asset->model->fieldset) {
                        $fields_array = [];
                        foreach ($asset->model->fieldset->fields as $field) {
                            if ($field->display_checkin == 1) {
                                $fields_array[$field->db_column] = $asset->{$field->db_column};
                            }
                        }
                    }
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

        if (static::class == Asset::class) {
            if ($asset = Asset::find($log->item_id)) {
                // add the custom fields that were changed
                if ($asset->model->fieldset) {
                    $fields_array = [];
                    foreach ($asset->model->fieldset->fields as $field) {
                        if ($field->display_audit == 1) {
                            $fields_array[$field->db_column] = $asset->{$field->db_column};
                        }
                    }
                }
            }
        }

        $changed = [];

        unset($originalValues['updated_at'], $originalValues['last_audit_date']);
        foreach ($originalValues as $key => $value) {

            if ($value != $this->getAttributes()[$key]) {
                $changed[$key]['old'] = $value;
                $changed[$key]['new'] = $this->getAttributes()[$key];
            }
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

                Log::error('Teams webhook server error', [
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

                Log::error('Teams webhook request failure', [
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage(),
                ]);
            } catch (Throwable $e) {
                Log::error('Teams webhook failed unexpectedly', [
                    'endpoint' => $endpoint,
                    'exception' => get_class($e),
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            Setting::getSettings()->notify(new AuditNotification($params));
        }

        return $log;
    }

    /**
     * @author Daniel Meltzer <dmeltzer.devel@gmail.com>
     *
     * @since  [v3.5]
     *
     * @return Actionlog
     */
    public function logCreate($note = null)
    {
        $created_by = -1;
        if (auth()->user()) {
            $created_by = auth()->id();
        }
        $log = new Actionlog;
        if (static::class == LicenseSeat::class) {
            $log->item_type = License::class;
            $log->item_id = $this->license_id;
        } else {
            $log->item_type = static::class;
            $log->item_id = $this->id;
        }
        $log->location_id = null;
        $log->action_date = date('Y-m-d H:i:s');
        $log->note = $note;
        $log->created_by = $created_by;
        $log->company_id = $this->resolveLoggableCompanyId();
        $log->logaction('create');
        $log->save();

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
        $log->created_at = date('Y-m-d H:i:s');
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
