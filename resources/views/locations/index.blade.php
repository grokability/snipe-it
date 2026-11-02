@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.locations') }}
@parent
@stop


@section('content')
    <x-container>
        <x-box name="locations" sr_only_title>
            {{-- Convert hand-rolled <table> to the shared x-table.locations
                 component so sticky-column CSS (snipe-table--sticky-right-1)
                 is wired the same way as every other list page. Preserves
                 the company_id / status query-string filtering the index
                 has always supported. --}}
            <x-table.locations :route="route('api.locations.index', ['company_id' => e(request('company_id')), 'status' => e(request('status'))])" />
        </x-box>
        <x-shiftclick/>
    </x-container>
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'locations-export', 'search' => true])

<x-table.advanced-search
    tableId="locationsListingTable"
    :model="\App\Models\Location::class"
/>
@stop
