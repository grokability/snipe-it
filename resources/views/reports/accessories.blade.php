@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.accessory_report') }}
    @parent
@stop


{{-- Page content --}}
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-body">
                        <table
                                data-cookie-id-table="accessoriesReport"
                                data-id-table="accessoriesReport"
                                data-side-pagination="server"
                                data-sort-order="asc"
                                id="accessoriesReport"
                                data-advanced-search="false"
                                class="table table-striped snipe-table"
                                data-url="{{ route('api.accessories.index') }}"
                                data-export-options='{
                        "fileName": "accessory-report-{{ date('Y-m-d') }}",
                        "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                        }'>

                            <thead>
                            <tr>
                                <th class="col-sm-1" data-field="company.name">{{ trans('admin/companies/table.title') }}</th>
                                <th class="col-sm-1" data-field="name">{{ trans('admin/accessories/table.title') }}</th>
                                <th class="col-sm-1" data-field="model_number">{{ trans('general.model_no') }}</th>
                                <th class="col-sm-1" data-field="qty">{{ trans('admin/accessories/general.total') }}</th>
                                <th class="col-sm-1" data-field="remaining_qty">{{ trans('admin/accessories/general.remaining') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                </div>
            </div>
        </div>


        @stop

        @section('moar_scripts')
            @include ('partials.bootstrap-table')

        @stop


