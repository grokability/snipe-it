@extends('layouts/default')
{{-- Page title --}}
@section('title')
{{ trans('general.ldap_user_sync') }}
@parent
@stop


@section('header_right')
<a href="{{ URL::previous() }}" class="btn btn-primary pull-right">
  {{ trans('general.back') }}</a>
@stop


{{-- Page content --}}
@section('content')

<div class="row">
  <div class="col-md-12">
    @if ($snipeSettings->ldap_enabled == 0)
      {{ trans('admin/users/message.ldap_not_configured') }}
    @else
    <form class="form-horizontal" role="form" method="post" action="" id="ldap-form">
      {{csrf_field()}}
      <div class="box box-default">
        <div class="box-body">

                <div class="callout callout-legend col-md-12">

                    <p>
                        <i class="fa-solid fa-lightbulb"></i>
                        <strong>{!!  trans('admin/users/general.ldap_sync_intro', ['link' => 'https://snipe-it.readme.io/docs/ldap-sync#/']) !!}</strong>
                    </p>
                </div>
         
          <!-- location_id-->
          <div class="form-group {{ $errors->has('location_id') ? 'has-error' : '' }}">
               <!-- Location -->
              @include ('partials.forms.edit.location-select', ['translated_name' => trans('general.ldap_sync_location'), 'help_text' => trans('admin/users/general.ldap_config_text'), 'fieldname' => 'location_id[]', 'multiple' => true])
          </div>

            <div class="box-footer">
                <div class="text-left col-md-6">
                    <a class="btn btn-link" href="{{ route('users.index') }}">{{ trans('button.cancel') }}</a>
                </div>
                <div class="text-right col-md-6">
                    <button type="submit" class="btn btn-primary" id="sync">
                        <i id="sync-button-icon" class="fas fa-sync-alt icon-white" aria-hidden="true"></i> <span id="sync-button-text">{{ trans('general.synchronize') }}</span>
                    </button>
                </div>

            </div>

        </div>
      </div>
    </form>
  </div>
  <div class="col-md-3">

  </div>
</div>

@if (Session::get('summary'))
<div class="row">
  <div class="col-md-12">

    <div class="box box-default">
      <div class="box-header with-border">
        <h2 class="box-title">{{ trans('general.sync_results') }}</h2>
      </div><!-- /.box-header -->
      <div class="box-body">
        <table class="table table-bordered">
          <tr>
              <th>{{ trans('general.username') }}</th><th>{{ trans('general.employee_number') }}</th>
              <th>{{ trans('general.first_name') }}</th><th>{{ trans('general.last_name') }}</th>
              <th>{{ trans('general.email') }}</th><th>{{ trans('general.notes') }}</th>
          </tr>

          @foreach (Session::get('summary') as $entry)
          <tr {!! ($entry['status']=='success') ? 'class="success"' : 'class="danger"' !!}>
              <td>{{ $entry['username'] }}</td>
              <td>{{ $entry['employee_number'] }}</td>
              <td>{{ $entry['firstname'] }}</td>
              <td>{{ $entry['lastname'] }}</td>
              <td>{{ $entry['email'] }}</td>
              <td>
                @if ($entry['status']=='success')
                  <i class="fas fa-check"></i> {!! $entry['note'] !!}
                @else
                  <span class="alert-msg" aria-hidden="true">{!! $entry['note'] !!}</span>
                @endif

                </td>
              </tr>
          @endforeach
        </table>
      </div>
    </div>
    @endif

  </div>
</div>
@endif

@stop

@section('moar_scripts')

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
