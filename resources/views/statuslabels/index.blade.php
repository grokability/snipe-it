@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/statuslabels/table.title') }}
@parent
@stop

{{-- Page content --}}
@section('content')
    <x-container columns="2">

        <x-page-column class="col-md-9">
            <x-box>

                <x-slot:bulkactions>
                    <x-table.bulk-actions
                            name='statuslabel'
                            action_route="{{ route('statuslabels.bulk.delete') }}"
                            model_name="statuslabel"
                    >
                        @can('delete', App\Models\Statuslabel::class)
                            <option>{{ trans('general.delete') }}</option>
                        @endcan
                    </x-table.bulk-actions>
                </x-slot:bulkactions>

                <x-table
                    name="statuslabel"
                    buttons="statuslabelButtons"
                    fixed_right_number="1"
                    fixed_number="1"
                    api_url="{{ route('api.statuslabels.index') }}"
                    :presenter="\App\Presenters\StatusLabelPresenter::dataTableLayout()"
                    export_filename="export-statuslabels-{{ date('Y-m-d') }}"
                />

            </x-box>
        </x-page-column>
        <x-page-column class="col-md-3">

            <x-box>
                <p>{!!  trans('admin/statuslabels/table.info') !!}</p>
            </x-box>

            <x-box box_style="success">
                <p><i class="fas fa-circle text-green"></i> <strong>{{ trans('admin/statuslabels/table.deployable') }}</strong>: {!!  trans('admin/statuslabels/message.help.deployable')  !!}</p>
            </x-box>

            <x-box box_style="warning">
                <p><i class="fas fa-circle text-orange"></i> <strong>{{ trans('admin/statuslabels/table.pending') }}</strong>: {{ trans('admin/statuslabels/message.help.pending') }}</p>
            </x-box>

            <x-box box_style="danger">
                <p><i class="fas fa-times text-red"></i> <strong>{{ trans('admin/statuslabels/table.undeployable') }}</strong>: {{ trans('admin/statuslabels/message.help.undeployable') }}</p>
            </x-box>

            <x-box box_style="danger">
                <p><i class="fas fa-times text-red"></i> <strong>{{ trans('admin/statuslabels/table.archived') }}</strong>: {{ trans('admin/statuslabels/message.help.archived') }}</p>
            </x-box>

        </x-page-column>
    </x-container>

@stop

@section('moar_scripts')
@include ('partials.bootstrap-table')

  <script nonce="{{ csrf_token() }}">


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
