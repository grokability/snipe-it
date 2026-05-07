@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/licenses/general.software_licenses') }}
@parent
@stop


{{-- Page content --}}
@section('content')
    <x-container>
        <x-box>

            <x-table.licenses
                fixed_right_number="2"
                fixed_number="1"
                show_footer="true"
                show_advanced_search="true"
                name="licenses"
                :route="route('api.licenses.index', ['status' => e(request('status'))])"/>

        </x-box>
    </x-container>
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table')

@stop
