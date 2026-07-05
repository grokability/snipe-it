@extends('layouts/default')

{{-- Page title --}}
@section('title')
  {{ trans('general.companies') }}
  @parent
@stop

{{-- Page content --}}
@section('content')
    <x-container>
            <x-box>

                <x-slot:bulkactions>
                    <x-table.bulk-actions
                            name='company'
                            action_route="{{ route('companies.bulk.delete') }}"
                            model_name="company"
                    >
                        @can('delete', App\Models\Company::class)
                            <option>{{ trans('general.delete') }}</option>
                        @endcan
                    </x-table.bulk-actions>
                </x-slot:bulkactions>

                <x-table
                        name="company"
                        buttons="companyButtons"
                        fixed_right_number="1"
                        fixed_number="1"
                        api_url="{{ route('api.companies.index') }}"
                        :presenter="\App\Presenters\CompanyPresenter::dataTableLayout()"
                        export_filename="export-companies-{{ date('Y-m-d') }}"
                />

            </x-box>
    </x-container>
@stop

@section('moar_scripts')
  @include ('partials.bootstrap-table')
@stop
