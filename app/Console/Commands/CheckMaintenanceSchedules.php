<?php

namespace App\Console\Commands;

use App\Models\MaintenanceSchedule;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckMaintenanceSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:check-schedules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check maintenance schedules and create work orders for overdue items';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking maintenance schedules...');

        // Get all active schedules that are overdue or due today
        $overdueSchedules = MaintenanceSchedule::where('status', 'active')
            ->where('next_due_date', '<=', Carbon::today())
            ->whereDoesntHave('workOrders', function ($query) {
                // Don't create new work order if there's already a pending, in_progress, or on_hold work order
                $query->whereIn('status', ['pending', 'in_progress', 'on_hold']);
            })
            ->get();

        if ($overdueSchedules->isEmpty()) {
            $this->info('No overdue maintenance schedules found.');
            return 0;
        }

        $createdCount = 0;

        foreach ($overdueSchedules as $schedule) {
            try {
                // Create work order from schedule
                $workOrder = WorkOrder::create([
                    'maintenance_schedule_id' => $schedule->id,
                    'asset_id' => $schedule->asset_id,
                    'predefined_kit_id' => $schedule->predefined_kit_id,
                    'work_order_number' => WorkOrder::generateWorkOrderNumber(),
                    'title' => $schedule->title,
                    'description' => $schedule->description,
                    'type' => 'preventive', // Auto-generated from schedule
                    'status' => 'pending',
                    'priority' => $schedule->priority,
                    'assigned_to' => $schedule->assigned_to,
                    'created_by' => $schedule->assigned_to ?? 1, // Default to admin if no assignee
                    'scheduled_start' => Carbon::now(),
                    'scheduled_end' => Carbon::now()->addMinutes($schedule->estimated_duration ?? 60),
                    'estimated_cost' => 0,
                    'notes' => "Auto-generated from maintenance schedule: {$schedule->title}",
                ]);

                $createdCount++;
                $this->info("Created work order {$workOrder->work_order_number} for schedule: {$schedule->title}");

            } catch (\Exception $e) {
                $this->error("Failed to create work order for schedule {$schedule->id}: {$e->getMessage()}");
            }
        }

        $this->info("Created {$createdCount} work order(s) from overdue schedules.");

        // Send notification emails (if needed)
        // TODO: Add email notifications to assigned users

        return 0;
    }
}
