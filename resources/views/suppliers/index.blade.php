@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/suppliers/table.suppliers') }}
@parent
@stop

{{-- Page content --}}
@section('content')


<div class="row">
  <div class="col-md-12">
      <div class="box box-default">
      <div class="box-body">

        <div class="row">
          <div class="col-md-12">

              <x-tables.bulk-actions
                      id_divname='suppliersBulkEditToolbar'
                      action_route="{{route('suppliers.bulk.delete')}}"
                      id_formname="suppliersBulkForm"
                      id_button="bulkSupplierEditButton"
                      model_name="supplier"
              >
                  @can('delete', App\Models\Supplier::class)
                      <option>Delete</option>
                  @endcan
              </x-tables.bulk-actions>
            <table
            data-columns="{{ \App\Presenters\SupplierPresenter::dataTableLayout() }}"
            data-cookie-id-table="suppliersTable"
            data-id-table="suppliersTable"
            data-side-pagination="server"
            data-sort-order="asc"
            id="suppliersTable"
            {{-- begin stuff for bulk dropdown --}}
            data-toolbar="#suppliersBulkEditToolbar"
            data-bulk-button-id="#bulkSupplierEditButton"
            data-bulk-form-id="#suppliersBulkForm"
            {{-- end stuff for bulk dropdown --}}
            data-advanced-search="false"
            data-buttons="supplierButtons"
            class="table table-striped snipe-table"
            data-url="{{ route('api.suppliers.index') }}"
            data-export-options='{
            "fileName": "export-suppliers-{{ date('Y-m-d') }}",
            "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
            }'>
      </table>
          </div>
        </div>
      </div>
  </div>
  </div>
</div>
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'suppliers-export', 'search' => true])
@stop
