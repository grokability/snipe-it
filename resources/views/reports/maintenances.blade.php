@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.asset_maintenance_report') }}
    @parent
@stop

{{-- Page content --}}
@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="box box-default">
      <div class="box-body">
            <table
                    data-cookie-id-table="maintenancesReport"
                    data-show-footer="true"
                    data-id-table="maintenancesReport"
                    data-side-pagination="server"
                    data-sort-order="asc"
                    id="maintenancesReport"
                    data-advanced-search="false"
                    data-url="{{route('api.maintenances.index') }}"
                    class="table table-striped snipe-table"
                    data-export-options='{
                        "fileName": "maintenance-report-{{ date('Y-m-d') }}",
                        "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                        }'>
                <thead>
                <tr>
                    <th data-field="company" data-sortable="false" data-visible="false" data-formatter="companiesLinkObjFormatter">{{ trans('admin/companies/table.title') }}</th>
                    <th data-sortable="true" data-field="id" data-visible="false">{{ trans('general.id') }}</th>
                    <th data-sortable="true" data-field="asset_tag" data-formatter="assetTagLinkFormatter" data-visible="false">{{ trans('general.asset_tag') }}</th>
                    <th data-sortable="false" data-field="asset_name" data-formatter="assetNameLinkFormatter">{{ trans('admin/maintenances/table.asset_name') }}</th>
                    <th data-sortable="false" data-field="supplier" data-formatter="suppliersLinkObjFormatter">{{ trans('general.supplier') }}</th>
                    <th data-searchable="true" data-sortable="true" data-field="asset_maintenance_type">{{ trans('admin/maintenances/form.asset_maintenance_type') }}</th>
                    <th data-searchable="true" data-sortable="true" data-field="title">{{ trans('admin/maintenances/form.title') }}</th>
                    <th data-searchable="true" data-sortable="false" data-field="start_date" data-formatter="dateDisplayFormatter">{{ trans('admin/maintenances/form.start_date') }}</th>
                    <th data-searchable="true" data-sortable="true" data-field="completion_date" data-formatter="dateDisplayFormatter">{{ trans('admin/maintenances/form.completion_date') }}</th>
                    <th data-searchable="true" data-sortable="true" data-field="asset_maintenance_time">{{ trans('admin/maintenances/form.asset_maintenance_time') }}</th>
                    <th data-searchable="true" data-sortable="true" data-field="cost" class="text-right" data-footer-formatter="sumFormatter">{{ trans('admin/maintenances/form.cost') }}</th>
                    <th data-sortable="true" data-field="location" data-formatter="deployedLocationFormatter" data-visible="false">{{ trans('general.location') }}</th>
                    <th data-sortable="true" data-field="rtd_location" data-formatter="deployedLocationFormatter" data-visible="false">{{ trans('admin/hardware/form.default_location') }}</th>
                    <th data-searchable="true" data-sortable="true" data-field="is_warranty" data-formatter="trueFalseFormatter">{{ trans('admin/maintenances/table.is_warranty') }}</th>
                    <th data-searchable="true" data-sortable="true" data-field="user_id" data-formatter="usersLinkObjFormatter">{{ trans('general.created_by') }}</th>
                    <th data-searchable="true" data-sortable="true" data-field="notes" data-visible="false">{{ trans('admin/maintenances/form.notes') }}</th>
                </tr>
                </thead>
            </table>
      </div>
    </div>
  </div>
</div>
@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')
@stop
