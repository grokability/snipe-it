<?php

namespace App\Http\Requests;

use App\Models\Document;

class PrintDocumentRequest extends Request
{
    protected $rules = [
        'source_type' => 'required|in:user,acceptance',
        'source_id' => 'required|integer|min:1',
        'document_template_version_id' => 'required|integer|exists:document_template_versions,id',
        'notes' => 'nullable|string|max:1000',
    ];

    public function authorize(): bool
    {
        return $this->user()?->can('create', Document::class) ?? false;
    }
}
