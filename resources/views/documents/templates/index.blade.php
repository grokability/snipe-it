@extends('layouts/default')

@section('title')
    {{ trans('documents.general.templates') }}
    @parent
@stop

@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('documents.general.templates') }}</h3>
                    <div class="box-tools pull-right">
                        @can('create', \App\Models\DocumentTemplate::class)
                            <a href="{{ route('documents.templates.create') }}" class="btn btn-sm btn-theme">
                                <x-icon type="plus" /> {{ trans('documents.general.create_template') }}</a>
                        @endcan
                    </div>
                </div>
                <div class="box-body">
                    @if (count($templates) > 0)
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ trans('general.name') }}</th>
                                    <th>{{ trans('documents.general.type') }}</th>
                                    <th>{{ trans('documents.general.version') }}</th>
                                    <th>{{ trans('general.language') }}</th>
                                    <th>{{ trans('documents.general.active') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($templates as $template)
                                    <tr>
                                        <td><a href="{{ route('documents.templates.show', $template) }}">{{ $template->name }}</a></td>
                                        <td>{{ ucfirst($template->type) }}</td>
                                        <td>@if ($template->currentVersion) v{{ $template->currentVersion->version }} @else — @endif</td>
                                        <td>{{ $template->language }}</td>
                                        <td>
                                            <label class="label label-{{ $template->active ? 'success' : 'default' }}">{{ $template->active ? trans('general.yes') : trans('general.no') }}</label>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>{{ trans('documents.general.no_templates') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
