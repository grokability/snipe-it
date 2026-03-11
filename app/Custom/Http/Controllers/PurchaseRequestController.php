<?php

namespace App\Custom\Http\Controllers;

use App\Custom\Models\PurchaseRequest;
use App\Custom\Services\StockService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PurchaseRequestController extends Controller
{
    public function __construct(protected StockService $stockService)
    {
    }

    /**
     * GET /fom/purchase-requests — list with status filter.
     */
    public function index(Request $request)
    {
        $query = PurchaseRequest::with(['sparePart', 'requestedBy', 'approvedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()
            ->paginate(20)
            ->appends($request->query());

        return view('custom::spare-parts.purchase-requests', compact('requests'));
    }

    /**
     * GET /fom/purchase-requests/{id} — detail with approve/reject.
     */
    public function show(int $id)
    {
        $purchaseRequest = PurchaseRequest::with([
            'sparePart.manufacturer',
            'sparePart.supplier',
            'requestedBy',
            'approvedBy',
            'workOrder',
        ])->findOrFail($id);

        return view('custom::spare-parts.purchase-request-show', compact('purchaseRequest'));
    }

    /**
     * PATCH /fom/purchase-requests/{id} — approve or reject.
     */
    public function update(Request $request, int $id)
    {
        $pr = PurchaseRequest::findOrFail($id);

        $request->validate([
            'action' => 'required|in:approve,reject,order',
        ]);

        switch ($request->action) {
            case 'approve':
                $pr->update([
                    'status'      => 'onaylandi',
                    'approved_by' => $request->user()->id,
                ]);
                $message = "Talep onaylandı: {$pr->request_number}";
                break;

            case 'reject':
                $pr->update([
                    'status'      => 'iptal',
                    'approved_by' => $request->user()->id,
                ]);
                $message = "Talep reddedildi: {$pr->request_number}";
                break;

            case 'order':
                $pr->update(['status' => 'siparis_verildi']);
                $message = "Sipariş verildi: {$pr->request_number}";
                break;

            default:
                return back()->with('error', 'Geçersiz işlem.');
        }

        return redirect()
            ->route('fom.purchase-requests.show', $pr->id)
            ->with('success', $message);
    }

    /**
     * POST /fom/purchase-requests/{id}/receive — mark as received, add stock.
     */
    public function receive(Request $request, int $id)
    {
        $pr = PurchaseRequest::with('sparePart')->findOrFail($id);

        if (! in_array($pr->status, ['onaylandi', 'siparis_verildi'])) {
            return back()->with('error', 'Bu talep teslim alınabilir durumda değil.');
        }

        $request->validate([
            'received_quantity' => 'required|integer|min:1',
        ]);

        $this->stockService->addStock(
            $pr->sparePart,
            $request->received_quantity,
            'purchase',
            $pr->id,
            $request->user(),
        );

        $pr->update(['status' => 'teslim_alindi']);

        return redirect()
            ->route('fom.purchase-requests.show', $pr->id)
            ->with('success', "{$request->received_quantity} adet teslim alındı, stok güncellendi.");
    }
}
