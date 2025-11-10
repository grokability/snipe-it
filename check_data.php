<?php

use App\Models\Asset;
use App\Models\MaintenanceSchedule;
use App\Models\WorkOrder;
use App\Models\MaintenanceHistory;

echo "=== Database Records ===\n";
echo "Assets: " . Asset::count() . "\n";
echo "Schedules: " . MaintenanceSchedule::count() . "\n";
echo "Work Orders: " . WorkOrder::count() . "\n";
echo "History: " . MaintenanceHistory::count() . "\n";

if (Asset::count() > 0) {
    echo "\n=== Sample Assets ===\n";
    foreach (Asset::limit(5)->get() as $asset) {
        echo "- {$asset->asset_tag}: {$asset->name}\n";
    }
}
