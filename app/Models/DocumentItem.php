<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentItem extends Model
{
    public const CONDITIONS = ['new', 'good', 'fair', 'damaged', 'needs_repair'];

    protected $casts = [
        'snapshot' => 'json',
    ];

    protected $fillable = [
        'document_id', 'item_id', 'asset_tag', 'name', 'model_name',
        'manufacturer_name', 'serial', 'status_name', 'condition', 'condition_notes', 'snapshot',
    ];

    protected static function booted(): void
    {
        static::updating(fn ($item) => ! $item->document?->isImmutable());
        static::deleting(fn ($item) => ! $item->document?->isImmutable());
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
