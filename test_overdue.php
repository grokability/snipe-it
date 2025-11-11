<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\MaintenanceSchedule;

try {
    echo "Testing overdue query...\n";
    
    $schedules = MaintenanceSchedule::overdue()
        ->with(['asset', 'kit', 'assignedUser'])
        ->orderBy('next_due_date', 'asc')
        ->limit(5)
        ->get();
    
    echo "Found " . $schedules->count() . " overdue schedules\n";
    
    foreach ($schedules as $schedule) {
        echo "\nSchedule: " . $schedule->title . "\n";
        echo "Due date: " . ($schedule->next_due_date ? $schedule->next_due_date->format('Y-m-d') : 'N/A') . "\n";
        echo "Asset: " . ($schedule->asset ? $schedule->asset->name : 'No asset') . "\n";
        echo "Kit: " . ($schedule->kit ? $schedule->kit->name : 'No kit') . "\n";
        echo "Assigned: " . ($schedule->assignedUser ? $schedule->assignedUser->first_name : 'Unassigned') . "\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
