@extends('layouts/default')

{{-- Page title --}}
@section('title')
  {{ trans('general.depreciations')}}
@parent
@stop


{{-- Page content --}}
@section('content')
    <x-container>
        <x-box name="depreciation">

            <x-slot:bulkactions>
                <x-table.bulk-actions
                        name='depreciation'
                        action_route="{{ route('depreciations.bulk.delete') }}"
                        model_name="depreciation"
                >
                    @can('delete', App\Models\Depreciation::class)
                        <option>{{ trans('general.delete') }}</option>
                    @endcan
                </x-table.bulk-actions>
            </x-slot:bulkactions>

            <x-table
                    name="depreciation"
                    show_column_search="false"
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
@stop
