<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Asset;
use App\Models\Maintenance;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class MaintenancesTransformer
{
    public function transformMaintenances(Collection $maintenances, $total)
    {
        $array = [];
        foreach ($maintenances as $assetmaintenance) {
            $array[] = self::transformMaintenance($assetmaintenance);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformMaintenance(Maintenance $assetmaintenance)
    {
        $array = [
            'id' => (int) $assetmaintenance->id,
            'asset' => ($assetmaintenance->asset) ? [
                'id' => (int) $assetmaintenance->asset->id,
                'name' => ($assetmaintenance->asset->name) ? e($assetmaintenance->asset->name) : null,
                'asset_tag' => e($assetmaintenance->asset->asset_tag),
                'serial' => e($assetmaintenance->asset->serial),
                'deleted_at' => Helper::getFormattedDateObject($assetmaintenance->asset->deleted_at, 'datetime'),
                'created_at' => Helper::getFormattedDateObject($assetmaintenance->asset->created_at, 'datetime'),
                'updated_at' => Helper::getFormattedDateObject($assetmaintenance->asset->updated_at, 'datetime'),
            ] : null,
            'image' => ($assetmaintenance->image != '') ? Storage::disk('public')->url('maintenances/'.e($assetmaintenance->image)) : null,
            'model' => (($assetmaintenance->asset) && ($assetmaintenance->asset->model)) ? [
                'id' => (int) $assetmaintenance->asset->model->id,
                'name' => ($assetmaintenance->asset->model->name) ? e($assetmaintenance->asset->model->name) : null,
                'model_number' => ($assetmaintenance->asset->model->model_number) ? e($assetmaintenance->asset->model->model_number) : null,
            ] : null,
            'status_label' => (($assetmaintenance->asset) && ($assetmaintenance->asset->status)) ? [
                'id' => (int) $assetmaintenance->asset->status->id,
                'name' => e($assetmaintenance->asset->status->name),
                'status_type' => e($assetmaintenance->asset->status->getStatuslabelType()),
                'status_meta' => e($assetmaintenance->asset->present()->statusMeta),
            ] : null,
            'assigned_to' => (new AssetsTransformer)->transformAssignedTo($assetmaintenance->asset),
            'company' => (($assetmaintenance->asset) && ($assetmaintenance->asset->company)) ? [
                'id' => (int) $assetmaintenance->asset->company->id,
                'name' => ($assetmaintenance->asset->company->name) ? e($assetmaintenance->asset->company->name) : null,

            ] : null,
            'name' => ($assetmaintenance->name) ? e($assetmaintenance->name) : null,
            'title' => ($assetmaintenance->name) ? e($assetmaintenance->name) : null, // legacy to not change the shape of the API
            'location' => (($assetmaintenance->asset) && ($assetmaintenance->asset->location)) ? [
                'id' => (int) $assetmaintenance->asset->location->id,
                'name' => e($assetmaintenance->asset->location->name),

            ] : null,
            'rtd_location' => (($assetmaintenance->asset) && ($assetmaintenance->asset->defaultLoc)) ? [
                'id' => (int) $assetmaintenance->asset->defaultLoc->id,
                'name' => e($assetmaintenance->asset->defaultLoc->name),
            ] : null,
            'notes' => ($assetmaintenance->notes) ? Helper::parseEscapedMarkedownInline($assetmaintenance->notes) : null,
            'supplier' => ($assetmaintenance->supplier) ? [
                'id' => $assetmaintenance->supplier->id,
                'name' => e($assetmaintenance->supplier->name),
            ] : null,
            'url' => ($assetmaintenance->url) ? e($assetmaintenance->url) : null,
            'cost' => Helper::formatCurrencyOutput($assetmaintenance->cost),
            'asset_maintenance_type' => e($assetmaintenance->asset_maintenance_type),
            'start_date' => Helper::getFormattedDateObject($assetmaintenance->start_date, 'date'),
            'asset_maintenance_time' => (int) $assetmaintenance->asset_maintenance_time,
            'expected_completion_date' => Helper::getFormattedDateObject($assetmaintenance->expected_completion_date, 'date'),
            // Legacy alias for `expected_completion_date` (the column was
            // renamed to match the "Expected Completion" UI label). Kept
            // in the payload so API v1 consumers reading .completion_date
            // don't break. Both keys point at the same underlying value.
            'completion_date' => Helper::getFormattedDateObject($assetmaintenance->expected_completion_date, 'date'),
            'user_id' => ($assetmaintenance->adminuser) ? [
                'id' => $assetmaintenance->adminuser->id,
                'name' => e($assetmaintenance->adminuser->display_name),
            ] : null, // legacy to not change the shape of the API
            'created_by' => ($assetmaintenance->adminuser) ? [
                'id' => (int) $assetmaintenance->adminuser->id,
                'name' => e($assetmaintenance->adminuser->display_name),
            ] : null,
            'maintenance_type' => $assetmaintenance->maintenanceType
                ? e($assetmaintenance->maintenanceType->name)
                : null,
            'responsible_party' => ($assetmaintenance->responsibleParty) ? [
                'id' => (int) $assetmaintenance->responsibleParty->id,
                'name' => e($assetmaintenance->responsibleParty->display_name),
            ] : null,
            'checked_out_to_at_creation' => $assetmaintenance->checked_out_to_id ? [
                'id' => (int) $assetmaintenance->checked_out_to_id,
                'type' => $assetmaintenance->checked_out_to_type,
            ] : null,
            'completed_at' => Helper::getFormattedDateObject($assetmaintenance->completed_at, 'datetime'),
            'completed_by' => ($assetmaintenance->completedByUser) ? [
                'id' => (int) $assetmaintenance->completedByUser->id,
                'name' => e($assetmaintenance->completedByUser->display_name),
            ] : null,
            'created_at' => Helper::getFormattedDateObject($assetmaintenance->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($assetmaintenance->updated_at, 'datetime'),
            'is_warranty' => (bool) $assetmaintenance->is_warranty,

        ];

        $permissions_array['available_actions'] = [
            'update' => (Gate::allows('update', Asset::class) && ((($assetmaintenance->asset) && $assetmaintenance->asset->deleted_at == ''))) ? true : false,
            'delete' => Gate::allows('delete', Asset::class),
            'complete' => Gate::allows('update', Asset::class) && ! $assetmaintenance->completed_at,
        ];

        $array += $permissions_array;

        return $array;
    }

    public function transformMaintenancesFlat(Collection $maintenances, $total)
    {
        $array = [];
        foreach ($maintenances as $assetmaintenance) {
            $array[] = self::transformMaintenanceForReport($assetmaintenance);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformMaintenanceForReport(Maintenance $assetmaintenance)
    {
        $array = [
            'id' => (int) $assetmaintenance->id,
            'asset_name' => ($assetmaintenance->asset->name) ? e($assetmaintenance->asset->name) : null,
            'asset_tag' => ($assetmaintenance->asset->asset_tag) ? e($assetmaintenance->asset->asset_tag) : null,
            'serial' => ($assetmaintenance->asset?->serial) ? e($assetmaintenance->asset->serial) : null,
            'image' => ($assetmaintenance->image != '') ? Storage::disk('public')->url('maintenances/'.e($assetmaintenance->image)) : null,
            'model' => ($assetmaintenance->asset?->model?->name) ? e($assetmaintenance->asset?->model?->name) : null,
            'model_number' => ($assetmaintenance->asset?->model?->model_number) ? e($assetmaintenance->asset?->model?->model_number) : null,
            'status_label' => ($assetmaintenance->asset?->status) ? e($assetmaintenance->asset?->status?->display_name) : null,
            'assigned_to' => ($assetmaintenance->asset?->assigned) ? e($assetmaintenance->asset?->assigned?->display_name) : null,
            'company' => ($assetmaintenance->asset?->company?->name) ? e($assetmaintenance->asset->company->name) : null,
            'name' => ($assetmaintenance->name) ? e($assetmaintenance->name) : null,
            'title' => ($assetmaintenance->name) ? e($assetmaintenance->name) : null, // legacy to not change the shape of the API
            'location' => (($assetmaintenance->asset) && ($assetmaintenance->asset->location)) ? e($assetmaintenance->asset->location->name) : null,
            'notes' => ($assetmaintenance->notes) ? Helper::parseEscapedMarkedownInline($assetmaintenance->notes) : null,
            'supplier' => ($assetmaintenance->supplier) ? e($assetmaintenance->supplier?->name) : null,
            'url' => ($assetmaintenance->url) ? e($assetmaintenance->url) : null,
            'cost' => Helper::formatCurrencyOutput($assetmaintenance->cost),
            'maintenance_type' => $assetmaintenance->maintenanceType
                ? e($assetmaintenance->maintenanceType->name)
                : null,
            'asset_maintenance_type' => e($assetmaintenance->asset_maintenance_type),
            'start_date' => Helper::getFormattedDateObject($assetmaintenance->start_date, 'date'),
            'asset_maintenance_time' => $assetmaintenance->asset_maintenance_time,
            'expected_completion_date' => Helper::getFormattedDateObject($assetmaintenance->expected_completion_date, 'date'),
            // Legacy alias for `expected_completion_date`; see the
            // matching comment in transformMaintenance() above.
            'completion_date' => Helper::getFormattedDateObject($assetmaintenance->expected_completion_date, 'date'),
            'responsible_party' => ($assetmaintenance->responsibleParty) ? [
                'id' => (int) $assetmaintenance->responsibleParty->id,
                'name' => e($assetmaintenance->responsibleParty->display_name),
            ] : null,
            'checked_out_to_at_creation' => ($assetmaintenance->checkedOutTo) ? e($assetmaintenance->checkedOutTo->display_name) : null,
            'completed_at' => Helper::getFormattedDateObject($assetmaintenance->completed_at, 'datetime'),
            'completed_by' => ($assetmaintenance->completedByUser) ? [
                'id' => (int) $assetmaintenance->completedByUser->id,
                'name' => e($assetmaintenance->completedByUser->display_name),
            ] : null,
            'created_by' => ($assetmaintenance->adminuser) ? e($assetmaintenance->adminuser->display_name) : null,
            'created_at' => Helper::getFormattedDateObject($assetmaintenance->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($assetmaintenance->updated_at, 'datetime'),
            'is_warranty' => (bool) $assetmaintenance->is_warranty,

        ];

        return $array;
    }
}
