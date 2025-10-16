@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.asset_report') }}
@parent
@stop

@section('header_right')
<a href="{{ route('reports/export/assets') }}" class="btn btn-default"><i class="fas fa-download icon-white" aria-hidden="true"></i>
{{ trans('admin/hardware/table.dl_csv') }}</a>
@stop

{{-- Page content --}}
@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-body">
                <div class="table-responsive">

                    <table
                    name="assetsReport"
                    class="table table-striped snipe-table"
                    data-advanced-search="false"
                    id="table"
                    data-url="{{route('api.assets.index', array(''=>e(Request::get('status')),'order_number'=>e(Request::get('order_number')), 'status_id'=>e(Request::get('status_id')), 'report'=>'true'))}}"
                    data-cookie="true"
                    data-cookie-id-table="{{ e(Request::get('status')) }}assetTable-{{ config('version.hash_version') }}">
                        <thead>
                            <tr>
                                @if (Request::get('status')!='Deleted')
                                <th data-class="hidden-xs" data-switchable="false" data-searchable="false" data-sortable="false" data-field="checkbox"><div class="text-center"><input type="checkbox" id="checkAll" style="padding-left: 0px;"></div></th>
                                @endif
                                <th data-sortable="true" data-field="id" data-visible="false">{{ trans('general.id') }}</th>
                                <th data-field="company" data-searchable="true" data-sortable="true" data-switchable="true" data-visible="false">{{ trans('general.company') }}</th>
                                <th data-sortable="true" data-field="name" data-visible="false">{{ trans('admin/hardware/form.name') }}</th>
                                <th data-sortable="true" data-field="asset_tag">{{ trans('admin/hardware/table.asset_tag') }}</th>
                                <th data-sortable="true" data-field="serial">{{ trans('admin/hardware/table.serial') }}</th>
                                <th data-sortable="true" data-field="model">{{ trans('admin/hardware/form.model') }}</th>
                                <th data-sortable="true" data-field="model_number" data-visible="false">{{ trans('admin/models/table.modelnumber') }}</th>
                                <th data-sortable="true" data-field="status_label">{{ trans('admin/hardware/table.status') }}</th>
                                <th data-sortable="true" data-field="assigned_to">{{ trans('admin/hardware/form.checkedout_to') }}</th>
                                <th data-sortable="true" data-field="employee_number">{{ trans('general.employee_number') }}</th>
                                <th data-sortable="true" data-field="location" data-searchable="true">{{ trans('admin/hardware/table.location') }}</th>
                                <th data-sortable="true" data-field="category" data-searchable="true">{{ trans('general.category') }}</th>
                                <th data-sortable="true" data-field="manufacturer" data-searchable="true" data-visible="false">{{ trans('general.manufacturer') }}</th>
                                <th data-sortable="true" data-field="purchase_cost" data-searchable="true" data-visible="false">{{ trans('admin/hardware/form.cost') }}</th>
                                <th data-sortable="true" data-field="purchase_date" data-searchable="true" data-visible="false">{{ trans('admin/hardware/form.date') }}</th>
                                <th data-sortable="false" data-field="eol" data-searchable="true">{{ trans('general.eol') }}</th>
                                <th data-sortable="true" data-searchable="true" data-field="notes">{{ trans('general.notes') }}</th>
                                <th data-sortable="true" data-searchable="true"  data-field="order_number">{{ trans('admin/hardware/form.order') }}</th>
                                <th data-sortable="true" data-searchable="true" data-field="last_checkout">{{ trans('admin/hardware/table.checkout_date') }}</th>
                                <th data-sortable="true" data-field="expected_checkin" data-searchable="true">{{ trans('admin/hardware/form.expected_checkin') }}</th>
                                @foreach(\App\Models\CustomField::all() AS $field)


                                <th data-sortable="{{ ($field->field_encrypted=='1' ? 'false' : 'true') }}" data-visible="false" data-field="{{$field->db_column_name()}}">
                                    @if ($field->field_encrypted=='1')
                                    <i class="fas fa-lock"></i>
                                    @endif

                                    {{$field->name}}
                                </th>

                                @endforeach
                                <th data-sortable="true" data-field="created_at" data-searchable="true" data-visible="false">{{ trans('general.created_at') }}</th>

                            </tr>
                        </thead>
                    </table>
                </div> <!-- .table-responsive -->
            </div>
        </div>
    </div>
</div>
@stop



@section('moar_scripts')
    @include ('partials.bootstrap-table', [
        'exportFile' => 'assets-export',
        'multiSort' => true,
        'search' => true,
        'columns' => \App\Presenters\AssetPresenter::dataTableLayout()
    ])

@stop
