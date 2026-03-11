<?php

namespace App\Custom\Services;

use App\Custom\Events\WorkOrderAssigned;
use App\Custom\Events\WorkOrderCompleted;
use App\Custom\Events\WorkOrderCreated;
use App\Custom\Models\ShiftHandoverItem;
use App\Custom\Models\WorkOrder;
use App\Models\User;

class WorkOrderService
{
    public function __construct(protected StockService $stockService)
    {
    }

    /**
     * Create a work order from an operator's fault report.
     */
    public function createFromReport(array $data, User $reporter): WorkOrder
    {
        $wo = WorkOrder::create([
            'asset_id'    => $data['asset_id'],
            'reported_by' => $reporter->id,
            'location_id' => $data['location_id'] ?? null,
            'priority'    => $data['priority'] ?? 'normal',
            'status'      => 'bekliyor',
            'description' => $data['description'],
            'photo_path'  => $data['photo_path'] ?? null,
        ]);

        WorkOrderCreated::dispatch($wo);

        return $wo;
    }

    /**
     * Assign a technician to a work order.
     */
    public function assignTechnician(WorkOrder $wo, User $technician, User $assignedBy): WorkOrder
    {
        $wo->update([
            'assigned_to' => $technician->id,
            'status'      => 'atandi',
        ]);

        WorkOrderAssigned::dispatch($wo, $assignedBy);

        return $wo->fresh();
    }

    /**
     * Mark a work order as in-progress.
     */
    public function startWork(WorkOrder $wo, User $technician): WorkOrder
    {
        $wo->update([
            'status'     => 'devam_ediyor',
            'started_at' => now(),
        ]);

        return $wo->fresh();
    }

    /**
     * Complete a work order, deduct used parts, fire event.
     *
     * $data keys: resolution_notes, time_spent_minutes
     */
    public function complete(WorkOrder $wo, array $data): WorkOrder
    {
        $wo->update([
            'status'             => 'tamamlandi',
            'completed_at'       => now(),
            'resolution_notes'   => $data['resolution_notes'] ?? null,
            'time_spent_minutes' => $data['time_spent_minutes'] ?? null,
        ]);

        // Deduct spare parts used during this work order
        $this->stockService->deductPartsUsed($wo);

        WorkOrderCompleted::dispatch($wo->fresh());

        return $wo->fresh();
    }

    /**
     * Create a work order from a damaged item found during shift handover.
     */
    public function createFromHandover(ShiftHandoverItem $item): WorkOrder
    {
        $handover = $item->handover;
        $asset = $item->asset;

        $description = "Vardiya devrinde tespit edildi";
        if ($item->notes) {
            $description .= ": {$item->notes}";
        }
        $description .= " (Vardiya: {$handover->shift_type}, Tarih: {$handover->shift_date->format('d.m.Y')})";

        $wo = WorkOrder::create([
            'asset_id'    => $item->asset_id,
            'reported_by' => $handover->outgoing_user_id,
            'location_id' => $handover->location_id,
            'priority'    => 'yuksek',
            'status'      => 'bekliyor',
            'description' => $description,
            'photo_path'  => $item->photo_path,
        ]);

        // Link the handover item to the created work order
        $item->update(['work_order_id' => $wo->id]);

        WorkOrderCreated::dispatch($wo);

        return $wo;
    }
}
