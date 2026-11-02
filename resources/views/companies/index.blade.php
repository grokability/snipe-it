@extends('layouts/default')

{{-- Page title --}}
@section('title')
  {{ trans('general.companies') }}
  @parent
@stop

{{-- Page content --}}
@section('content')
    <x-container>
            <x-box name="company" sr_only_title>

                <x-slot:table_header>{{ trans('general.companies') }}</x-slot:table_header>

                <x-slot:bulkactions>
                    <x-table.bulk-companies />
                </x-slot:bulkactions>

                <x-table
                        name="company"
                        buttons="companyButtons"
                        fixed_right_number="1"
                        fixed_number="1"
                        show_advanced_search="true"
                        api_url="{{ route('api.companies.index') }}"
                        :presenter="\App\Presenters\CompanyPresenter::dataTableLayout()"
                        export_filename="export-companies-{{ date('Y-m-d') }}"
                />

            </x-box>
    </x-container>
@stop

@section('moar_scripts')
  @include ('partials.bootstrap-table')

<x-table.advanced-search
    tableId="companyListingTable"
    :model="\App\Models\Company::class"
/>
@stop
