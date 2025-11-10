<?php

namespace App\Http\Controllers\Kits;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceHistory;
use App\Models\Asset;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $history = MaintenanceHistory::with(['asset', 'performedByUser', 'kit', 'workOrder'])
            ->when($request->asset_id, function ($query, $assetId) {
                return $query->where('asset_id', $assetId);
            })
            ->when($request->type, function ($query, $type) {
                return $query->where('type', $type);
            })
            ->when($request->outcome, function ($query, $outcome) {
                return $query->where('outcome', $outcome);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                return $query->whereDate('performed_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                return $query->whereDate('performed_at', '<=', $dateTo);
            })
            ->when($request->search, function ($query, $search) {
                return $query->where('title', 'like', "%{$search}%");
            })
            ->orderBy('performed_at', 'desc')
            ->paginate(50);

        $assets = Asset::select(['id', 'name', 'asset_tag'])->orderBy('name')->get();
        $totalCost = MaintenanceHistory::sum('cost');
        $totalMaintenance = MaintenanceHistory::count();

        return view('kits.history.index', compact('history', 'assets', 'totalCost', 'totalMaintenance'));
    }

    public function show(MaintenanceHistory $history)
    {
        $history->load(['asset', 'workOrder', 'maintenanceSchedule', 'kit', 'performedByUser']);
        return view('kits.history.show', compact('history'));
    }

    public function byAsset(Asset $asset)
    {
        $history = MaintenanceHistory::byAsset($asset->id)
            ->with(['performedByUser', 'kit', 'workOrder'])
            ->orderBy('performed_at', 'desc')
            ->paginate(50);

        return view('kits.history.by-asset', compact('history', 'asset'));
    }

    public function export(Request $request)
    {
        return response()->json(['message' => 'Export functionality to be implemented']);
    }

    public function statistics(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->subMonths(6);
        $dateTo = $request->date_to ?? now();

        $stats = [
            'total_maintenance' => MaintenanceHistory::byDateRange($dateFrom, $dateTo)->count(),
            'successful' => MaintenanceHistory::byDateRange($dateFrom, $dateTo)->successful()->count(),
            'failed' => MaintenanceHistory::byDateRange($dateFrom, $dateTo)->failed()->count(),
            'total_cost' => MaintenanceHistory::byDateRange($dateFrom, $dateTo)->sum('cost'),
            'total_duration' => MaintenanceHistory::byDateRange($dateFrom, $dateTo)->sum('duration'),
            'by_type' => MaintenanceHistory::byDateRange($dateFrom, $dateTo)
                ->selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type'),
            'by_outcome' => MaintenanceHistory::byDateRange($dateFrom, $dateTo)
                ->selectRaw('outcome, count(*) as count')
                ->groupBy('outcome')
                ->pluck('count', 'outcome'),
        ];

        return view('kits.history.statistics', compact('stats', 'dateFrom', 'dateTo'));
    }
}
