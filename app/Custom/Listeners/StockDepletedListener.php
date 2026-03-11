<?php

namespace App\Custom\Listeners;

use App\Custom\Events\StockBelowMinimum;
use App\Custom\Models\PurchaseRequest;
use App\Custom\Notifications\LowStockAlertNotification;
use App\Custom\Services\StockService;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class StockDepletedListener
{
    public function __construct(protected StockService $stockService)
    {
    }

    public function handle(StockBelowMinimum $event): void
    {
        $part = $event->sparePart;
        $currentQty = $event->currentQty;

        // Notify superadmins (will be refined to fom.warehouse + fom.purchasing roles)
        $recipients = User::where('activated', 1)
            ->where('permissions->superuser', '1')
            ->get();

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new LowStockAlertNotification($part, $currentQty));
        }

        // Auto-create purchase request if no pending request exists for this part
        $hasPending = PurchaseRequest::where('spare_part_id', $part->id)
            ->whereIn('status', ['bekliyor', 'onaylandi', 'siparis_verildi'])
            ->exists();

        if (! $hasPending) {
            $pr = $this->stockService->createPurchaseRequest($part);

            Log::channel('single')->info("FOM: Otomatik satın alma talebi — {$pr->request_number}", [
                'spare_part_id' => $part->id,
                'qty'           => $pr->requested_quantity,
            ]);
        }

        Log::channel('single')->warning("FOM: Düşük stok alarmı — {$part->name}", [
            'spare_part_id' => $part->id,
            'current_qty'   => $currentQty,
            'minimum_qty'   => $part->minimum_quantity,
        ]);
    }
}
