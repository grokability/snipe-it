<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentSignature extends Model
{
    public const ROLES = ['employee', 'it_representative', 'manager', 'custom'];

    public const METHOD_PRINTED = 'printed';

    public const METHOD_DIGITAL = 'digital';

    protected $casts = [
        'signed_at' => 'datetime',
        'metadata' => 'json',
    ];

    protected $fillable = [
        'document_id', 'role', 'signer_user_id', 'signer_name',
        'signature_filename', 'method', 'signed_at', 'remote_ip',
        'user_agent', 'checkout_acceptance_id', 'metadata',
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

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signer_user_id');
    }
}
