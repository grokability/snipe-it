<?php

namespace App\Custom\Services;

use App\Custom\Events\ShiftHandoverCompleted;
use App\Custom\Models\ShiftHandover;
use App\Custom\Models\ShiftHandoverItem;
use App\Models\User;

class ShiftService
{
    public function __construct(protected WorkOrderService $workOrderService)
    {
    }

    /**
     * Start a shift handover — outgoing operator records equipment status.
     *
     * $data keys: location_id, outgoing_notes, items (array of [asset_id, condition, notes, photo_path])
     */
    public function startHandover(array $data, User $outgoingUser): ShiftHandover
    {
        $handover = ShiftHandover::create([
            'shift_type'       => ShiftHandover::getCurrentShift(),
            'shift_date'       => now()->toDateString(),
            'location_id'      => $data['location_id'],
            'outgoing_user_id' => $outgoingUser->id,
            'incoming_user_id' => $outgoingUser->id, // placeholder until accepted
            'outgoing_notes'   => $data['outgoing_notes'] ?? null,
            'outgoing_signature' => $data['outgoing_signature'] ?? null,
            'status'           => 'bekliyor',
        ]);

        foreach ($data['items'] ?? [] as $itemData) {
            ShiftHandoverItem::create([
                'shift_handover_id' => $handover->id,
                'asset_id'          => $itemData['asset_id'],
                'condition'         => $itemData['condition'] ?? 'iyi',
                'notes'             => $itemData['notes'] ?? null,
                'photo_path'        => $itemData['photo_path'] ?? null,
            ]);
        }

        return $handover->load('items');
    }

    /**
     * Accept (complete) a shift handover — incoming operator reviews and confirms.
     *
     * $itemUpdates: optional array keyed by item ID with [condition, notes] overrides
     */
    public function acceptHandover(ShiftHandover $handover, array $itemUpdates, User $incomingUser): ShiftHandover
    {
        $handover->update([
            'incoming_user_id' => $incomingUser->id,
            'incoming_notes'   => $itemUpdates['incoming_notes'] ?? null,
            'incoming_signature' => $itemUpdates['incoming_signature'] ?? null,
            'status'           => 'tamamlandi',
            'completed_at'     => now(),
        ]);

        // Process item-level updates from incoming operator
        foreach ($handover->items as $item) {
            if (isset($itemUpdates['items'][$item->id])) {
                $update = $itemUpdates['items'][$item->id];
                $item->update([
                    'condition' => $update['condition'] ?? $item->condition,
                    'notes'     => $update['notes'] ?? $item->notes,
                ]);
            }

            // Auto-create work order for damaged equipment
            if ($item->condition === 'hasarli' && ! $item->work_order_id) {
                $this->workOrderService->createFromHandover($item);
            }
        }

        ShiftHandoverCompleted::dispatch($handover->fresh('items'));

        return $handover->fresh('items');
    }
}
