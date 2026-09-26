<?php

namespace App\Services\Documents;

use App\Models\Document;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

/**
 * Atomic document numbering (spec §9): one global sequence, per-type prefixes,
 * CO-2026-000142 style. Uniqueness is backed by the documents.number unique
 * index; concurrent callers retry on collision.
 */
class DocumentNumberService
{
    public const PREFIX_MAP = [
        'checkout' => 'document_prefix_checkout',
        'handover' => 'document_prefix_handover',
        'return' => 'document_prefix_return',
    ];

    private const MAX_ATTEMPTS = 5;

    /**
     * Reserve the next number for a document type.
     */
    public function next(string $type): string
    {
        $settings = Setting::getSettings();
        $column = self::PREFIX_MAP[$type] ?? null;

        if ($column === null) {
            throw new \InvalidArgumentException("Unknown document type: {$type}");
        }

        $prefix = $settings->{$column} ?? strtoupper(substr($type, 0, 2)).'-';

        // Atomic increment on the settings row (row lock via the UPDATE itself)
        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $affected = DB::table('settings')
                ->where('id', $settings->id)
                ->update(['document_number_seq' => DB::raw('document_number_seq + 1')]);

            if (! $affected) {
                usleep(50000);
                continue;
            }

            $seq = (int) DB::table('settings')->where('id', $settings->id)->value('document_number_seq');
            $number = sprintf('%s%s-%06d', $prefix, now()->format('Y'), $seq);

            // Defense in depth: the unique index is the real guarantee
            if (! Document::withTrashed()->where('number', $number)->exists()) {
                return $number;
            }
        }

        throw new \RuntimeException('Could not allocate a unique document number after '.self::MAX_ATTEMPTS.' attempts.');
    }
}
