@extends('layouts/default')

{{-- Page title --}}
@section('title')
  {{ trans('general.depreciations')}}
@parent
@stop


{{-- Page content --}}
@section('content')
    <x-container>
        <x-box name="depreciation" sr_only_title>

            <x-slot:table_header>{{ trans('general.depreciations') }}</x-slot:table_header>

            <x-slot:bulkactions>
                <x-table.bulk-depreciations />
            </x-slot:bulkactions>

            <x-table
                    name="depreciation"
                    show_column_search="false"
                    show_advanced_search="true"
                    buttons="depreciationButtons"
                    fixed_right_number="1"
                    fixed_number="1"
                    api_url="{{ route('api.depreciations.index') }}"
                    :presenter="\App\Presenters\DepreciationPresenter::dataTableLayout()"
                    export_filename="export-depreciations-{{ date('Y-m-d') }}"
            />
        </x-box>
    </x-container>
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'depreciations-export', 'search' => true])

<x-table.advanced-search
    tableId="depreciationListingTable"
    :model="\App\Models\Depreciation::class"
/>
@stop
