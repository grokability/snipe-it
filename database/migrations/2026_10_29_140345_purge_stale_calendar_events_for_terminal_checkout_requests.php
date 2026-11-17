<?php

use App\Models\CalendarEvent;
use App\Models\CheckoutRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * One-shot cleanup for installs where the user- or archive-initiated
 * cancel paths flipped checkout_requests to a terminal state (canceled
 * or fulfilled) without the HasCalendarEvents observer firing.
 *
 * Delete every calendar_events row whose source CheckoutRequest is
 * no longer pending.
 */
return new class extends Migration
{
    public function up(): void
    {
        $terminalIds = CheckoutRequest::query()
            ->where(function ($q) {
                $q->whereNotNull('canceled_at')
                    ->orWhereNotNull('fulfilled_at');
            })
            ->pluck('id')
            ->all();

        if ($terminalIds === []) {
            return;
        }

        // Chunk the IN list so an install with tens of thousands of
        // historically-canceled rows does not blow MySQL's
        // prepared-statement placeholder ceiling on a single DELETE.
        foreach (array_chunk($terminalIds, 1000) as $chunk) {
            DB::table((new CalendarEvent)->getTable())
                ->where('source_type', CheckoutRequest::class)
                ->whereIn('source_id', $chunk)
                ->delete();
        }
    }

    public function down(): void
    {
        // No-op.
    }
};
