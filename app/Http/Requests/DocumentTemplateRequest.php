<?php

namespace App\Http\Requests;

use App\Models\DocumentTemplate;
use App\Services\Documents\TemplateService;

class DocumentTemplateRequest extends Request
{
    public function authorize(): bool
    {
        return $this->user()->can($this->route('template') ? 'update' : 'create', DocumentTemplate::class);
    }

    public function attributes(): array
    {
        return [
            'eula_title' => trans('documents.general.eula_title'),
            'eula_body' => trans('documents.general.eula_body'),
            'eula_version' => trans('documents.general.eula_version'),
        ];
    }

    public function rules(): array
    {
        return array_merge(app(TemplateService::class)->rules(), [
            'body' => ['nullable', 'string', 'max:60000'],
            'eula_enabled' => ['sometimes', 'boolean'],
            'eula_title' => ['required_if:eula_enabled,1', 'nullable', 'string', 'max:191'],
            'eula_body' => ['required_if:eula_enabled,1', 'nullable', 'string', 'max:60000'],
            'eula_version' => ['required_if:eula_enabled,1', 'nullable', 'string', 'max:40'],
            'eula_format' => ['sometimes', 'in:plain,markdown'],
        ]);
    }
}
