@props(['user', 'assetIds', 'acceptance' => null])
@php
    $printContext = $acceptance ? 'acceptance-'.$acceptance->id : 'user-'.$user->id;
    $printHistory = app(\App\Services\Documents\DocumentPrintService::class)->history($user, collect($assetIds));
@endphp
@can('create', \App\Models\Document::class)
    @can('view', \App\Models\Asset::class)
        @can('download', new \App\Models\Document(['assigned_to_id' => $user->id, 'company_id' => $user->legacy_company_id]))
            <div class="well hidden-print" data-document-print-context="{{ $printContext }}">
                <h4>{{ trans('documents.general.print_documents') }}</h4>
                <p>{{ trans($acceptance ? 'documents.general.acceptance_print_help' : 'documents.general.print_help') }}</p>
                @if (count($assetIds) === 0)
                    <p class="text-muted">{{ trans('documents.general.no_print_assets') }}</p>
                @else
                <form method="POST" action="{{ route('documents.print') }}" target="_blank">
                    @csrf
                    <input type="hidden" name="source_type" value="{{ $acceptance ? 'acceptance' : 'user' }}">
                    <input type="hidden" name="source_id" value="{{ $acceptance ? $acceptance->id : $user->id }}">
                    <div class="form-group">
                        <x-documents.template-select :id="'template-'.$printContext" />
                    </div>
                    <div class="form-group">
                        <label for="notes-{{ $printContext }}">{{ trans('documents.general.notes') }}</label>
                        <textarea id="notes-{{ $printContext }}" name="notes" class="form-control" rows="2" maxlength="1000">{{ old('notes') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-print" aria-hidden="true"></i> {{ trans('documents.general.generate_pdf') }}</button>
                </form>
                @endif
            </div>
        @endcan
    @endcan
@endcan
@if ($printHistory->isNotEmpty())
    <h4>{{ trans('documents.general.existing_documents') }}</h4>
    <div class="table-responsive">
        <table class="table table-striped inventory">
            <thead><tr>
                <th>{{ trans('documents.general.number') }}</th>
                <th>{{ trans('documents.general.type') }}</th>
                <th>{{ trans('documents.general.status') }}</th>
                <th>{{ trans('documents.general.template') }}</th>
                <th>{{ trans('documents.general.signed_at') }}</th>
                <th class="hidden-print">{{ trans('documents.general.print_pdf') }}</th>
            </tr></thead>
            <tbody>
            @foreach ($printHistory as $printDocument)
                <tr>
                    <td><a href="{{ route('documents.show', $printDocument) }}">{{ $printDocument->number }}</a></td>
                    <td>{{ ucfirst($printDocument->type) }}</td>
                    <td>{{ str_replace('_', ' ', $printDocument->status) }}</td>
                    <td>{{ $printDocument->templateVersion?->snapshotField('name') }} — v{{ $printDocument->templateVersion?->version }}</td>
                    <td>{{ $printDocument->signed_at }}</td>
                    <td class="hidden-print">
                        @can('download', $printDocument)
                            <a href="{{ route('documents.pdf', ['document' => $printDocument, 'inline' => 1]) }}" target="_blank" rel="noopener">{{ trans('documents.general.print_pdf') }}</a>
                        @endcan
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif
