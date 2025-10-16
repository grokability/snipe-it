<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Asset;
use App\Models\Maintenance;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Collection;
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
            'id'            => (int) $assetmaintenance->id,
            'asset' => ($assetmaintenance->asset) ? [
                'id' => (int) $assetmaintenance->asset->id,
                'name'=> ($assetmaintenance->asset->name) ? e($assetmaintenance->asset->name) : null,
                'asset_tag'=> e($assetmaintenance->asset->asset_tag),
                'serial'=> e($assetmaintenance->asset->serial),
                'deleted_at'=> Helper::getFormattedDateObject($assetmaintenance->asset->deleted_at, 'datetime'),
                'created_at' => Helper::getFormattedDateObject($assetmaintenance->asset->created_at, 'datetime'),
                'updated_at' => Helper::getFormattedDateObject($assetmaintenance->asset->updated_at, 'datetime'),
            ] : null,
            'image' => ($assetmaintenance->image != '') ? Storage::disk('public')->url('maintenances/'.e($assetmaintenance->image)) : null,
            'model' => (($assetmaintenance->asset) && ($assetmaintenance->asset->model)) ? [
                'id' => (int) $assetmaintenance->asset->model->id,
                'name'=> ($assetmaintenance->asset->model->name) ? e($assetmaintenance->asset->model->name).' '.e($assetmaintenance->asset->model->model_number) : null,
            ] : null,
            'status_label' => (($assetmaintenance->asset) && ($assetmaintenance->asset->assetstatus)) ? [
                'id' => (int) $assetmaintenance->asset->assetstatus->id,
                'name'=> e($assetmaintenance->asset->assetstatus->name),
                'status_type'=> e($assetmaintenance->asset->assetstatus->getStatuslabelType()),
                'status_meta' => e($assetmaintenance->asset->present()->statusMeta),
            ] : null,
            'company' => (($assetmaintenance->asset) && ($assetmaintenance->asset->company)) ? [
                'id' => (int) $assetmaintenance->asset->company->id,
                'name'=> ($assetmaintenance->asset->company->name) ? e($assetmaintenance->asset->company->name) : null,

            ] : null,
            'name'         => ($assetmaintenance->name) ? e($assetmaintenance->name) : null,
            'title'         => ($assetmaintenance->name) ? e($assetmaintenance->name) : null, // legacy to not change the shape of the API
            'location' => (($assetmaintenance->asset) && ($assetmaintenance->asset->location)) ? [
                'id' => (int) $assetmaintenance->asset->location->id,
                'name'=> e($assetmaintenance->asset->location->name),

            ] : null,
            'rtd_location' => (($assetmaintenance->asset) && ($assetmaintenance->asset->defaultLoc)) ? [
                'id' => (int) $assetmaintenance->asset->defaultLoc->id,
                'name'=> e($assetmaintenance->asset->defaultLoc->name),
            ] : null,
            'notes'         => ($assetmaintenance->notes) ? Helper::parseEscapedMarkedownInline($assetmaintenance->notes) : null,
            'supplier'      => ($assetmaintenance->supplier) ?  [
                    'id' => $assetmaintenance->supplier->id,
                    'name'=> e($assetmaintenance->supplier->name)
                    ] : null,
            'url'         => ($assetmaintenance->url) ? e($assetmaintenance->url) : null,
            'cost'          => Helper::formatCurrencyOutput($assetmaintenance->cost),
            'asset_maintenance_type'          => e($assetmaintenance->asset_maintenance_type),
            'start_date'         => Helper::getFormattedDateObject($assetmaintenance->start_date, 'date'),
            'asset_maintenance_time'          => $assetmaintenance->asset_maintenance_time,
            'completion_date'     => Helper::getFormattedDateObject($assetmaintenance->completion_date, 'date'),
            'user_id'    => ($assetmaintenance->adminuser) ? [
                'id' => $assetmaintenance->adminuser->id,
                'name'=> e($assetmaintenance->adminuser->display_name)
            ] : null, // legacy to not change the shape of the API
            'created_by' => ($assetmaintenance->adminuser) ? [
                'id' => (int) $assetmaintenance->adminuser->id,
                'name'=> e($assetmaintenance->adminuser->display_name),
            ] : null,
            'created_at' => Helper::getFormattedDateObject($assetmaintenance->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($assetmaintenance->updated_at, 'datetime'),
            'is_warranty'=> $assetmaintenance->is_warranty,

        ];

        $permissions_array['available_actions'] = [
            'update' => (Gate::allows('update', Asset::class) && ((($assetmaintenance->asset) && $assetmaintenance->asset->deleted_at==''))) ? true : false,
            'delete' => Gate::allows('delete', Asset::class),
        ];

        $array += $permissions_array;

        return $array;
    }
}
