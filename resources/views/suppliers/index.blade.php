@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/suppliers/table.suppliers') }}
@parent
@stop

{{-- Page content --}}
@section('content')
    <x-container>
        <x-box name="supplier" sr_only_title>

            <x-slot:table_header>{{ trans('admin/suppliers/table.suppliers') }}</x-slot:table_header>

            <x-slot:bulkactions>
                <x-table.bulk-suppliers />
            </x-slot:bulkactions>


            <x-table
                name="supplier"
                buttons="supplierButtons"
                fixed_right_number="1"
                fixed_number="1"
                show_advanced_search="true"
                api_url="{{ route('api.suppliers.index') }}"
                :presenter="\App\Presenters\SupplierPresenter::dataTableLayout()"
                export_filename="export-suppliers-{{ date('Y-m-d') }}"
            />

        </x-box>
        <x-shiftclick/>
    </x-container>
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'suppliers-export', 'search' => true])

<x-table.advanced-search
    tableId="supplierListingTable"
    :model="\App\Models\Supplier::class"
/>
@stop
