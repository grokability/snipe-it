<?php

namespace App\Custom\Http\Controllers;

use App\Custom\Models\PurchaseRequest;
use App\Custom\Models\ShiftHandover;
use App\Custom\Models\SparePart;
use App\Custom\Models\WorkOrder;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * GET /fom — main FOM dashboard.
     */
    public function index()
    {
        // Open work orders grouped by priority
        $openWorkOrders = WorkOrder::open()
            ->selectRaw("priority, count(*) as count")
            ->groupBy('priority')
            ->pluck('count', 'priority');

        $openTotal = $openWorkOrders->sum();

        // Today's shift handover status
        $todayHandovers = ShiftHandover::where('shift_date', now()->toDateString())
            ->with(['location', 'outgoingUser', 'incomingUser'])
            ->get();

        $handoversPending   = $todayHandovers->where('status', 'bekliyor')->count();
        $handoversCompleted = $todayHandovers->where('status', 'tamamlandi')->count();

        // Low stock parts
        $lowStockCount = SparePart::lowStock()->count();

        // Pending purchase requests
        $pendingPurchases = PurchaseRequest::where('status', 'bekliyor')->count();

        // Recent 5 work orders
        $recentWorkOrders = WorkOrder::with(['asset', 'assignedTo'])
            ->latest()
            ->limit(5)
            ->get();

        return view('custom::dashboard.index', compact(
            'openWorkOrders',
            'openTotal',
            'todayHandovers',
            'handoversPending',
            'handoversCompleted',
            'lowStockCount',
            'pendingPurchases',
            'recentWorkOrders',
        ));
    }
}
