@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.categories') }}
@parent
@stop


{{-- Page content --}}
@section('content')
    <x-container>
        <x-box name="category" sr_only_title>

            <x-slot:table_header>{{ trans('general.categories') }}</x-slot:table_header>

            <x-slot:bulkactions>
                <x-table.bulk-categories />
            </x-slot:bulkactions>

            <x-table
                    name="category"
                    buttons="categoryButtons"
                    fixed_right_number="1"
                    fixed_number="1"
                    show_advanced_search="true"
                    api_url="{{ route('api.categories.index') }}"
                    :presenter="\App\Presenters\CategoryPresenter::dataTableLayout()"
                    export_filename="export-categories-{{ date('Y-m-d') }}"
            />
        </x-box>
        <x-shiftclick/>
    </x-container>

@stop

@section('moar_scripts')
  @include ('partials.bootstrap-table',
      ['exportFile' => 'category-export',
      'search' => true,
      'columns' => \App\Presenters\CategoryPresenter::dataTableLayout()
  ])

<x-table.advanced-search
    tableId="categoryListingTable"
    :model="\App\Models\Category::class"
/>
@stop

