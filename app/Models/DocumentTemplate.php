<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentTemplate extends Model
{
    use SoftDeletes;

    public const TYPES = ['checkout', 'handover', 'return'];

    /**
     * Validation rules — also consumed by Helper::fieldMaxLength() in forms.
     */
    public static function rules(): array
    {
        return [
            'name' => 'required|string|max:191',
            'slug' => 'nullable|string|max:191',
            'type' => 'required|string|max:20',
            'language' => 'nullable|string|max:10',
            'page_size' => 'nullable|string|max:10',
            'orientation' => 'nullable|string|max:2',
        ];
    }

    protected $casts = [
        'eula_config' => 'json',
        'header_config' => 'json',
        'footer_config' => 'json',
        'signature_config' => 'json',
        'asset_columns' => 'json',
        'active' => 'boolean',
    ];

    protected $fillable = [
        'name', 'slug', 'type', 'language', 'page_size', 'orientation',
        'header_config', 'footer_config', 'signature_config', 'asset_columns',
        'eula_config', 'body', 'active', 'created_by', 'updated_by',
    ];

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentTemplateVersion::class, 'document_template_id');
    }

    public function currentVersion()
    {
        return $this->hasOne(DocumentTemplateVersion::class, 'document_template_id')
            ->orderBy('version', 'desc');
    }

    public function documents(): HasManyThrough
    {
        return $this->hasManyThrough(Document::class, DocumentTemplateVersion::class,
            'document_template_id', 'document_template_version_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', '=', 1);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
