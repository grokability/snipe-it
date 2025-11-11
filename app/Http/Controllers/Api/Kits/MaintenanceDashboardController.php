<?php

namespace App\Http\Controllers\Api\Kits;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\MaintenanceSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class MaintenanceDashboardController extends Controller
{
    /**
     * Get maintenance todo items for dashboard
     *
     * @return JsonResponse
     */
    public function getTodoItems(Request $request): JsonResponse
    {
        try {
            $items = [];
            $sevenDaysFromNow = now()->addDays(7);

            // Get overdue work orders
            $overdueWorkOrders = WorkOrder::whereIn('status', ['pending', 'in_progress', 'on_hold'])
                ->where('scheduled_end', '<', now())
                ->orderBy('scheduled_end', 'asc')
                ->limit(10)
                ->get();

            foreach ($overdueWorkOrders as $wo) {
                $items[] = [
                    'priority' => 'high',
                    'priority_label' => 'HIGH',
                    'priority_class' => 'danger',
                    'task' => $wo->title,
                    'description' => $wo->description ? Str::limit($wo->description, 60) : '',
                    'type' => 'Work Order',
                    'type_class' => 'primary',
                    'type_icon' => 'clipboard-list',
                    'due_date' => $wo->scheduled_end ? $wo->scheduled_end->format('Y-m-d') : 'N/A',
                    'due_date_human' => $wo->scheduled_end ? $wo->scheduled_end->diffForHumans() : '',
                    'is_overdue' => true,
                    'status' => ucfirst(str_replace('_', ' ', $wo->status)),
                    'status_class' => $this->getWorkOrderStatusClass($wo->status),
                    'url' => route('maintenance.workorders.show', $wo->id),
                    'row_class' => 'danger',
                ];
            }

            // Get upcoming work orders (next 7 days)
            $upcomingWorkOrders = WorkOrder::whereIn('status', ['pending', 'in_progress', 'on_hold'])
                ->where('scheduled_end', '>=', now())
                ->where('scheduled_end', '<=', $sevenDaysFromNow)
                ->orderBy('scheduled_end', 'asc')
                ->limit(15)
                ->get();

            foreach ($upcomingWorkOrders as $wo) {
                $items[] = [
                    'priority' => 'medium',
                    'priority_label' => 'MEDIUM',
                    'priority_class' => 'warning',
                    'task' => $wo->title,
                    'description' => $wo->description ? Str::limit($wo->description, 60) : '',
                    'type' => 'Work Order',
                    'type_class' => 'primary',
                    'type_icon' => 'clipboard-list',
                    'due_date' => $wo->scheduled_end ? $wo->scheduled_end->format('Y-m-d') : 'No due date',
                    'due_date_human' => $wo->scheduled_end ? $wo->scheduled_end->diffForHumans() : '',
                    'is_overdue' => false,
                    'status' => ucfirst(str_replace('_', ' ', $wo->status)),
                    'status_class' => $this->getWorkOrderStatusClass($wo->status),
                    'url' => route('maintenance.workorders.show', $wo->id),
                    'row_class' => '',
                ];
            }

            // Get overdue schedules (that haven't generated work orders yet)
            $overdueSchedules = MaintenanceSchedule::where('next_due_date', '<', now())
                ->where('status', 'active')
                ->whereDoesntHave('workOrders', function ($query) {
                    $query->whereIn('status', ['pending', 'in_progress', 'on_hold']);
                })
                ->orderBy('next_due_date', 'asc')
                ->limit(5)
                ->get();

            foreach ($overdueSchedules as $schedule) {
                $items[] = [
                    'priority' => 'high',
                    'priority_label' => 'HIGH',
                    'priority_class' => 'danger',
                    'task' => $schedule->title,
                    'description' => $schedule->description ?? '',
                    'type' => 'Schedule',
                    'type_class' => 'success',
                    'type_icon' => 'calendar-alt',
                    'due_date' => $schedule->next_due_date ? $schedule->next_due_date->format('Y-m-d') : 'N/A',
                    'due_date_human' => $schedule->next_due_date ? $schedule->next_due_date->diffForHumans() : '',
                    'is_overdue' => true,
                    'status' => 'Overdue',
                    'status_class' => 'danger',
                    'url' => route('maintenance.scheduler.show', $schedule->id),
                    'row_class' => 'danger',
                ];
            }

            // Get upcoming schedules (next 7 days, without active work orders)
            $upcomingSchedules = MaintenanceSchedule::where('next_due_date', '>=', now())
                ->where('next_due_date', '<=', $sevenDaysFromNow)
                ->where('status', 'active')
                ->whereDoesntHave('workOrders', function ($query) {
                    $query->whereIn('status', ['pending', 'in_progress', 'on_hold']);
                })
                ->orderBy('next_due_date', 'asc')
                ->limit(10)
                ->get();

            foreach ($upcomingSchedules as $schedule) {
                $items[] = [
                    'priority' => 'low',
                    'priority_label' => 'LOW',
                    'priority_class' => 'info',
                    'task' => $schedule->title,
                    'description' => $schedule->description ?? '',
                    'type' => 'Schedule',
                    'type_class' => 'success',
                    'type_icon' => 'calendar-alt',
                    'due_date' => $schedule->next_due_date ? $schedule->next_due_date->format('Y-m-d') : 'N/A',
                    'due_date_human' => $schedule->next_due_date ? $schedule->next_due_date->diffForHumans() : '',
                    'is_overdue' => false,
                    'status' => 'Scheduled',
                    'status_class' => 'info',
                    'url' => route('maintenance.scheduler.show', $schedule->id),
                    'row_class' => '',
                ];
            }

            // Sort items by due date (overdue first, then by date)
            usort($items, function($a, $b) {
                if ($a['is_overdue'] != $b['is_overdue']) {
                    return $b['is_overdue'] - $a['is_overdue']; // overdue first
                }
                return strcmp($a['due_date'], $b['due_date']);
            });

            return response()->json([
                'total' => count($items),
                'rows' => $items,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'total' => 0,
                'rows' => [],
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get Bootstrap class for work order status
     *
     * @param string $status
     * @return string
     */
    private function getWorkOrderStatusClass($status): string
    {
        return match($status) {
            'pending' => 'warning',
            'in_progress' => 'primary',
            'on_hold' => 'default',
            'completed' => 'success',
            'cancelled' => 'default',
            default => 'default',
        };
    }
}
