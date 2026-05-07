@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.categories') }}
@parent
@stop


{{-- Page content --}}
@section('content')
    <x-container>
        <x-box>

            <x-slot:bulkactions>
                <x-table.bulk-actions
                    name='category'
                    action_route="{{route('categories.bulk.delete')}}"
                    model_name="category"
                >
                @can('delete', App\Models\Category::class)
                    <option>Delete</option>
                @endcan
                </x-table.bulk-actions>
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
    </x-container>
@stop

@section('moar_scripts')
  @include ('partials.bootstrap-table',
      ['exportFile' => 'category-export',
      'search' => true,
      'columns' => \App\Presenters\CategoryPresenter::dataTableLayout()
  ])
@stop

