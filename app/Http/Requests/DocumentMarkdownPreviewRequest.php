<?php

namespace App\Http\Requests;

use App\Models\DocumentTemplate;
use App\Services\Documents\DocumentLayout;

class DocumentMarkdownPreviewRequest extends Request
{
    public function authorize(): bool
    {
        return $this->user()->can('create', DocumentTemplate::class)
            || $this->user()->can('update', DocumentTemplate::class);
    }

    public function rules(): array
    {
        return array_merge([
            'text' => ['nullable', 'string', 'max:60000'],
            'format' => ['required', 'in:plain,markdown'],
            'header_config' => ['sometimes', 'array:date_calendar,date_format'],
        ], array_intersect_key(DocumentLayout::rules(), array_flip(['header_config.date_calendar', 'header_config.date_format'])));
    }
}
