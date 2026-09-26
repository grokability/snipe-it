@props(['id' => 'document_template_version_id', 'selected' => null, 'required' => true])
@php
    $documentTemplates = app(\App\Services\Documents\DocumentPrintService::class)->templates();
    $selectedVersionId = old('document_template_version_id', $selected);
    $savedVersion = $selectedVersionId && !$documentTemplates->contains(fn ($item) => (string) $item->currentVersion->id === (string) $selectedVersionId)
        ? \App\Models\DocumentTemplateVersion::whereHas('template', fn ($item) => $item->active())->find($selectedVersionId)
        : null;
@endphp
<label for="{{ $id }}">{{ trans('documents.general.template') }}</label>
<select id="{{ $id }}" name="document_template_version_id" class="form-control" @required($required)>
    <option value="">{{ trans('documents.general.choose_template') }}</option>
    @foreach ($documentTemplates as $documentTemplate)
        <option value="{{ $documentTemplate->currentVersion->id }}" @selected((string) old('document_template_version_id', $selected) === (string) $documentTemplate->currentVersion->id)>
            {{ $documentTemplate->currentVersion->snapshotField('name') }} — {{ ucfirst($documentTemplate->currentVersion->snapshotField('type')) }} — v{{ $documentTemplate->currentVersion->version }}
        </option>
    @endforeach
    @if ($savedVersion)
        <option value="{{ $savedVersion->id }}" selected>{{ $savedVersion->snapshotField('name') }} — {{ ucfirst($savedVersion->snapshotField('type')) }} — v{{ $savedVersion->version }}</option>
    @endif
</select>
<x-form.error name="document_template_version_id" />
@if ($documentTemplates->isEmpty())
    <p class="help-block">{{ trans('documents.general.no_published_version') }}</p>
@endif
@can('view', \App\Models\DocumentTemplate::class)
    <a href="{{ route('documents.templates.index') }}" target="_blank" rel="noopener">{{ trans('documents.general.templates') }}</a>
@endcan
