@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.depreciation_report') }} 
@parent
@stop

{{-- Page content --}}
@section('content')

<div class="row">
  <div class="col-md-12">
    <div class="box box-default">
      <div class="box-body">


          @if (($depreciations) && ($depreciations->count() > 0))
                  <table
                        data-cookie-id-table="depreciationReport"
                        data-id-table="depreciationReport"
                        data-side-pagination="server"
                        data-sort-order="desc"
                        data-sort-name="created_at"
                        data-show-footer="true"
                        id="depreciationReport"
                        data-advanced-search="false"
                        data-url="{{ route('api.depreciation-report.index') }}"
                        data-mobile-responsive="true"
                        {{-- data-toggle="table" --}}
                        class="table table-striped snipe-table"
                        data-columns="{{ \App\Presenters\DepreciationReportPresenter::dataTableLayout() }}"
                        data-export-options='{
                          "fileName": "depreciation-report-{{ date('Y-m-d') }}",
                          "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                          }'>
          </table>
              @else
              <div class="col-md-12">
                  <div class="alert alert-warning fade in">
                      <i class="fas fa-exclamation-triangle faa-pulse animated"></i>
                      {!! trans('admin/depreciations/general.no_depreciations_warning') !!}
                  </div>
              </div>
          @endif
      </div> <!-- /.box-body-->
    </div> <!--/box.box-default-->
  </div> <!-- /.col-md-12-->
</div> <!--/.row-->

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')
@stop
