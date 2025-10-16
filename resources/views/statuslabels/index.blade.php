@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/statuslabels/table.title') }}
@parent
@stop

{{-- Page content --}}
@section('content')

<div class="row">
  <div class="col-md-9">
    <div class="box box-default">
      <div class="box-body">
            <table
                    data-columns="{{ \App\Presenters\StatusLabelPresenter::dataTableLayout() }}"
                    data-cookie-id-table="statuslabelsTable"
                    data-id-table="statuslabelsTable"
                    data-show-footer="false"
                    data-side-pagination="server"
                    data-sort-order="asc"
                    data-sort-name="name"
                    id="statuslabelsTable"
                    data-buttons="statuslabelButtons"
                    data-advanced-search="false"
                    class="table table-striped snipe-table"
                    data-url="{{ route('api.statuslabels.index') }}"
                    data-export-options='{
                "fileName": "export-statuslabels-{{ date('Y-m-d') }}",
                "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
          </table>
      </div>
    </div>
  </div>
  <!-- side address column -->
  <div class="col-md-3">
    <h2>{{ trans('admin/statuslabels/table.about') }}</h2>

      <div class="box">
          <div class="box-body">
              <p>{!!  trans('admin/statuslabels/table.info') !!}</p>
          </div>
      </div>


      <div class="box box-success">
          <div class="box-body">
          <p><i class="fas fa-circle text-green"></i> <strong>{{ trans('admin/statuslabels/table.deployable') }}</strong>: {!!  trans('admin/statuslabels/message.help.deployable')  !!}</p>
          </div>
      </div>

      <div class="box box-warning">
          <div class="box-body">
              <p><i class="fas fa-circle text-orange"></i> <strong>{{ trans('admin/statuslabels/table.pending') }}</strong>: {{ trans('admin/statuslabels/message.help.pending') }}</p>
          </div>
      </div>
      <div class="box box-danger">
          <div class="box-body">
            <p><i class="fas fa-times text-red"></i> <strong>{{ trans('admin/statuslabels/table.undeployable') }}</strong>: {{ trans('admin/statuslabels/message.help.undeployable') }}</p>
          </div>
      </div>

      <div class="box box-danger">
          <div class="box-body">
              <p><i class="fas fa-times text-red"></i> <strong>{{ trans('admin/statuslabels/table.archived') }}</strong>: {{ trans('admin/statuslabels/message.help.archived') }}</p>
          </div>
      </div>

  </div>

</div>
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table')

  <script nonce="{{ csrf_token() }}">
      function colorSqFormatter(value, row) {
          if (value) {
              return '<span class="label" style="background-color: ' + value + ';">&nbsp;</span> ' + value;
          }
      }

      function statuslabelsAssetLinkFormatter(value, row) {
          if ((row) && (row.name)) {
              return '<a href="{{ config('app.url') }}/hardware/?status_id=' + row.id + '"> ' + row.name + '</a>';
          }
      }

      function statusLabelTypeFormatter (row, value) {

          switch (value.type) {
              case 'deployable':
                  text_color = 'green';
                  icon_style = 'fa-circle';
                  trans  = '{{ strtolower(trans('admin/hardware/general.deployable')) }}';

                  break;
              case 'pending':
                  text_color = 'orange';
                  icon_style = 'fa-circle';
                  trans  = '{{ strtolower(trans('general.pending')) }}';

                  break;
              case 'undeployable':
                  text_color = 'red';
                  icon_style = 'fa-circle';
                  trans  ='{{ trans('admin/statuslabels/table.undeployable') }}';

                  break;
              default:
                  text_color = 'red';
                  icon_style = 'fa-times';
                  trans  = '{{ strtolower(trans('general.archived')) }}';

          }

          var typename_lower = trans;
          var typename = typename_lower.charAt(0).toUpperCase() + typename_lower.slice(1);
          return '<nobr><i class="fa ' + icon_style + ' text-' + text_color + '"></i> ' + typename + '</nobr>';


      }
  </script>
@stop
