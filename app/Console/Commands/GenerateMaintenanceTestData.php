<?php

namespace App\Console\Commands;

use App\Models\MaintenanceSchedule;
use App\Models\WorkOrder;
use App\Models\MaintenanceHistory;
use App\Models\Asset;
use App\Models\User;
use App\Models\PredefinedKit;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateMaintenanceTestData extends Command
{
    protected $signature = 'maintenance:generate-test-data {--count=20}';
    protected $description = 'Generate test data for maintenance schedules, work orders, and history';

    public function handle()
    {
        $count = $this->option('count');
        
        $this->info("Generating {$count} maintenance test records...");
        
        // Get existing assets and users - use withoutGlobalScopes to bypass soft deletes
        $assets = Asset::query()->get();
        $users = User::query()->get();
        $kits = PredefinedKit::query()->get();
        
        if ($assets->isEmpty()) {
            $this->error('No assets found! Please create some assets first.');
            $this->info('Run: php artisan assets:generate-test --count=30');
            return 1;
        }
        
        if ($users->isEmpty()) {
            $this->error('No users found! Please create some users first.');
            return 1;
        }
        
        $this->info("Found {$assets->count()} assets and {$users->count()} users");
        
        $frequencies = ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'];
        $priorities = ['low', 'medium', 'high', 'critical'];
        $statuses = ['active', 'active', 'active', 'paused', 'completed']; // More active ones
        $workOrderStatuses = ['pending', 'in_progress', 'completed', 'on_hold', 'cancelled'];
        $workOrderTypes = ['preventive', 'corrective', 'inspection', 'emergency'];
        
        $createdSchedules = 0;
        $createdWorkOrders = 0;
        $createdHistory = 0;
        
        // Generate Maintenance Schedules
        $this->info("\n📅 Creating Maintenance Schedules...");
        for ($i = 0; $i < $count; $i++) {
            $asset = $assets->random();
            $frequency = $frequencies[array_rand($frequencies)];
            $startDate = Carbon::now()->subDays(rand(0, 90));
            
            $schedule = MaintenanceSchedule::create([
                'asset_id' => $asset->id,
                'predefined_kit_id' => $kits->isNotEmpty() && rand(0, 1) ? $kits->random()->id : null,
                'title' => $this->generateTitle($asset->name, $frequency),
                'description' => $this->generateDescription($frequency),
                'frequency' => $frequency,
                'frequency_interval' => rand(1, 3),
                'start_date' => $startDate,
                'next_due_date' => $this->calculateNextDue($startDate, $frequency, rand(1, 3)),
                'last_completed_date' => rand(0, 1) ? $startDate->copy()->subDays(rand(7, 30)) : null,
                'priority' => $priorities[array_rand($priorities)],
                'status' => $statuses[array_rand($statuses)],
                'assigned_to' => rand(0, 1) ? $users->random()->id : null,
                'estimated_duration' => rand(30, 480), // 30 mins to 8 hours
                'notes' => rand(0, 1) ? 'Auto-generated test data for schedule #' . ($i + 1) : null,
            ]);
            
            $createdSchedules++;
            $this->line("  ✓ Created schedule: {$schedule->title}");
            
            // Create some work orders for active schedules
            if ($schedule->status === 'active' && rand(0, 2) > 0) {
                $woCount = rand(1, 3);
                for ($j = 0; $j < $woCount; $j++) {
                    $scheduledStart = Carbon::now()->subDays(rand(0, 60));
                    $workOrder = WorkOrder::create([
                        'maintenance_schedule_id' => $schedule->id,
                        'asset_id' => $schedule->asset_id,
                        'predefined_kit_id' => $schedule->predefined_kit_id,
                        'work_order_number' => WorkOrder::generateWorkOrderNumber(),
                        'title' => "WO: " . $schedule->title,
                        'description' => "Work order for scheduled maintenance: {$schedule->title}",
                        'type' => $workOrderTypes[array_rand($workOrderTypes)],
                        'status' => $workOrderStatuses[array_rand($workOrderStatuses)],
                        'priority' => $schedule->priority,
                        'assigned_to' => $schedule->assigned_to ?? $users->random()->id,
                        'created_by' => $users->random()->id,
                        'scheduled_start' => $scheduledStart,
                        'scheduled_end' => $scheduledStart->copy()->addHours(rand(1, 8)),
                        'actual_start' => rand(0, 1) ? $scheduledStart->copy()->addMinutes(rand(-30, 30)) : null,
                        'actual_end' => null,
                        'estimated_cost' => rand(100, 5000),
                        'actual_cost' => null,
                        'notes' => "Auto-generated work order #" . ($createdWorkOrders + 1),
                    ]);
                    
                    $createdWorkOrders++;
                    $this->line("    ↳ Created work order: {$workOrder->work_order_number}");
                    
                    // Create history for completed work orders
                    if ($workOrder->status === 'completed') {
                        $actualStart = $scheduledStart->copy()->addMinutes(rand(-30, 30));
                        $duration = rand(30, 360);
                        $actualEnd = $actualStart->copy()->addMinutes($duration);
                        
                        $workOrder->update([
                            'actual_start' => $actualStart,
                            'actual_end' => $actualEnd,
                            'actual_cost' => rand(100, 6000),
                        ]);
                        
                        $history = MaintenanceHistory::create([
                            'work_order_id' => $workOrder->id,
                            'maintenance_schedule_id' => $schedule->id,
                            'asset_id' => $schedule->asset_id,
                            'predefined_kit_id' => $schedule->predefined_kit_id,
                            'performed_by' => $workOrder->assigned_to,
                            'performed_at' => $actualEnd,
                            'duration_minutes' => $duration,
                            'outcome' => rand(0, 9) > 0 ? 'success' : 'issues_found',
                            'components_replaced' => $this->generateComponents(),
                            'consumables_used' => $this->generateConsumables(),
                            'cost' => $workOrder->actual_cost,
                            'notes' => $this->generateHistoryNotes(),
                        ]);
                        
                        // Update schedule last completed date
                        $schedule->update([
                            'last_completed_date' => $actualEnd,
                            'next_due_date' => $this->calculateNextDue($actualEnd, $schedule->frequency, $schedule->frequency_interval),
                        ]);
                        
                        $createdHistory++;
                        $this->line("      ↳ Created history record for completed work");
                    }
                }
            }
        }
        
        $this->info("\n✅ Test Data Generation Complete!");
        $this->table(
            ['Type', 'Count'],
            [
                ['Maintenance Schedules', $createdSchedules],
                ['Work Orders', $createdWorkOrders],
                ['History Records', $createdHistory],
            ]
        );
        
        return 0;
    }
    
    private function generateTitle($assetName, $frequency)
    {
        $titles = [
            'daily' => ['Daily Inspection', 'Daily Check', 'Daily Maintenance'],
            'weekly' => ['Weekly Service', 'Weekly Inspection', 'Weekly Maintenance'],
            'monthly' => ['Monthly Service', 'Monthly Inspection', 'Preventive Maintenance'],
            'quarterly' => ['Quarterly Review', 'Quarterly Service', 'Quarterly Inspection'],
            'yearly' => ['Annual Service', 'Yearly Inspection', 'Annual Maintenance'],
        ];
        
        $title = $titles[$frequency][array_rand($titles[$frequency])];
        return "{$title} - {$assetName}";
    }
    
    private function generateDescription($frequency)
    {
        $descriptions = [
            'daily' => 'Daily operational check and basic maintenance tasks',
            'weekly' => 'Weekly inspection and preventive maintenance procedures',
            'monthly' => 'Monthly comprehensive maintenance and performance check',
            'quarterly' => 'Quarterly deep inspection and component verification',
            'yearly' => 'Annual complete system overhaul and certification',
        ];
        
        return $descriptions[$frequency];
    }
    
    private function calculateNextDue($startDate, $frequency, $interval)
    {
        $date = Carbon::parse($startDate);
        
        switch ($frequency) {
            case 'daily':
                return $date->addDays($interval);
            case 'weekly':
                return $date->addWeeks($interval);
            case 'monthly':
                return $date->addMonths($interval);
            case 'quarterly':
                return $date->addMonths($interval * 3);
            case 'yearly':
                return $date->addYears($interval);
            default:
                return $date->addMonths($interval);
        }
    }
    
    private function generateComponents()
    {
        $components = [
            ['name' => 'Air Filter', 'part_number' => 'AF-' . rand(1000, 9999)],
            ['name' => 'Oil Filter', 'part_number' => 'OF-' . rand(1000, 9999)],
            ['name' => 'Spark Plug', 'part_number' => 'SP-' . rand(1000, 9999)],
            ['name' => 'Belt', 'part_number' => 'BT-' . rand(1000, 9999)],
            ['name' => 'Battery', 'part_number' => 'BAT-' . rand(1000, 9999)],
        ];
        
        if (rand(0, 1)) {
            $count = rand(1, 3);
            return json_encode(array_slice($components, 0, $count));
        }
        
        return null;
    }
    
    private function generateConsumables()
    {
        $consumables = [
            ['name' => 'Engine Oil', 'quantity' => rand(1, 5) . 'L'],
            ['name' => 'Grease', 'quantity' => rand(100, 500) . 'g'],
            ['name' => 'Coolant', 'quantity' => rand(1, 3) . 'L'],
            ['name' => 'Cleaning Solution', 'quantity' => rand(1, 2) . 'L'],
        ];
        
        if (rand(0, 1)) {
            $count = rand(1, 3);
            return json_encode(array_slice($consumables, 0, $count));
        }
        
        return null;
    }
    
    private function generateHistoryNotes()
    {
        $notes = [
            'Maintenance completed successfully. All systems operating normally.',
            'Minor issues found and resolved. Equipment functioning properly.',
            'Routine maintenance performed. No issues detected.',
            'Preventive maintenance completed as scheduled.',
            'All checks passed. Equipment in good condition.',
            'Some wear detected but within acceptable limits. Monitoring required.',
        ];
        
        return $notes[array_rand($notes)];
    }
}
