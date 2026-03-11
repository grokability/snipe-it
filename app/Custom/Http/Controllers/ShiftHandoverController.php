<?php

namespace App\Custom\Http\Controllers;

use App\Custom\Http\Requests\StoreHandoverRequest;
use App\Custom\Models\ShiftHandover;
use App\Custom\Services\ShiftService;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Location;
use Illuminate\Http\Request;

class ShiftHandoverController extends Controller
{
    public function __construct(protected ShiftService $shiftService)
    {
    }

    /**
     * GET /fom/shift/history — shift handover history with filters.
     */
    public function index(Request $request)
    {
        $query = ShiftHandover::with(['location', 'outgoingUser', 'incomingUser']);

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }
        if ($request->filled('date_from')) {
            $query->where('shift_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('shift_date', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $handovers = $query->latest('shift_date')
            ->latest('id')
            ->paginate(20)
            ->appends($request->query());

        $locations = Location::orderBy('name')->get(['id', 'name']);

        return view('custom::shift-handover.index', compact('handovers', 'locations'));
    }

    /**
     * GET /fom/shift/handover — start new handover form.
     */
    public function create(Request $request)
    {
        $locations = Location::orderBy('name')->get(['id', 'name']);
        $currentShift = ShiftHandover::getCurrentShift();

        $assets = collect();
        if ($request->filled('location_id')) {
            $assets = Asset::where('rtd_location_id', $request->location_id)
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(['id', 'name', 'asset_tag']);
        }

        return view('custom::shift-handover.create', compact('locations', 'currentShift', 'assets'));
    }

    /**
     * POST /fom/shift/handover — store new shift handover.
     */
    public function store(StoreHandoverRequest $request)
    {
        $handover = $this->shiftService->startHandover(
            $request->validated(),
            $request->user(),
        );

        return redirect()
            ->route('fom.shift.accept', $handover->id)
            ->with('success', 'Vardiya devri oluşturuldu. Gelen operatör onay bekliyor.');
    }

    /**
     * GET /fom/shift/accept/{id} — show acceptance form for incoming operator.
     */
    public function accept(int $id)
    {
        $handover = ShiftHandover::with([
            'items.asset',
            'items.workOrder',
            'location',
            'outgoingUser',
        ])->findOrFail($id);

        if ($handover->status === 'tamamlandi') {
            return redirect()
                ->route('fom.shift.history')
                ->with('info', 'Bu devir teslim zaten tamamlanmış.');
        }

        return view('custom::shift-handover.accept', compact('handover'));
    }

    /**
     * PATCH /fom/shift/accept/{id} — incoming operator accepts handover.
     */
    public function update(Request $request, int $id)
    {
        $handover = ShiftHandover::with('items')->findOrFail($id);

        if ($handover->status === 'tamamlandi') {
            return back()->with('error', 'Bu devir teslim zaten tamamlanmış.');
        }

        $request->validate([
            'incoming_notes'      => 'nullable|string|max:2000',
            'incoming_signature'  => 'nullable|string',
            'items'               => 'nullable|array',
            'items.*.condition'   => 'nullable|in:iyi,hasarli,eksik',
            'items.*.notes'       => 'nullable|string|max:500',
        ]);

        $handover = $this->shiftService->acceptHandover(
            $handover,
            $request->only(['incoming_notes', 'incoming_signature', 'items']),
            $request->user(),
        );

        return redirect()
            ->route('fom.shift.history')
            ->with('success', 'Vardiya devir teslim tamamlandı.');
    }
}
