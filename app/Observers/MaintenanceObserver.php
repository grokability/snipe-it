<?php

namespace App\Observers;

use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\MaintenanceType;

class MaintenanceObserver
{
    /**
     * Capture the asset's current checkout state before the maintenance record is saved.
     */
    public function creating(Maintenance $maintenance): void
    {
        if ($maintenance->asset_id && $asset = Asset::find($maintenance->asset_id)) {
            $maintenance->checked_out_to_id = $asset->assigned_to;
            $maintenance->checked_out_to_type = $asset->assigned_type;
        }

        $this->syncLegacyMaintenanceType($maintenance);
    }

    /**
     * Listen to the Maintenance updating event.
     */
    public function updating(Maintenance $maintenance)
    {
        $this->syncLegacyMaintenanceType($maintenance);
    }

    private function syncLegacyMaintenanceType(Maintenance $maintenance): void
    {
        if ($maintenance->maintenance_type_id && ! $maintenance->asset_maintenance_type) {
            $type = MaintenanceType::find($maintenance->maintenance_type_id);
            if ($type) {
                $maintenance->asset_maintenance_type = $type->name;
            }
        }
    }
}
