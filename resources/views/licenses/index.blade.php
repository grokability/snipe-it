@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/licenses/general.software_licenses') }}
@parent
@stop


{{-- Page content --}}
@section('content')
    <x-container>
        <x-box name="licenses" sr_only_title>

            <x-slot:bulkactions>
                <x-table.bulk-licenses />
            </x-slot:bulkactions>

            <x-table.licenses
                fixed_right_number="2"
                fixed_number="1"
                show_footer="true"
                show_advanced_search="true"
                name="licenses"
                :route="route('api.licenses.index', ['status' => e(request('status'))])"/>

        </x-box>
        <x-shiftclick/>
    </x-container>
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table')

<x-table.advanced-search
    tableId="licensesListingTable"
    :model="\App\Models\License::class"
/>
@stop
