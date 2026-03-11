<?php

namespace App\Custom\Services;

use App\Custom\Events\StockBelowMinimum;
use App\Custom\Models\PurchaseRequest;
use App\Custom\Models\SparePart;
use App\Custom\Models\SparePartMovement;
use App\Custom\Models\WorkOrder;
use App\Models\User;

class StockService
{
    /**
     * Deduct all spare parts recorded on a completed work order.
     */
    public function deductPartsUsed(WorkOrder $wo): void
    {
        $wo->loadMissing('parts.sparePart');
        $user = $wo->assignedTo ?? User::find($wo->reported_by);

        foreach ($wo->parts as $woPart) {
            $this->deduct(
                $woPart->sparePart,
                $woPart->quantity_used,
                'work_order',
                $wo->id,
                $user,
            );
        }
    }

    /**
     * Deduct stock for a spare part.
     *
     * @throws \RuntimeException if deduction would result in negative stock
     */
    public function deduct(SparePart $part, int $qty, string $refType, int $refId, User $user): void
    {
        if ($part->quantity_on_hand < $qty) {
            throw new \RuntimeException(
                "Yetersiz stok: {$part->name} — Mevcut: {$part->quantity_on_hand}, İstenen: {$qty}"
            );
        }

        $part->decrement('quantity_on_hand', $qty);

        SparePartMovement::create([
            'spare_part_id'  => $part->id,
            'movement_type'  => 'cikis',
            'quantity'       => -$qty,
            'reference_type' => $refType,
            'reference_id'   => $refId,
            'performed_by'   => $user->id,
            'notes'          => "Otomatik çıkış — {$refType} #{$refId}",
            'created_at'     => now(),
        ]);

        // Refresh to get updated quantity
        $part->refresh();

        if ($part->quantity_on_hand <= $part->minimum_quantity) {
            StockBelowMinimum::dispatch($part, $part->quantity_on_hand);
        }
    }

    /**
     * Add stock for a spare part (purchase receipt, return, etc.).
     */
    public function addStock(SparePart $part, int $qty, string $refType, int $refId, User $user): void
    {
        $part->increment('quantity_on_hand', $qty);

        SparePartMovement::create([
            'spare_part_id'  => $part->id,
            'movement_type'  => 'giris',
            'quantity'       => $qty,
            'reference_type' => $refType,
            'reference_id'   => $refId,
            'performed_by'   => $user->id,
            'notes'          => "Stok girişi — {$refType} #{$refId}",
            'created_at'     => now(),
        ]);
    }

    /**
     * Create an automatic purchase request for a low-stock spare part.
     */
    public function createPurchaseRequest(SparePart $part): PurchaseRequest
    {
        $orderQty = max(1, ($part->minimum_quantity * 2) - $part->quantity_on_hand);

        return PurchaseRequest::create([
            'spare_part_id'      => $part->id,
            'requested_quantity' => $orderQty,
            'estimated_cost'     => $part->unit_cost ? $orderQty * $part->unit_cost : null,
            'reason'             => "Minimum stok altına düştü (Mevcut: {$part->quantity_on_hand}, Minimum: {$part->minimum_quantity})",
            'status'             => 'bekliyor',
            'requested_by'       => 1, // System user (admin)
            'supplier_id'        => $part->supplier_id,
        ]);
    }
}
