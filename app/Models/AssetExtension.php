<?php

namespace App\Models;

/**
 * Asset Extension for Purchase Orders
 * This extends the base Asset functionality with PO relationships
 */
class AssetExtension
{
    /**
     * Get purchase order relationship for an asset
     */
    public static function getPurchaseOrder($assetId)
    {
        return \App\Models\Asset::find($assetId)?->purchaseOrder();
    }

    /**
     * Get all assets for a supplier that are available for PO
     */
    public static function getAvailableAssetsForSupplier($supplierId)
    {
        return \App\Models\Asset::where('supplier_id', $supplierId)
            ->whereNull('purchase_order_id') // Only assets not yet in a PO
            ->with(['model', 'supplier'])
            ->get();
    }

    /**
     * Get all assets for a supplier (including those in POs)
     */
    public static function getAllAssetsForSupplier($supplierId)
    {
        return \App\Models\Asset::where('supplier_id', $supplierId)
            ->with(['model', 'supplier'])
            ->get();
    }

    /**
     * Link asset to purchase order
     */
    public static function linkToPurchaseOrder($assetId, $purchaseOrderId)
    {
        $asset = \App\Models\Asset::find($assetId);
        if ($asset) {
            $asset->purchase_order_id = $purchaseOrderId;
            return $asset->save();
        }
        return false;
    }

    /**
     * Unlink asset from purchase order
     */
    public static function unlinkFromPurchaseOrder($assetId)
    {
        $asset = \App\Models\Asset::find($assetId);
        if ($asset) {
            $asset->purchase_order_id = null;
            return $asset->save();
        }
        return false;
    }
}
