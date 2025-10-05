<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\AssetModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
// use Barryvdh\DomPDF\Facade\Pdf; // Disabled for now

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of purchase orders
     */
    public function index(Request $request)
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'creator', 'items'])
            ->when($request->status, function ($query, $status) {
                return $query->byStatus($status);
            })
            ->when($request->supplier_id, function ($query, $supplierId) {
                return $query->bySupplier($supplierId);
            })
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('po_number', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $suppliers = Supplier::all();
        $statuses = ['draft', 'pending', 'approved', 'ordered', 'received', 'cancelled'];

        return view('purchase-orders.index', compact('purchaseOrders', 'suppliers', 'statuses'));
    }

    /**
     * Show the form for creating a new purchase order
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $assetModels = AssetModel::orderBy('name')->get();
        
        return view('purchase-orders.create', compact('suppliers', 'assetModels'));
    }

    /**
     * Store a newly created purchase order
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after:order_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.asset_id' => 'nullable|exists:assets,id',
            'items.*.model_name' => 'nullable|string',
            'items.*.quantity' => 'nullable|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        
        try {
            // Create purchase order
            $purchaseOrder = new PurchaseOrder();
            $purchaseOrder->po_number = $purchaseOrder->generatePoNumber();
            $purchaseOrder->supplier_id = $request->supplier_id;
            $purchaseOrder->order_date = $request->order_date;
            $purchaseOrder->expected_delivery_date = $request->expected_delivery_date;
            $purchaseOrder->notes = $request->notes;
            $purchaseOrder->created_by = Auth::id();
            $purchaseOrder->save();

            // Create purchase order items
            foreach ($request->items as $itemData) {
                $item = new PurchaseOrderItem();
                $item->purchase_order_id = $purchaseOrder->id;
                
                // Handle asset-based items
                if (isset($itemData['asset_id']) && $itemData['asset_id']) {
                    $asset = \App\Models\Asset::find($itemData['asset_id']);
                    if ($asset) {
                        $item->asset_id = $asset->id;
                        $item->asset_model_id = $asset->model_id;
                        $item->model_name = $asset->model ? $asset->model->name : $itemData['model_name'];
                        
                        // Link asset to this PO
                        $asset->purchase_order_id = $purchaseOrder->id;
                        $asset->save();
                    }
                } else {
                    // Fallback to model-based items (legacy)
                    $item->asset_model_id = $itemData['asset_model_id'] ?? null;
                    $item->model_name = $itemData['model_name'];
                }
                
                $item->quantity = $itemData['quantity'] ?? 1;
                $item->unit_price = $itemData['unit_price'];
                $item->notes = $itemData['notes'] ?? null;
                $item->save();
            }

            // Calculate total
            $purchaseOrder->calculateTotal();

            DB::commit();

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                           ->with('success', 'Purchase order created successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                        ->with('error', 'Error creating purchase order: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified purchase order
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'creator', 'items.assetModel']);
        
        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    /**
     * Show the form for editing the specified purchase order
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        if (in_array($purchaseOrder->status, ['received', 'cancelled'])) {
            return back()->with('error', 'Cannot edit a ' . $purchaseOrder->status . ' purchase order.');
        }

        $suppliers = Supplier::orderBy('name')->get();
        $assetModels = AssetModel::orderBy('name')->get();
        $purchaseOrder->load('items');
        
        return view('purchase-orders.edit', compact('purchaseOrder', 'suppliers', 'assetModels'));
    }

    /**
     * Update the specified purchase order
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (in_array($purchaseOrder->status, ['received', 'cancelled'])) {
            return back()->with('error', 'Cannot update a ' . $purchaseOrder->status . ' purchase order.');
        }

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'status' => 'required|in:draft,pending,approved,ordered,received,cancelled',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after:order_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.model_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        
        try {
            // Update purchase order
            $purchaseOrder->update([
                'supplier_id' => $request->supplier_id,
                'status' => $request->status,
                'order_date' => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'notes' => $request->notes
            ]);

            // Delete existing items and create new ones
            $purchaseOrder->items()->delete();
            
            foreach ($request->items as $itemData) {
                $item = new PurchaseOrderItem();
                $item->purchase_order_id = $purchaseOrder->id;
                $item->asset_model_id = $itemData['asset_model_id'] ?? null;
                $item->model_name = $itemData['model_name'];
                $item->quantity = $itemData['quantity'];
                $item->unit_price = $itemData['unit_price'];
                $item->notes = $itemData['notes'] ?? null;
                $item->save();
            }

            // Calculate total
            $purchaseOrder->calculateTotal();

            DB::commit();

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                           ->with('success', 'Purchase order updated successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                        ->with('error', 'Error updating purchase order: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified purchase order
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if (in_array($purchaseOrder->status, ['ordered', 'received'])) {
            return back()->with('error', 'Cannot delete a ' . $purchaseOrder->status . ' purchase order.');
        }

        $purchaseOrder->delete();
        
        return redirect()->route('purchase-orders.index')
                       ->with('success', 'Purchase order deleted successfully!');
    }

    /**
     * Generate PDF for purchase order
     */
    public function generatePdf(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'creator', 'items']);
        
        // For now, return the PDF view as HTML until DomPDF is properly installed
        return view('purchase-orders.pdf', compact('purchaseOrder'));
        
        // TODO: Enable when DomPDF is installed
        // $pdf = Pdf::loadView('purchase-orders.pdf', compact('purchaseOrder'));
        // return $pdf->download("PO-{$purchaseOrder->po_number}.pdf");
    }

    /**
     * Update purchase order status
     */
    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate([
            'status' => 'required|in:draft,pending,approved,ordered,received,cancelled'
        ]);

        $purchaseOrder->update(['status' => $request->status]);
        
        return back()->with('success', 'Purchase order status updated successfully!');
    }

    /**
     * Get assets for a specific supplier (for PO creation)
     */
    public function getSupplierAssets(\App\Models\Supplier $supplier)
    {
        try {
            // Debug info
            \Log::info('Getting assets for supplier: ' . $supplier->id . ' (' . $supplier->name . ')');
            
            // Get all assets from this supplier
            $assets = \App\Models\Asset::where('supplier_id', $supplier->id)
                ->with(['model'])
                ->select(['id', 'name', 'asset_tag', 'model_id', 'supplier_id'])
                ->get();

            \Log::info('Found ' . $assets->count() . ' assets');
            \Log::info('Assets: ' . $assets->toJson());

            // Format the response
            $formattedAssets = $assets->map(function ($asset) {
                return [
                    'id' => $asset->id,
                    'name' => $asset->name ?: 'Asset #' . $asset->asset_tag,
                    'asset_tag' => $asset->asset_tag,
                    'model_name' => $asset->model ? $asset->model->name : 'Unknown Model',
                    'model_id' => $asset->model_id,
                    'display_name' => ($asset->name ?: 'Asset #' . $asset->asset_tag) . 
                                    ' (' . ($asset->model ? $asset->model->name : 'Unknown') . ')'
                ];
            });

            return response()->json([
                'success' => true,
                'assets' => $formattedAssets->values(),
                'count' => $formattedAssets->count(),
                'message' => $formattedAssets->count() . ' assets available for this supplier',
                'debug' => [
                    'supplier_id' => $supplier->id,
                    'supplier_name' => $supplier->name,
                    'raw_count' => $assets->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading assets: ' . $e->getMessage(),
                'assets' => [],
                'count' => 0
            ], 500);
        }
    }

    /**
     * Get asset models for a specific supplier (legacy method - kept for compatibility)
     */
    public function getSupplierModels(\App\Models\Supplier $supplier)
    {
        try {
            // Get asset models that have assets from this supplier
            $models = \App\Models\AssetModel::whereHas('assets', function ($query) use ($supplier) {
                $query->where('supplier_id', $supplier->id);
            })
            ->select(['id', 'name', 'model_number'])
            ->distinct()
            ->get();

            // Format the response
            $formattedModels = $models->map(function ($model) {
                return [
                    'id' => $model->id,
                    'name' => $model->name,
                    'model_number' => $model->model_number,
                    'display_name' => $model->name . ($model->model_number ? ' (' . $model->model_number . ')' : '')
                ];
            });

            return response()->json([
                'success' => true,
                'models' => $formattedModels->values(),
                'count' => $formattedModels->count(),
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->name
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching models for supplier ' . $supplier->id . ': ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching models for supplier: ' . $e->getMessage(),
                'models' => [],
                'count' => 0
            ], 500);
        }
    }

    /**
     * Get purchase order information for a specific asset
     */
    public function getAssetPurchaseOrderInfo($assetId)
    {
        try {
            // Get the asset and its purchase order
            $asset = \App\Models\Asset::find($assetId);
            
            if (!$asset || !$asset->purchase_order_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No purchase order found for this asset',
                    'purchase_order' => null
                ]);
            }

            $purchaseOrder = PurchaseOrder::with(['supplier'])
                ->find($asset->purchase_order_id);

            if (!$purchaseOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase order not found',
                    'purchase_order' => null
                ]);
            }

            // Format the response
            $poData = [
                'id' => $purchaseOrder->id,
                'po_number' => $purchaseOrder->po_number,
                'status' => $purchaseOrder->status,
                'order_date' => $purchaseOrder->order_date,
                'expected_delivery_date' => $purchaseOrder->expected_delivery_date,
                'total_amount' => $purchaseOrder->total_amount,
                'notes' => $purchaseOrder->notes,
                'supplier_name' => $purchaseOrder->supplier ? $purchaseOrder->supplier->name : null,
                'supplier_id' => $purchaseOrder->supplier_id
            ];

            return response()->json([
                'success' => true,
                'message' => 'Purchase order information retrieved',
                'purchase_order' => $poData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving purchase order information: ' . $e->getMessage(),
                'purchase_order' => null
            ], 500);
        }
    }

    /**
     * Duplicate a purchase order
     */
    public function duplicate(PurchaseOrder $purchaseOrder)
    {
        DB::beginTransaction();
        
        try {
            $newPo = $purchaseOrder->replicate();
            $newPo->po_number = $newPo->generatePoNumber();
            $newPo->status = 'draft';
            $newPo->created_by = Auth::id();
            $newPo->save();

            foreach ($purchaseOrder->items as $item) {
                $newItem = $item->replicate();
                $newItem->purchase_order_id = $newPo->id;
                $newItem->save();
            }

            $newPo->calculateTotal();

            DB::commit();

            return redirect()->route('purchase-orders.edit', $newPo)
                           ->with('success', 'Purchase order duplicated successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error duplicating purchase order: ' . $e->getMessage());
        }
    }
}
