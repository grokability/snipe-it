<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentTemplateVersion extends Model
{
    protected $casts = [
        'snapshot' => 'json',
        'eula_enabled' => 'boolean',
    ];

    protected $fillable = [
        'document_template_id', 'version', 'snapshot',
        'eula_enabled', 'eula_title', 'eula_body', 'eula_version', 'published_by',
    ];

    /**
     * Versions are immutable after creation — no update() path exists.
     * Guard at model level too: block any save on an existing row.
     */
    public static function boot(): void
    {
        parent::boot();

        static::updating(function ($version) {
            // Allow nothing. A published version is a permanent snapshot.
            return false;
        });

        static::deleting(function ($version) {
            // Versions referenced by documents must never disappear.
            return Document::where('document_template_version_id', $version->id)->doesntExist();
        });
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'document_template_id');
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function snapshotField(string $key, $default = null)
    {
        return data_get($this->snapshot, $key, $default);
    }
}
