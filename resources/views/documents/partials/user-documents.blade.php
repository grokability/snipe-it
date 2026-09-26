@php
    $userDocuments = \App\Models\Document::forUser($user->id)
        ->with(['templateVersion.template'])
        ->orderByDesc('id')
        ->get();
@endphp

<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>{{ trans('documents.general.number') }}</th>
                <th>{{ trans('documents.general.type') }}</th>
                <th>{{ trans('documents.general.status') }}</th>
                <th>{{ trans('documents.general.signatures') }}</th>
                <th>{{ trans('general.created_at') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($userDocuments as $document)
                @php
                    $progress = $document->signatureProgress();
                    $remaining = count(array_diff($progress['required'], $progress['obtained']));
                @endphp
                <tr>
                    <td><a href="{{ route('documents.show', $document) }}">{{ $document->number }}</a></td>
                    <td>{{ ucfirst($document->type) }}</td>
                    <td>{{ str_replace('_', ' ', ucfirst($document->status)) }}</td>
                    <td>{{ count($progress['obtained']) }} / {{ count($progress['required']) }}</td>
                    <td>{{ $document->created_at }}</td>
                </tr>
            @empty
                <tr><td colspan="5">{{ trans('documents.general.no_documents') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
