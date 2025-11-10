<?php

namespace App\Http\Controllers\Kits;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceSchedule;
use App\Models\Asset;
use App\Models\PredefinedKit;
use App\Models\User;
use Illuminate\Http\Request;

class SchedulerController extends Controller
{
    public function index(Request $request)
    {
        // Get all assets for filter dropdown
        $assets = Asset::select(['id', 'name', 'asset_tag'])->orderBy('name')->get();
        
        // Default sorting

        // 默认按创建日期降序排列
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');

        // Validate sort column
        $allowedSorts = ['title', 'asset_id', 'frequency', 'next_due_date', 'priority', 'status', 'created_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }

        // Validate order
        if (!in_array($order, ['asc', 'desc'])) {
            $order = 'desc';
        }

        $query = MaintenanceSchedule::with(['asset', 'kit', 'assignedUser']);

        // Handle special filters (overdue, upcoming)
        if ($request->filter === 'overdue') {
            $query->overdue();
        } elseif ($request->filter === 'upcoming') {
            $query->upcoming(7); // 7 days
        } else {
            // Normal status filter
            $query->when($request->status, function ($q, $status) {
                return $q->where('status', $status);
            });
        }

        // Other filters
        $query->when($request->priority, function ($q, $priority) {
                return $q->where('priority', $priority);
            })
            ->when($request->asset_id, function ($q, $assetId) {
                return $q->where('asset_id', $assetId);
            })
            ->when($request->search, function ($q, $search) {
                return $q->where('title', 'like', "%{$search}%");
            });

        $schedules = $query->orderBy($sort, $order)
            ->paginate(50)
            ->appends($request->all()); // Preserve query parameters in pagination

        // Statistics
        $overdueCount = MaintenanceSchedule::overdue()->count();
        $upcomingCount = MaintenanceSchedule::upcoming()->count();
        $activeCount = MaintenanceSchedule::where('status', 'active')->count();
        $pausedCount = MaintenanceSchedule::where('status', 'paused')->count();

        return view('kits.scheduler.index', compact('schedules', 'overdueCount', 'upcomingCount', 'activeCount', 'pausedCount', 'assets'));
    }

    public function create()
    {
        $assets = Asset::select(['id', 'name', 'asset_tag'])->orderBy('name')->get();
        $kits = PredefinedKit::select(['id', 'name'])->orderBy('name')->get();
        $users = User::select(['id', 'first_name', 'last_name'])->orderBy('first_name')->get();
        $suppliers = \App\Models\Supplier::select(['id', 'name'])->orderBy('name')->get();
        $item = new MaintenanceSchedule(); // Required by layouts/edit-form

        return view('kits.scheduler.create', compact('assets', 'kits', 'users', 'suppliers', 'item'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'predefined_kit_id' => 'nullable|exists:kits,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly,yearly',
            'frequency_interval' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'priority' => 'required|in:low,medium,high,critical',
            'assigned_to' => 'nullable|exists:users,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'estimated_duration' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $validated['next_due_date'] = $validated['start_date'];
        $validated['status'] = 'active';

        $schedule = MaintenanceSchedule::create($validated);

        return redirect()->route('maintenance.scheduler.index')
            ->with('success', 'Maintenance schedule created successfully.');
    }

    public function show(MaintenanceSchedule $schedule)
    {
        $schedule->load(['asset', 'kit', 'assignedUser', 'workOrders', 'maintenanceHistory']);
        return view('kits.scheduler.show', compact('schedule'));
    }

    public function edit(MaintenanceSchedule $schedule)
    {
        $assets = Asset::select(['id', 'name', 'asset_tag'])->orderBy('name')->get();
        $kits = PredefinedKit::select(['id', 'name'])->orderBy('name')->get();
        $users = User::select(['id', 'first_name', 'last_name'])->orderBy('first_name')->get();
        $suppliers = \App\Models\Supplier::select(['id', 'name'])->orderBy('name')->get();
        $item = $schedule; // Required by layouts/edit-form

        return view('kits.scheduler.edit', compact('schedule', 'assets', 'kits', 'users', 'suppliers', 'item'));
    }

    public function update(Request $request, MaintenanceSchedule $schedule)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'predefined_kit_id' => 'nullable|exists:kits,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly,yearly',
            'frequency_interval' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'next_due_date' => 'required|date',
            'priority' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:active,paused,completed,cancelled',
            'assigned_to' => 'nullable|exists:users,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'estimated_duration' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $schedule->update($validated);

        return redirect()->route('maintenance.scheduler.index')
            ->with('success', 'Maintenance schedule updated successfully.');
    }

    public function destroy(MaintenanceSchedule $schedule)
    {
        $schedule->delete();

        return redirect()->route('maintenance.scheduler.index')
            ->with('success', 'Maintenance schedule deleted successfully.');
    }

    public function overdue()
    {
        $schedules = MaintenanceSchedule::overdue()
            ->with(['asset', 'kit', 'assignedUser'])
            ->orderBy('next_due_date', 'asc')
            ->paginate(50);

        return view('kits.scheduler.overdue', compact('schedules'));
    }

    public function upcoming()
    {
        $schedules = MaintenanceSchedule::upcoming(30)
            ->with(['asset', 'kit', 'assignedUser'])
            ->orderBy('next_due_date', 'asc')
            ->paginate(50);

        return view('kits.scheduler.upcoming', compact('schedules'));
    }
}

