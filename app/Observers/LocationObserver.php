<?php

namespace App\Observers;

use App\Models\Location;
use App\Models\Actionlog;
use Illuminate\Support\Facades\Auth;

class LocationObserver
{
        /**
     * Listen to the Location updating event.
     *
     * @param  Location  $location
     * @return void
     */
    public function updating(Location $location)
    {
        $attributes = $location->getAttributes();
        $attributesOriginal = $location->getOriginal();
        $changed = [];

        // Liste promenjenih polja
        $fields = [
            'name', 'city', 'state', 'country', 
            'created_by', 'address', 'address2', 'zip', 'fax', 'phone', 
            'parent_id', 'currency', 'ldap_ou', 'manager_id', 
            'image', 'notes', 'status_id'
        ];

        // Prolazak kroz polja i upisivanje promena
        foreach ($fields as $field) {
            if (isset($attributes[$field]) && $attributes[$field] != $attributesOriginal[$field]) {
                $changed[$field] = [
                    'old' => $attributesOriginal[$field],
                    'new' => $attributes[$field],
                ];
            }
        }

        // Ako postoje promene, logujte ih
        if (!empty($changed)) {
            $logAction = new Actionlog();
            $logAction->item_type = Location::class;
            $logAction->item_id = $location->id;
            $logAction->created_at = now();
            $logAction->created_by = Auth::id();
            $logAction->log_meta = json_encode($changed);
            $logAction->logaction('update'); // Akcija 'update' za promene
            $logAction->save();
        }
    }

    /**
     * Listen to the Location created event.
     *
     * @param  Location  $location
     * @return void
     */
    public function created(Location $location)
    {
        $logAction = new Actionlog();
        $logAction->item_type = Location::class;
        $logAction->item_id = $location->id;
        $logAction->created_at = now();
        $logAction->created_by = Auth::id();
        $logAction->log_meta = json_encode([
            'new' => $location->getAttributes(),
        ]);
        $logAction->logaction('create'); // Akcija 'create' za novorođeni zapis
        $logAction->save();
    }

    /**
     * Listen to the Location deleting event.
     *
     * @param  Location  $location
     * @return void
     */
    public function deleting(Location $location)
    {
        $logAction = new Actionlog();
        $logAction->item_type = Location::class;
        $logAction->item_id = $location->id;
        $logAction->created_at = now();
        $logAction->created_by = Auth::id();
        $logAction->logaction('delete'); // Akcija 'delete' za obrisani zapis
        $logAction->save();
    }

    /**
     * Handle the Location "updated" event.
     */
    public function updated(Location $location): void
    {
        //
    }

    /**
     * Handle the Location "deleted" event.
     */
    public function deleted(Location $location): void
    {
        //
    }

    /**
     * Handle the Location "restored" event.
     */
    public function restored(Location $location): void
    {
        //
    }

    /**
     * Handle the Location "force deleted" event.
     */
    public function forceDeleted(Location $location): void
    {
        //
    }
}
