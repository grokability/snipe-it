@extends('layouts/default')

@section('title')
    {{ trans('documents.general.create') }}
    @parent
@stop

@section('content')

    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <p class="lead">{{ trans('documents.general.start_help') }}</p>
            @if ($templates->isEmpty())
                <div class="alert alert-info">
                    <p>{{ trans('documents.general.template_setup_help') }}</p>
                    @can('view', \App\Models\DocumentTemplate::class)
                        <a href="{{ route('documents.templates.index') }}">{{ trans('documents.general.templates') }}</a>
                    @endcan
                </div>
            @else
                @can('view', \App\Models\Asset::class)
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-print" aria-hidden="true"></i> {{ trans('documents.general.all_assigned') }}</h3>
                        </div>
                        <div class="box-body">
                            <p>{{ trans('documents.general.all_assigned_help') }}</p>
                            <form method="POST" action="{{ route('documents.print') }}" target="_blank" class="form-horizontal">
                                @csrf
                                <input type="hidden" name="source_type" value="user">
                                <x-input.user-select :label="trans('documents.general.assignee')" name="source_id"
                                    :selected="old('source_id')" :required="true" :hideNewButton="true"
                                    id="print_assigned_user" wrapperId="print_assigned_user_group" />
                                <div class="form-group">
                                    <div class="col-md-7 col-md-offset-3">
                                        <x-documents.template-select id="print_document_template" />
                                    </div>
                                </div>
                                <x-form.row :label="trans('documents.general.notes')" name="print_notes">
                                    <x-slot:input>
                                        <textarea class="form-control" id="print_notes" name="notes" rows="2" maxlength="1000">{{ old('source_type') ? old('notes') : '' }}</textarea>
                                        <x-form.error name="notes" />
                                    </x-slot:input>
                                </x-form.row>
                                <div class="col-md-9 col-md-offset-3">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-print" aria-hidden="true"></i> {{ trans('documents.general.generate_pdf') }}</button>
                                    <p class="help-block">{{ trans('documents.general.create_help') }}</p>
                                </div>
                            </form>
                        </div>
                    </div>
                @endcan
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('documents.general.selected_assets') }}</h3>
                </div>
                <div class="box-body">

                    <p>{{ trans('documents.general.selected_assets_help') }}</p>
                    <form method="POST" action="{{ route('documents.store') }}" accept-charset="UTF-8" class="form-horizontal">
                        {{ csrf_field() }}

                        <x-form.row
                            :label="trans('documents.general.template')"
                            name="document_template_id"
                            input_div_class="col-md-7 required"
                        >
                            <x-slot:input>
                                <x-input.select
                                    name="document_template_id"
                                    id="document_template_id"
                                    style="width: 100%;"
                                    data-placeholder="{{ trans('documents.general.choose_template') }}"
                                    :selected="old('document_template_id')"
                                    :includeEmpty="true"
                                    :options="$templates->mapWithKeys(fn ($t) => [$t->id => $t->name.' ('.ucfirst($t->type).' — v'.$t->currentVersion->version.')'])->all()"
                                    required="required"
                                    aria-label="document_template_id"
                                />
                                <x-form.error name="document_template_id" />
                            </x-slot:input>
                        </x-form.row>

                        <x-input.user-select
                            :label="trans('documents.general.assignee')"
                            name="assigned_to_id"
                            :selected="old('assigned_to_id')"
                            :required="true"
                            :hideNewButton="true"
                        />

                        <x-input.asset-select
                            :label="trans('documents.general.assets')"
                            name="asset_ids"
                            :selected="old('asset_ids', [])"
                            :required="true"
                            :multiple="true"
                        />

                        <x-form.row
                            :label="trans('documents.general.notes')"
                            name="notes"
                        >
                            <x-slot:input>
                                <textarea class="form-control" name="notes" id="notes" cols="50" rows="3">{{ old('notes') }}</textarea>
                                <x-form.error name="notes" />
                            </x-slot:input>
                        </x-form.row>

                        <div class="col-md-9 col-md-offset-3">
                            <button type="submit" class="btn btn-primary">{{ trans('documents.general.generate_document') }}</button>
                            <a class="btn btn-default" href="{{ route('documents.index') }}">{{ trans('button.cancel') }}</a>
                            <p class="help-block">{{ trans('documents.general.create_help') }}</p>
                        </div>

                    </form>

                </div>
            </div>
            @endif
        </div>
    </div>
@stop
