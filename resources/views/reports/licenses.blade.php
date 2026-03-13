@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.license_report') }} 
@parent
@stop

{{-- Page content --}}
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-body">
                    <table
                            data-cookie-id-table="licensesReport"
                            data-id-table="licensesReport"
                            data-side-pagination="client"
                            data-sort-order="asc"
                            id="licensesReport"
                            data-advanced-search="false"
                            class="table table-striped snipe-table"
                            data-export-options='{
                        "fileName": "license-report-{{ date('Y-m-d') }}",
                        "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                        }'>
                        <thead>
                            <tr role="row">
                                <th class="col-sm-1">{{ trans('admin/companies/table.title') }}</th>
                                <th class="col-sm-1">{{ trans('admin/licenses/table.title') }}</th>
                                <th class="col-sm-1">{{ trans('admin/licenses/form.license_key') }}</th>
                                <th class="col-sm-1">{{ trans('admin/licenses/form.seats') }}</th>
                                <th class="col-sm-1">{{ trans('admin/licenses/form.remaining_seats') }}</th>
                                <th class="col-sm-1">{{ trans('admin/licenses/form.expiration') }}</th>
                                <th class="col-sm-1">{{ trans('admin/licenses/form.termination_date') }}</th>
                                <th class="col-sm-1">{{ trans('general.purchase_date') }}</th>
                                <th class="col-sm-1 text-right" class="col-sm-1">{{ trans('general.purchase_cost') }}</th>
                                <th class="col-sm-1">{{ trans('general.depreciation') }}</th>
                                <th class="col-sm-1 text-right">{{ trans('admin/hardware/table.book_value') }}</th>
                                <th class="col-sm-1 text-right">{{ trans('admin/hardware/table.diff') }}</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($licenses as $license)
                            <tr>
                                <td>{{ is_null($license->company) ? '' : $license->company->name }}</td>
                                <td>{{ $license->name }}</td>
                                <td>
                                    @can('viewKeys', $license)
                                        {{ $license->serial }}
                                    @else
                                        ------------
                                    @endcan
                                </td>
                                <td>{{ $license->seats }}</td>
                                <td>{{ $license->remaincount() }}</td>
                                <td>{{ $license->expires_formatted_date }}</td>
                                <td>{{ $license->terminates_formatted_date }}
                                    @if ($license->isTerminated())
                                        <span class="text-danger">
                                        <x-icon type="warning" class="text-warning" />
                                        </span>
                                    @endif</td>
                                <td>{{ $license->purchase_date }}</td>
                                <td class="text-right">
                                    {{ $snipeSettings->default_currency }}{{ Helper::formatCurrencyOutput($license->purchase_cost) }}
                                </td>
                                <td>
                                    {{ ($license->depreciation) ? e($license->depreciation->name).' ('.$license->depreciation->months.' '.trans('general.months').')' : ''  }}
                                </td>
                                <td class="text-right">
                                    {{ $snipeSettings->default_currency }}{{ Helper::formatCurrencyOutput($license->getDepreciatedValue()) }}
                                </td>
                                <td class="text-right">
                                    -{{ $snipeSettings->default_currency }}{{ Helper::formatCurrencyOutput(($license->purchase_cost - $license->getDepreciatedValue())) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
            </div>
        </div>
    </div>
</div>

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')
@stop
