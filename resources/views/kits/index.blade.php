@extends('layouts/default', [
    'helpTitle' => trans('admin/kits/general.about_kits_title'),
    'helpText' => trans('admin/kits/general.about_kits_text')])

{{-- Web site Title --}}
@section('title')
  {{ trans('general.kits') }}
@parent
@stop

@section('header_right')
<a href="{{ route('kits.create') }}" class="btn btn-primary text-right">{{ trans('general.create') }}</a>
@stop


{{-- Content --}}
@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="box box-default">
      <div class="box-body">
            <table
                data-cookie-id-table="kitsTable"
                data-columns="{{ \App\Presenters\PredefinedKitPresenter::dataTableLayout() }}"
                data-side-pagination="server"
                data-sort-order="asc"
                data-sort-name="name"
                id="kitsTable"
                class="table table-striped snipe-table"
                data-url="{{ route('api.kits.index') }}"
                data-export-options='{
        "fileName": "export-kits-{{ date('Y-m-d') }}",
            "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
            }'>
          </table>
      </div> <!--.box-body-->
    </div> <!-- /.box.box-default-->
  </div> <!-- .col-md-12-->
</div>
@stop
@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'kits-export', 'search' => true])
@stop
