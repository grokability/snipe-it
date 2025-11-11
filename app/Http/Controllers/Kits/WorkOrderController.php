<?php

namespace App\Http\Controllers\Kits;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\MaintenanceSchedule;
use App\Models\MaintenanceHistory;
use App\Models\Asset;
use App\Models\Actionlog;
use App\Models\PredefinedKit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = WorkOrder::with(['asset', 'assignedUser', 'createdByUser', 'kit']);
        
        // Handle status filter (including special 'overdue' filter)
        if ($request->status) {
            if ($request->status === 'overdue') {
                // Overdue: scheduled_end is past and status is pending or in_progress
                $query->where(function ($q) {
                    $q->whereIn('status', ['pending', 'in_progress'])
                      ->where('scheduled_end', '<', now());
                });
            } else {
                $query->where('status', $request->status);
            }
        }

        // Other filters
        $query->when($request->priority, function ($q, $priority) {
                return $q->where('priority', $priority);
            })
            ->when($request->type, function ($q, $type) {
                return $q->where('type', $type);
            })
            ->when($request->search, function ($q, $search) {
                return $q->where(function ($subQuery) use ($search) {
                    $subQuery->where('title', 'like', "%{$search}%")
                      ->orWhere('work_order_number', 'like', "%{$search}%");
                });
            });
        
        $workOrders = $query->orderBy('created_at', 'desc')->paginate(50);

        $pendingCount = WorkOrder::pending()->count();
        $inProgressCount = WorkOrder::inProgress()->count();
        $onHoldCount = WorkOrder::where('status', 'on_hold')->count();
        $overdueCount = WorkOrder::overdue()->count();
        $completedCount = WorkOrder::where('status', 'completed')->count();
        $cancelledCount = WorkOrder::where('status', 'cancelled')->count();

        return view('kits.workorders.index', compact('workOrders', 'pendingCount', 'inProgressCount', 'onHoldCount', 'overdueCount', 'completedCount', 'cancelledCount'));
    }

    public function create()
    {
        $assets = Asset::select(['id', 'name', 'asset_tag'])->orderBy('name')->get();
        $kits = PredefinedKit::select(['id', 'name'])->orderBy('name')->get();
        $users = User::select(['id', 'first_name', 'last_name'])->orderBy('first_name')->get();
        $schedules = MaintenanceSchedule::select(['id', 'title', 'asset_id'])->active()->get();
        $item = new WorkOrder(); // Required by layouts/edit-form
        return view('kits.workorders.create', compact('assets', 'kits', 'users', 'schedules', 'item'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'maintenance_schedule_id' => 'nullable|exists:maintenance_schedules,id',
            'predefined_kit_id' => 'nullable|exists:kits,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:preventive,corrective,inspection,emergency',
            'priority' => 'required|in:low,medium,high,critical',
            'assigned_to' => 'nullable|exists:users,id',
            'scheduled_start' => 'nullable|date',
            'scheduled_end' => 'nullable|date|after:scheduled_start',
            'estimated_duration' => 'nullable|integer|min:1',
            'estimated_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['status'] = 'pending';

        $workOrder = WorkOrder::create($validated);

        // Log action: created
        $this->logAction($workOrder, 'created', 'Work order created');

        return redirect()->route('maintenance.workorders.index')
            ->with('success', 'Work order created successfully. Work Order #: ' . $workOrder->work_order_number);
    }

    public function show(WorkOrder $workorder)
    {
    $workorder->load(['asset', 'maintenanceSchedule', 'kit', 'assignedUser', 'createdByUser', 'completedByUser', 'maintenanceHistory']);
        $users = User::select(['id', 'first_name', 'last_name'])->orderBy('first_name')->get();
        return view('kits.workorders.show', compact('workorder', 'users'));
    }

    public function edit(WorkOrder $workorder)
    {
        $item = $workorder;
        $assets = Asset::select(['id', 'name', 'asset_tag'])->orderBy('name')->get();
        $kits = PredefinedKit::select(['id', 'name'])->orderBy('name')->get();
        $users = User::select(['id', 'first_name', 'last_name'])->orderBy('first_name')->get();
        $schedules = MaintenanceSchedule::select(['id', 'title', 'asset_id'])->active()->get();
        $suppliers = \App\Models\Supplier::orderBy('name')->get();

        return view('kits.workorders.edit', compact('item', 'workorder', 'assets', 'kits', 'users', 'schedules', 'suppliers'));
    }

    public function update(Request $request, WorkOrder $workorder)
    {
        $originalStatus = $workorder->status;
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'maintenance_schedule_id' => 'nullable|exists:maintenance_schedules,id',
            'predefined_kit_id' => 'nullable|exists:kits,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:preventive,corrective,inspection,emergency',
            'priority' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:pending,in_progress,on_hold,completed,cancelled',
            'assigned_to' => 'nullable|exists:users,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'scheduled_start' => 'nullable|date',
            'scheduled_end' => 'nullable|date|after:scheduled_start',
            'actual_start' => 'nullable|date',
            'actual_end' => 'nullable|date|required_if:status,completed',
            'estimated_duration' => 'nullable|integer|min:1',
            'actual_duration' => 'nullable|integer|min:1|required_if:status,completed',
            'estimated_cost' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'work_performed' => 'nullable|string|required_if:status,completed',
            'parts_used' => 'nullable|string',
            'notes' => 'nullable|string',
            'completed_by' => 'nullable|exists:users,id|required_if:status,completed',
        ]);

        $workorder->update($validated);

        // Log action: updated
        $this->logAction($workorder, 'updated', 'Work order updated'.(isset($validated['status']) ? ' (status: '.$validated['status'].')' : ''));

        // Status-specific logs
        if ($originalStatus !== $workorder->status) {
            $this->logAction($workorder, 'status_changed', 'Status changed from '.$originalStatus.' to '.$workorder->status);
        }

        if ($validated['status'] === 'completed' && !$workorder->maintenanceHistory) {
            $this->createMaintenanceHistory($workorder, $validated['completed_by'] ?? null);
            $this->logAction($workorder, 'completed', 'Work order marked completed');
            
            if ($workorder->maintenanceSchedule) {
                $workorder->maintenanceSchedule->last_completed_date = now();
                $workorder->maintenanceSchedule->save();
                $workorder->maintenanceSchedule->updateNextDueDate();
            }
        }

        return redirect()->route('maintenance.workorders.show', $workorder)
            ->with('success', 'Work order updated successfully.');
    }

    public function destroy(WorkOrder $workorder)
    {
        $workorder->delete();

        $this->logAction($workorder, 'deleted', 'Work order soft-deleted');

        return redirect()->route('maintenance.workorders.index')
            ->with('success', 'Work order deleted successfully.');
    }

    public function deleted()
    {
        $workOrders = WorkOrder::onlyTrashed()->with(['asset','assignedUser'])->orderBy('deleted_at','desc')->paginate(50);
        return view('kits.workorders.deleted', compact('workOrders'));
    }

    public function restore($id)
    {
        $workOrder = WorkOrder::withTrashed()->findOrFail($id);
        if ($workOrder->trashed()) {
            $workOrder->restore();
            $this->logAction($workOrder, 'restored', 'Work order restored');
            return redirect()->route('maintenance.workorders.deleted')->with('success','Work order restored successfully.');
        }
        return redirect()->route('maintenance.workorders.show',$workOrder)->with('info','Work order is not deleted.');
    }

    public function complete(Request $request, WorkOrder $workorder)
    {
        // This method is no longer used - completion is handled through update() method
        return redirect()->route('maintenance.workorders.show', $workorder)
            ->with('info', 'Please use the Save Changes or Mark as Completed button on the work order page.');
    }

    public function updateStatus(Request $request, WorkOrder $workorder)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,on_hold,cancelled',
        ]);

        $statusMessages = [
            'pending' => 'Work order status changed to Pending.',
            'in_progress' => 'Work order status changed to In Progress.',
            'on_hold' => 'Work order status changed to On Hold.',
            'cancelled' => 'Work order has been cancelled.',
        ];

        $workorder->update([
            'status' => $validated['status'],
        ]);

        // Set actual_start when changing to in_progress
        if ($validated['status'] === 'in_progress' && !$workorder->actual_start) {
            $workorder->update(['actual_start' => now()]);
        }

        $this->logAction($workorder, 'status_changed', 'Status changed to '.$validated['status']);

        return redirect()->route('maintenance.workorders.show', $workorder)
            ->with('success', $statusMessages[$validated['status']]);
    }

    private function createMaintenanceHistory(WorkOrder $workorder, $completedBy = null)
    {
        MaintenanceHistory::create([
            'asset_id' => $workorder->asset_id,
            'work_order_id' => $workorder->id,
            'maintenance_schedule_id' => $workorder->maintenance_schedule_id,
            'predefined_kit_id' => $workorder->predefined_kit_id,
            'title' => $workorder->title,
            'description' => $workorder->description,
            'type' => $workorder->type,
            'performed_at' => $workorder->actual_end ?? now(),
            'performed_by' => $completedBy ?? $workorder->assigned_to ?? Auth::id(),
            'duration' => $workorder->actual_duration,
            'cost' => $workorder->actual_cost,
            'work_performed' => $workorder->work_performed,
            'parts_used' => $workorder->parts_used,
            'outcome' => 'success',
            'notes' => $workorder->notes,
        ]);
    }

    public function activity(WorkOrder $workorder)
    {
        return view('kits.workorders.activity', compact('workorder'));
    }

    public function allActivity()
    {
        return view('kits.workorders.all-activity');
    }

    private function logAction(WorkOrder $workorder, string $actionType, string $note): void
    {
        $log = new Actionlog();
        $log->item_type = WorkOrder::class;
        $log->item_id = $workorder->id;
        $log->created_by = Auth::id();
        $log->note = $note;
        $log->logaction($actionType);
    }
}

