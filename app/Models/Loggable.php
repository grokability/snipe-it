<?php

namespace App\Models;

use App\Models\Setting;
use App\Notifications\AuditNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Osama\LaravelTeamsNotification\TeamsNotification;

trait Loggable
{
    // an attribute for setting whether or not the item was imported
    public ?bool $imported = false;

    /**
     * @author Daniel Meltzer <dmeltzer.devel@gmail.com>
     * @since  [v3.4]
     * @return \App\Models\Actionlog
     */
    public function log()
    {
        return $this->morphMany(Actionlog::class, 'item');
    }

    public function setImported(bool $bool): void
    {
        $this->imported = $bool;
    }

    /**
     * @author Daniel Meltzer <dmeltzer.devel@gmail.com>
     * @since  [v3.4]
     * @return \App\Models\Actionlog
     */
    public function logCheckout($note, $target, $action_date = null, $originalValues = [])
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


        $changed = [];
        $array_to_flip = array_keys($fields_array);
        $array_to_flip = array_merge($array_to_flip, ['name','status_id','location_id','expected_checkin']);
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

        if (!empty($changed)) {
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
     * @author Daniel Meltzer <dmeltzer.devel@gmail.com>
     * @since  [v3.4]
     * @return \App\Models\Actionlog
     */
    public function logCheckin($target, $note, $action_date = null, $originalValues = [])
    {
        $log = new Actionlog;

        $fields_array = [];

        if($target != null) {
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

        if (!$action_date) {
            $log->action_date = date('Y-m-d H:i:s');
        }

        if (auth()->user()) {
            $log->created_by = auth()->id();
        }

        $changed = [];

        $array_to_flip = array_keys($fields_array);
        $array_to_flip = array_merge($array_to_flip, ['name','status_id','location_id','expected_checkin']);

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

        if (!empty($changed)) {
            $log->log_meta = json_encode($changed);
        }

        $log->logaction('checkin from');

        return $log;
    }

    /**
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v4.0]
     * @return \App\Models\Actionlog
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

        if (!empty($changed)) {
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
        $log->logaction('audit');

        $params = [
            'item' => $log->item,
            'filename' => $log->filename,
            'admin' => $log->adminuser,
            'location' => ($location) ? $location->name : '',
            'note' => $note,
        ];
        if(Setting::getSettings()->webhook_selected === 'microsoft' && Str::contains(Setting::getSettings()->webhook_endpoint, 'workflows')) {
            $message = AuditNotification::toMicrosoftTeams($params);
            $notification = new TeamsNotification(Setting::getSettings()->webhook_endpoint);
            $notification->success()->sendMessage($message[0], $message[1]);
        }
        else {
            Setting::getSettings()->notify(new AuditNotification($params));
        }

        return $log;
    }

    /**
     * @author Daniel Meltzer <dmeltzer.devel@gmail.com>
     * @since  [v3.5]
     * @return \App\Models\Actionlog
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
        $log->logaction('create');
        $log->save();

        return $log;
    }

    /**
     * @author Daniel Meltzer <dmeltzer.devel@gmail.com>
     * @since  [v3.4]
     * @return \App\Models\Actionlog
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
     * @param  User $user
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
