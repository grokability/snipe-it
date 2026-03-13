@extends('layouts/default')
{{-- Page title --}}
@section('title')
{{ trans('general.ldap_user_sync') }}
@parent
@stop

{{-- Page content --}}
@section('content')

<div class="row">
    <div class="col-md-8 col-md-offset-2">
        @if ($snipeSettings->ldap_enabled == 0)
          {{ trans('admin/users/message.ldap_not_configured') }}
        @else

          <div class="box box-default">
              <form class="form-horizontal" role="form" method="post" action="" id="ldap-form">
                  {{csrf_field()}}
                    <div class="box-body">
                        <div class="callout callout-legend col-md-12">
                            <p>
                                <i class="fa-solid fa-lightbulb text-info"></i>
                                <strong>
                                    {!!  trans('admin/users/general.ldap_sync_intro', ['link' => 'https://snipe-it.readme.io/docs/ldap-sync#/']) !!}
                                </strong>
                            </p>
                        </div>
                        <!-- Location -->
                            @include ('partials.forms.edit.location-select', ['translated_name' => trans('general.ldap_sync_location'), 'help_text' => trans('admin/users/general.ldap_config_text'), 'fieldname' => 'location_id[]', 'multiple' => true])

                        </div><!-- ./box-body -->

                        <div class="box-footer">
                            <div class="text-left col-md-6">
                                <a class="btn btn-link" href="{{ route('users.index') }}">{{ trans('button.cancel') }}</a>
                            </div>
                            <div class="text-right col-md-6">
                                <button type="submit" class="btn btn-primary" id="sync">
                                    <i id="sync-button-icon" class="fas fa-sync-alt icon-white" aria-hidden="true"></i> <span id="sync-button-text">{{ trans('general.synchronize') }}</span>
                                </button>
                            </div>
                        </div> <!-- ./box-footer -->
              </form>
        </div><!-- /.box -->

        @endif
    </div><!-- /.col-md-8 -->
</div><!-- /.row -->

@if (Session::get('summary'))
<div class="row">
    <div class="col-md-8 col-md-offset-2">

        <div class="box box-default">
            <div class="box-header with-border">
                <h2 class="box-title">
                    {{ trans('general.sync_results') }}
                </h2>
            </div><!-- /.box-header -->

            <div class="box-body"><!-- .box-body -->
                <table
                    data-cookie-id-table="ldapUserSync"
                    data-id-table="ldapUserSyncTable"
                    data-side-pagination="client"
                    data-sort-order="asc"
                    data-sort-name="username"
                    data-show-refresh="false"
                    id="customFieldsTable"
                    data-advanced-search="false"
                    class="table table-striped snipe-table"
                    data-export-options='{
                    "fileName": "ldap-sync-results-{{ date('Y-m-d') }}"
                }'>
                    <thead>
                        <tr>
                            <th data-sortable="true" data-visible="false" data-searchable="true">{{ trans('general.id') }}</th>
                            <th data-sortable="true" data-visible="true" data-searchable="true">{{ trans('general.username') }}</th>
                            <th data-sortable="true" data-visible="true" data-searchable="true">{{ trans('admin/users/table.display_name') }}</th>
                            <th data-sortable="true" data-visible="true" data-searchable="true">{{ trans('general.employee_number') }}</th>
                            <th data-sortable="true" data-visible="true" data-searchable="true">{{ trans('general.first_name') }}</th>
                            <th data-sortable="true" data-visible="true" data-searchable="true">{{ trans('general.last_name') }}</th>
                            <th data-sortable="true" data-visible="true" data-searchable="true">{{ trans('general.email') }}</th>
                            <th data-sortable="true" data-visible="true" data-searchable="true">{{ trans('general.notes') }}</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach (Session::get('summary') as $entry)
                            <tr>
                                <td>{{ (array_key_exists('id', $entry)) ?  $entry['id'] : '' }}</td>
                                <td>{{ $entry['username'] }}</td>
                                <td>{{ $entry['display_name'] }}</td>
                                <td>{{ $entry['employee_number'] }}</td>
                                <td>{{ $entry['firstname'] }}</td>
                                <td>{{ $entry['lastname'] }}</td>
                                <td>{{ $entry['email'] }}</td>
                                <td>
                                @if ($entry['status']=='success')
                                <span class="text-success"><i class="fas fa-check"></i> {!! $entry['note'] !!}</span>
                                @else
                                <span class="alert-msg" aria-hidden="true">{!! $entry['note'] !!}</span>
                                @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div><!-- /.col-md-12 -->
</div><!-- /.row -->

@endif

@stop

@section('moar_scripts')

 @include ('partials.bootstrap-table')

    <script type="text/javascript">
    $(document).ready(function () {
        $("#sync").click(function () {
            $("#sync").removeClass("btn-warning");
            $("#sync").addClass("btn-success");
            $("#sync-button-icon").addClass("fa-spin");
            $("#sync-button-text").html("{{ trans('general.processing') }}");
        });
    });
</script>

@stop
