@extends('layouts/default')

@section('title')
    {{ trans('documents.general.documents') }}
    @parent
@stop

@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('documents.general.documents') }}</h3>
                    <div class="box-tools pull-right">
                        @can('create', \App\Models\Document::class)
                            <a href="{{ route('documents.create') }}" class="btn btn-sm btn-theme">
                                <x-icon type="plus" /> {{ trans('documents.general.create') }}</a>
                        @endcan
                    </div>
                </div>
                <div class="box-body">
                    <p class="text-muted">{{ trans('documents.general.history_help') }}</p>
                    <form method="GET" action="{{ route('documents.index') }}" class="row" style="margin-bottom: 16px;">
                        <div class="col-sm-6 form-group">
                            <label for="document-search">{{ trans('documents.general.search') }}</label>
                            <input id="document-search" name="search" class="form-control" value="{{ request('search') }}">
                        </div>
                        <div class="col-sm-3 form-group">
                            <label for="document-status">{{ trans('documents.general.status') }}</label>
                            <select id="document-status" name="status" class="form-control">
                                <option value="">{{ trans('documents.general.all_statuses') }}</option>
                                @foreach (\App\Models\Document::STATUSES as $status)
                                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ trans('documents.statuses.'.$status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-3 form-group" style="padding-top: 25px;">
                            <button class="btn btn-primary" type="submit">{{ trans('documents.general.filter') }}</button>
                            <a class="btn btn-default" href="{{ route('documents.index') }}">{{ trans('documents.general.reset_filters') }}</a>
                        </div>
                    </form>
                    @if ($documents->count() > 0)
                        <div class="table-responsive">
                    <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>{{ trans('documents.general.number') }}</th>
                                    <th>{{ trans('documents.general.type') }}</th>
                                    <th>{{ trans('documents.general.status') }}</th>
                                    <th>{{ trans('documents.general.assignee') }}</th>
                                    <th>{{ trans('documents.general.assets') }}</th>
                                    <th>{{ trans('general.created_at') }}</th>
                                    <th>{{ trans('general.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($documents as $document)
                                    <tr>
                                        <td>
                                            <a href="{{ route('documents.show', $document) }}">{{ $document->number }}</a>
                                        </td>
                                        <td>{{ ucfirst($document->type) }}</td>
                                        <td>
                                            <label class="label label-default">{{ trans('documents.statuses.'.$document->status) }}</label>
                                        </td>
                                        <td>
                                            @if ($document->assignedTo)
                                                <a href="{{ route('users.show', $document->assignedTo) }}">{{ $document->assignedTo->present()->fullName }}</a>
                                            @endif
                                        </td>
                                        <td>{{ $document->items_count }}</td>
                                        <td>{{ $document->created_at }}</td>
                                        <td>
                                            @can('download', $document)
                                                @if ($document->pdf_path)
                                                    <a class="btn btn-sm btn-default" href="{{ route('documents.pdf', ['document' => $document, 'inline' => 1]) }}" target="_blank" rel="noopener">
                                                        <i class="fa fa-print" aria-hidden="true"></i> {{ trans('documents.general.print_pdf') }}
                                                    </a>
                                                @endif
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                        <div class="pull-right">{{ $documents->links() }}</div>
                    @else
                        <p>{{ trans(request()->filled('search') || request()->filled('status') ? 'documents.general.no_matches' : 'documents.general.no_documents') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
