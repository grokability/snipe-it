@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/manufacturers/table.asset_manufacturers') }} 
@parent
@stop

{{-- Page content --}}
@section('content')
    <x-container>
        <x-box name="manufacturer" sr_only_title>

            @if ($manufacturer_count == 0)

                    <form action="{{ route('manufacturers.seed') }}" method="POST">
                      {{ csrf_field() }}
                    <x-callout type="info" role="status">
                        {{ trans('general.seeding.manufacturers.prompt') }}
                        <button class="btn btn-sm btn-theme hidden-print" rel="noopener">
                          {{ trans('general.seeding.manufacturers.button') }}
                        </button>
                    </x-callout>
                    </form>

              @else
                <x-slot:table_header>{{ trans('admin/manufacturers/table.asset_manufacturers') }}</x-slot:table_header>

                <x-slot:bulkactions>
                    <x-table.bulk-manufacturers />
                </x-slot:bulkactions>


                <x-table
                        name="manufacturer"
                        buttons="manufacturerButtons"
                        fixed_right_number="1"
                        fixed_number="1"
                        show_advanced_search="true"
                        api_url="{{ route('api.manufacturers.index') }}"
                        :presenter="\App\Presenters\ManufacturerPresenter::dataTableLayout()"
                        export_filename="export-manufacturers-{{ date('Y-m-d') }}"
                />




            @endif
        </x-box>
        <x-shiftclick/>
    </x-container>
@stop

@section('moar_scripts')


  @include ('partials.bootstrap-table')

<x-table.advanced-search
    tableId="manufacturerListingTable"
    :model="\App\Models\Manufacturer::class"
/>
@stop
