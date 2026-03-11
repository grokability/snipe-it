<?php

namespace App\Custom\Http\Controllers;

use App\Custom\Http\Requests\StoreWorkOrderRequest;
use App\Custom\Models\SparePart;
use App\Custom\Models\WorkOrder;
use App\Custom\Models\WorkOrderPart;
use App\Custom\Services\WorkOrderService;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;

class WorkOrderController extends Controller
{
    public function __construct(protected WorkOrderService $workOrderService)
    {
    }

    /**
     * GET /fom/work-orders — filterable, paginated list.
     */
    public function index(Request $request)
    {
        $query = WorkOrder::with(['asset', 'reportedBy', 'assignedTo', 'location']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('asset_id')) {
            $query->where('asset_id', $request->asset_id);
        }
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        $workOrders = $query->orderByRaw("FIELD(priority, 'kritik', 'yuksek', 'normal')")
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        return view('custom::work-orders.index', compact('workOrders'));
    }

    /**
     * GET /fom/work-orders/create — new work order form.
     */
    public function create()
    {
        $assets = Asset::whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name', 'asset_tag', 'rtd_location_id']);

        $locations = Location::orderBy('name')->get(['id', 'name']);

        $users = User::where('activated', 1)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'username']);

        return view('custom::work-orders.create', compact('assets', 'locations', 'users'));
    }

    /**
     * POST /fom/work-orders — store new work order.
     */
    public function store(StoreWorkOrderRequest $request)
    {
        $data = $request->validated();

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')
                ->store('fom/work-orders', 'public');
        }

        $wo = $this->workOrderService->createFromReport($data, $request->user());

        return redirect()
            ->route('fom.work-orders.show', $wo->id)
            ->with('success', "İş emri oluşturuldu: {$wo->work_order_number}");
    }

    /**
     * GET /fom/work-orders/{id} — work order detail.
     */
    public function show(int $id)
    {
        $workOrder = WorkOrder::with([
            'asset',
            'reportedBy',
            'assignedTo',
            'location',
            'parts.sparePart',
            'handoverItems.handover',
        ])->findOrFail($id);

        $technicians = User::where('activated', 1)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'jobtitle']);

        $spareParts = SparePart::where('quantity_on_hand', '>', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'part_number', 'quantity_on_hand']);

        return view('custom::work-orders.show', compact('workOrder', 'technicians', 'spareParts'));
    }

    /**
     * PATCH /fom/work-orders/{id} — update status, assign technician, complete.
     */
    public function update(Request $request, int $id)
    {
        $wo = WorkOrder::findOrFail($id);
        $action = $request->input('action');

        switch ($action) {
            case 'assign':
                $request->validate(['assigned_to' => 'required|exists:users,id']);
                $technician = User::findOrFail($request->assigned_to);
                $this->workOrderService->assignTechnician($wo, $technician, $request->user());
                $message = "İş emri {$technician->full_name} atandı.";
                break;

            case 'start':
                $this->workOrderService->startWork($wo, $request->user());
                $message = 'İş emri başlatıldı.';
                break;

            case 'complete':
                $request->validate([
                    'resolution_notes'       => 'required|string|min:5',
                    'time_spent_minutes'     => 'required|integer|min:1',
                    'parts'                  => 'nullable|array',
                    'parts.*.spare_part_id'  => 'nullable|exists:spare_parts,id',
                    'parts.*.quantity'       => 'nullable|integer|min:1',
                ]);

                // Save parts used before completing (so deductPartsUsed picks them up)
                if ($request->filled('parts')) {
                    foreach ($request->input('parts') as $partData) {
                        if (! empty($partData['spare_part_id']) && ! empty($partData['quantity'])) {
                            WorkOrderPart::create([
                                'work_order_id' => $wo->id,
                                'spare_part_id' => $partData['spare_part_id'],
                                'quantity_used' => $partData['quantity'],
                            ]);
                        }
                    }
                }

                $this->workOrderService->complete($wo, $request->only([
                    'resolution_notes', 'time_spent_minutes',
                ]));
                $message = 'İş emri tamamlandı.';
                break;

            case 'cancel':
                $wo->update(['status' => 'iptal']);
                $message = 'İş emri iptal edildi.';
                break;

            default:
                return back()->with('error', 'Geçersiz işlem.');
        }

        return redirect()
            ->route('fom.work-orders.show', $wo->id)
            ->with('success', $message);
    }

    /**
     * GET /fom/work-orders/board — kanban board grouped by status.
     */
    public function board()
    {
        $columns = [
            'bekliyor'      => 'Bekliyor',
            'atandi'        => 'Atandı',
            'devam_ediyor'  => 'Devam Ediyor',
            'tamamlandi'    => 'Tamamlandı',
        ];

        $workOrders = WorkOrder::with(['asset', 'assignedTo'])
            ->where('status', '!=', 'iptal')
            ->orderByRaw("FIELD(priority, 'kritik', 'yuksek', 'normal')")
            ->latest()
            ->get()
            ->groupBy('status');

        return view('custom::work-orders.board', compact('columns', 'workOrders'));
    }
}
