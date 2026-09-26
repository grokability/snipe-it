<?php

namespace App\Models;

use App\Models\Traits\CompanyableChildTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use CompanyableChildTrait, SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_GENERATED = 'generated';

    public const STATUS_PENDING_SIGNATURE = 'pending_signature';

    public const STATUS_PARTIALLY_SIGNED = 'partially_signed';

    public const STATUS_SIGNED = 'signed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_GENERATED,
        self::STATUS_PENDING_SIGNATURE,
        self::STATUS_PARTIALLY_SIGNED,
        self::STATUS_SIGNED,
        self::STATUS_CANCELLED,
    ];

    // Legal state transitions (spec §8). Signing may start straight from
    // generated (markPendingSignature is the explicit "sent out" step).
    public const TRANSITIONS = [
        self::STATUS_DRAFT => [self::STATUS_GENERATED, self::STATUS_CANCELLED],
        self::STATUS_GENERATED => [self::STATUS_PENDING_SIGNATURE, self::STATUS_PARTIALLY_SIGNED, self::STATUS_SIGNED, self::STATUS_CANCELLED],
        self::STATUS_PENDING_SIGNATURE => [self::STATUS_PARTIALLY_SIGNED, self::STATUS_SIGNED, self::STATUS_CANCELLED],
        self::STATUS_PARTIALLY_SIGNED => [self::STATUS_PARTIALLY_SIGNED, self::STATUS_SIGNED, self::STATUS_CANCELLED],
    ];

    protected $casts = [
        'data' => 'json',
        'signed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected $fillable = [
        'number', 'type', 'status', 'document_template_version_id',
        'assigned_to_id', 'company_id', 'checkout_log_id', 'data',
        'pdf_path', 'pdf_sha256', 'notes', 'created_by',
    ];

    protected static function booted(): void
    {
        static::updating(function (Document $document) {
            return ! in_array($document->getOriginal('status'), [self::STATUS_SIGNED, self::STATUS_CANCELLED], true);
        });
        static::deleting(fn (Document $document) => ! $document->isImmutable());
    }

    public function getCompanyableParents()
    {
        return ['assignedTo'];
    }

    public function templateVersion(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplateVersion::class, 'document_template_version_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DocumentItem::class);
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(DocumentSignature::class);
    }

    public function isSigned(): bool
    {
        return $this->status === self::STATUS_SIGNED;
    }

    public function isImmutable(): bool
    {
        return in_array($this->status, [self::STATUS_SIGNED, self::STATUS_CANCELLED]);
    }

    /**
     * Signature progress against the roles the template version requires.
     *
     * @return array{required: string[], obtained: string[]}
     */
    public function signatureProgress(): array
    {
        $required = $this->templateVersion?->snapshotField('signature_config.roles', ['employee', 'it_representative']) ?? ['employee', 'it_representative'];
        $obtained = $this->signatures()->whereNotNull('signed_at')->pluck('role')->all();

        return ['required' => $required, 'obtained' => $obtained];
    }

    public function scopeForAsset($query, int $assetId)
    {
        return $query->whereHas('items', fn ($q) => $q->where('item_id', $assetId));
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('assigned_to_id', $userId);
    }
}
