<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Asset to Purchase Order Lookup Controller
 * Provides actual lookup functionality for the PO Lookup button
 */
class AssetPOLookupController extends Controller
{
    /**
     * Look up Purchase Order information for a specific asset
     */
    public function lookupAssetPO(Request $request)
    {
        $request->validate([
            'asset_identifier' => 'required|string|max:255'
        ]);

        $assetIdentifier = trim($request->asset_identifier);

        try {
            // First try to find by asset_tag (exact match)
            $result = DB::select("
                SELECT 
                    a.id as asset_id,
                    a.asset_tag,
                    COALESCE(a.name, 'Unnamed Asset') as asset_name,
                    po.id as po_id,
                    po.po_number,
                    po.status as po_status,
                    po.order_date,
                    po.expected_delivery_date,
                    po.total_amount,
                    po.notes as po_notes,
                    s.name as supplier_name,
                    am.name as model_name
                FROM assets a
                LEFT JOIN purchase_orders po ON a.purchase_order_id = po.id
                LEFT JOIN suppliers s ON po.supplier_id = s.id
                LEFT JOIN models am ON a.model_id = am.id
                WHERE a.asset_tag = ?
                LIMIT 1
            ", [$assetIdentifier]);

            // If not found by asset_tag and input is numeric, try by ID
            if (empty($result) && is_numeric($assetIdentifier)) {
                $result = DB::select("
                    SELECT 
                        a.id as asset_id,
                        a.asset_tag,
                        COALESCE(a.name, 'Unnamed Asset') as asset_name,
                        po.id as po_id,
                        po.po_number,
                        po.status as po_status,
                        po.order_date,
                        po.expected_delivery_date,
                        po.total_amount,
                        po.notes as po_notes,
                        s.name as supplier_name,
                        am.name as model_name
                    FROM assets a
                    LEFT JOIN purchase_orders po ON a.purchase_order_id = po.id
                    LEFT JOIN suppliers s ON po.supplier_id = s.id
                    LEFT JOIN models am ON a.model_id = am.id
                    WHERE a.id = ?
                    LIMIT 1
                ", [$assetIdentifier]);
            }

            if (empty($result)) {
                return response()->json([
                    'success' => false,
                    'message' => "Asset not found: {$assetIdentifier}",
                    'asset' => null,
                    'purchase_order' => null
                ]);
            }

            $asset = $result[0];

            // Format the response
            $response = [
                'success' => true,
                'message' => 'Asset found',
                'asset' => [
                    'id' => $asset->asset_id,
                    'asset_tag' => $asset->asset_tag,
                    'name' => $asset->asset_name,
                    'model_name' => $asset->model_name,
                    'supplier_name' => $asset->supplier_name
                ]
            ];

            // Add PO information if available
            if ($asset->po_id) {
                $response['purchase_order'] = [
                    'id' => $asset->po_id,
                    'po_number' => $asset->po_number,
                    'status' => $asset->po_status,
                    'order_date' => $asset->order_date,
                    'expected_delivery_date' => $asset->expected_delivery_date,
                    'total_amount' => $asset->total_amount,
                    'notes' => $asset->po_notes,
                    'supplier_name' => $asset->supplier_name
                ];
                $response['message'] = 'Asset found with Purchase Order';
            } else {
                $response['purchase_order'] = null;
                $response['message'] = 'Asset found but not linked to any Purchase Order';
            }

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error during lookup: ' . $e->getMessage(),
                'asset' => null,
                'purchase_order' => null
            ], 500);
        }
    }

    /**
     * Get all assets with their PO information
     */
    public function getAllAssetPOs()
    {
        try {
            $results = DB::select("
                SELECT 
                    a.asset_tag,
                    COALESCE(a.name, 'Unnamed') as asset_name,
                    po.po_number,
                    po.status as po_status,
                    s.name as supplier_name
                FROM assets a
                JOIN purchase_orders po ON a.purchase_order_id = po.id
                LEFT JOIN suppliers s ON po.supplier_id = s.id
                ORDER BY po.created_at DESC
                LIMIT 20
            ");

            return response()->json([
                'success' => true,
                'message' => 'Assets with Purchase Orders retrieved',
                'assets' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving assets: ' . $e->getMessage(),
                'assets' => []
            ], 500);
        }
    }
}
