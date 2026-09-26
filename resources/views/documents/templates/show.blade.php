@extends('layouts/default')

@section('title')
    {{ $item->name }}
    @parent
@stop

@push('css')
<style>.document-terms { overflow-wrap: anywhere; } .document-terms table { width:100%; border-collapse:collapse; } .document-terms td, .document-terms th { border:1px solid #ddd; padding:6px; }</style>
@endpush

@section('content')
    @php $terms = app(\App\Services\Documents\TemplateService::class)->terms($item); @endphp

    <div class="row">
        <div class="col-md-8">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ $item->name }}
                        <label class="label label-default">{{ ucfirst($item->type) }}</label>
                    </h3>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px;">
                        <a class="btn btn-sm btn-default" href="{{ route('documents.templates.preview', $item) }}" target="_blank" rel="noopener"><i class="fa fa-print" aria-hidden="true"></i> {{ trans('documents.general.preview') }}</a>
                        <a class="btn btn-sm btn-default" href="{{ route('documents.templates.preview', ['template' => $item, 'web' => 1]) }}" target="_blank" rel="noopener">{{ trans('documents.custom.web_preview') }}</a>
                        @can('update', $item)
                            <a href="{{ route('documents.templates.edit', $item) }}" class="btn btn-sm btn-warning">{{ trans('documents.general.edit_agreement') }}</a>
                        @endcan
                    </div>
                </div>
                <div class="box-body">

                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <td>{{ trans('general.language') }}</td>
                                <td>{{ $item->language }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('documents.general.page_size') }} / {{ trans('documents.general.orientation') }}</td>
                                <td>{{ $item->page_size }} / {{ $item->orientation == 'P' ? 'Portrait' : 'Landscape' }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('documents.general.signature_config') }}</td>
                                <td>{{ implode(', ', array_map(fn ($r) => ucfirst(str_replace('_', ' ', $r)), $item->signature_config['roles'] ?? [])) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <p class="help-block">{{ trans('documents.general.preview_help') }}</p>
                    <h3>{{ trans('documents.general.terms_draft') }}</h3>
                    @if ($terms['eula_enabled'])
                        <h4>{{ $terms['eula_title'] ?: trans('documents.general.eula') }}</h4>
                        <p class="text-muted">{{ trans('documents.general.eula_version') }}: {{ $terms['eula_version'] }}</p>
                        <div class="well document-terms">{!! app(\App\Services\Documents\TemplateService::class)->renderTerms($terms['eula_body'], $terms['eula_format']) !!}</div>
                    @else
                        <p>{{ trans('documents.general.terms_disabled') }}</p>
                    @endif
                    @if ($item->body)
                        <details style="margin-bottom: 20px;">
                            <summary>{{ trans('documents.general.body') }}</summary>
                            <pre style="white-space: pre-wrap;">{{ $item->body }}</pre>
                        </details>
                    @endif

                    <h4>{{ trans('documents.general.versions') }}</h4>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ trans('documents.general.version') }}</th>
                                <th>{{ trans('documents.general.eula') }}</th>
                                <th>{{ trans('documents.general.published_by') }}</th>
                                <th>{{ trans('documents.general.published_at') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($versions as $version)
                                <tr>
                                    <td>v{{ $version->version }}</td>
                                    <td>{{ $version->eula_enabled ? ($version->eula_version ?: trans('general.yes')) : '—' }}</td>
                                    <td>{{ $version->publishedBy?->present()->fullName ?? '—' }}</td>
                                    <td>{{ $version->created_at }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4">{{ trans('documents.general.no_versions') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </div>
        </div>

        <div class="col-md-4">
            @can('update', $item)
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('documents.general.publish_saved') }}</h3>
                </div>
                <div class="box-body">
                    <p class="text-muted small">{{ trans('documents.general.working_copy_note') }}</p>
                    <form method="POST" action="{{ route('documents.templates.publish', $item) }}">
                        {{ csrf_field() }}

                        <p>{{ trans('documents.general.terms_snapshot_help') }}</p>
                        @foreach (['eula_title', 'eula_body', 'eula_version'] as $field)
                            <x-form.error :name="$field" />
                        @endforeach
                        <button type="submit" class="btn btn-theme">{{ trans('documents.general.publish_saved') }}</button>
                    </form>
                </div>
            </div>
            @endcan

            @can('delete', $item)
                <form method="POST" action="{{ route('documents.templates.destroy', $item) }}" onsubmit="return confirm('{{ trans('general.confirm_delete') }}');">
                    {{ csrf_field() }}
                    {{ method_field('DELETE') }}
                    <button type="submit" class="btn btn-danger btn-sm">{{ trans('general.delete') }}</button>
                </form>
            @endcan
        </div>
    </div>
@stop
