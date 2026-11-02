@extends('layouts/default')

{{-- Page title --}}
@section('title')

  @if (request()->input('status')=='deleted')
    {{ trans('admin/models/general.view_deleted') }}
    {{ trans('admin/models/table.title') }}
    @else
    {{ trans('admin/models/general.view_models') }}
  @endif
@parent
@stop

{{-- Page content --}}
@section('content')
    <x-container>
        <x-box name="models" sr_only_title>

            <x-slot:table_header>{{ trans('general.asset_models') }}</x-slot:table_header>

            <x-slot:bulkactions>
                <x-table.bulk-models />
            </x-slot:bulkactions>

            <x-table
                    name="models"
                    show_column_search="false"
                    show_advanced_search="true"
                    show_footer="true"
                    buttons="modelButtons"
                    fixed_right_number="2"
                    fixed_number="1"
                    toolbar_id="modelsToolbar"
                    api_url="{{ route('api.models.index', ['status' => e(request('status'))]) }}"
                    :presenter="\App\Presenters\AssetModelPresenter::dataTableLayout()"
                    export_filename="export-models-{{ date('Y-m-d') }}"
            />

        </x-box>
        <x-shiftclick/>
    </x-container>
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'models-export', 'search' => true])

<x-table.advanced-search
    tableId="modelsListingTable"
    :model="\App\Models\AssetModel::class"
/>
@stop
