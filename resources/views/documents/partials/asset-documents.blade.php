@php
    $assetDocuments = \App\Models\Document::forAsset($asset->id)
        ->with(['assignedTo', 'templateVersion.template'])
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
                <th>{{ trans('documents.general.assignee') }}</th>
                <th>{{ trans('general.created_at') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($assetDocuments as $document)
                <tr>
                    <td><a href="{{ route('documents.show', $document) }}">{{ $document->number }}</a></td>
                    <td>{{ ucfirst($document->type) }}</td>
                    <td>{{ str_replace('_', ' ', ucfirst($document->status)) }}</td>
                    <td>{{ $document->assignedTo?->present()->fullName }}</td>
                    <td>{{ $document->created_at }}</td>
                </tr>
            @empty
                <tr><td colspan="5">{{ trans('documents.general.no_documents') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
