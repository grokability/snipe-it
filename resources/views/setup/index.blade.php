@extends('layouts/setup')
{{-- TODO: Translate --}}
{{-- Page title --}}
@section('title')
Create a User ::
@parent
@stop

{{-- Page content --}}
@section('content')

<h4> First let's do a quick system check to make sure your configuration looks correct.  </h4>

<table class="table">
  <thead>
    <tr>
      <th class="col-lg-2">Setting</th>
      <th class="col-lg-1">Valid</th>
      <th class="col-lg-9">Notes</th>
    </tr>
  </thead>
  <tbody>
  <tr {!! ($start_settings['php_version_min']) ? ' class="success"' : ' class="danger"' !!}>
      <td>PHP</td>
      <td>
          @if ($start_settings['php_version_min'])
              <i class="fas fa-check preflight-success"></i>
          @else
              <i class="fas fa-times preflight-error"></i>
          @endif
      </td>
      <td>
          @if ($start_settings['php_version_min'])
              Yay!
          @else
              Oh no!
          @endif

              You're running PHP version {{ PHP_VERSION }}. ({{ config('app.min_php') }} or greater is required.)
      </td>
  </tr>

    <tr {!! ($start_settings['url_valid']) ? ' class="success"' : ' class="danger"' !!}>
      <td>URL</td>
      <td>
        @if ($start_settings['url_valid'])
          <i class="fas fa-check preflight-success"></i>
        @else
          <i class="fas fa-times preflight-error"></i>
        @endif
      </td>
      <td>
        @if ($start_settings['url_valid'])
          That URL looks right! Good job!
        @else
          Uh oh! Snipe-IT thinks your URL is {{ $start_settings['url_config'] }}, but your real URL is {{ $start_settings['real_url'] }}
          Please update your <code>APP_URL</code> settings in your  <code>.env</code> file
        @endif
      </td>
    </tr>

    <tr {!! ($start_settings['db_conn']===true) ? ' class="success"' : ' class="danger"' !!}>
      <td>Database</td>
      <td>
        @if ($start_settings['db_conn']===true)
          <i class="fas fa-check preflight-success"></i>
        @else
          <i class="fas fa-times preflight-error"></i>
        @endif
      </td>
      <td>
        @if ($start_settings['db_conn']===true)
          Great work! Connected to <code>{{ $start_settings['db_name'] }}</code>
        @else
          D'oh! Looks like we can't connect to your database. Please update your database settings in your  <code>.env</code> file. Your database says: <code>{{ $start_settings['db_error'] }}</code>
        @endif
      </td>
    </tr>

    <tr {!! (!$start_settings['env_exposed']) ? ' class="success"' : ' class="danger"' !!}>
      <td>Config File</td>
      <td>
        @if (!$start_settings['env_exposed'])
          <i class="fas fa-check preflight-success"></i>
        @else
          <i class="fas fa-times preflight-error"></i>
        @endif
      </td>
      <td>
        @if (!$start_settings['env_exposed'])
          Sweet. It doesn't look like your <code>.env</code> file is exposed to the outside world. (You should double check this in a browser though. You don't ever want anyone able to see that file. Ever. Ever ever.) <a href="../../.env">Click here to check now</a> (This should return a file not found or forbidden error.)
        @else
          We cannot determine if your config file is exposed to the outside world, so you will have to manually verify this. You don't ever want anyone able to see that file. Ever. Ever ever. An exposed <code>.env</code> file can disclose sensitive data about your system and database.
        @endif
      </td>
    </tr>

    <tr {!! ($start_settings['prod']) ? ' class="success"' : ' class="warning"' !!}>
      <td>Environment</td>
      <td>
        @if ($start_settings['prod'])
          <i class="fas fa-check preflight-success"></i>
        @else
          <i class="fas fa-times preflight-error"></i>
        @endif
      </td>
      <td>
        @if ($start_settings['prod'])
          Your app is set to production mode. Rock on!
        @else
          Your app is set <code>{{ $start_settings['env'] }}</code> instead of <code>production</code> mode. If you're not planning on developing on Snipe-IT, please update your <code>APP_ENV</code> settings in your  <code>.env</code> file to <code>production</code>.
        @endif
      </td>
    </tr>

    <tr {!! (!$start_settings['owner_is_admin']) ? ' class="success"' : ' class="danger"' !!}>
      <td>File Owner</td>
      <td>
        @if (!$start_settings['owner_is_admin'])
          <i class="fas fa-check preflight-success"></i>
        @else
          <i class="fas fa-times preflight-error"></i>
        @endif
      </td>
      <td>
        @if (!$start_settings['owner_is_admin'])
          Your app files are owned by <code>{{ $start_settings['owner'] }}</code>. That doesn't look like a default root/admin account. Nice!
        @else
          It looks like your files are owned by <code>{{ $start_settings['owner'] }}</code>, which might be a root/admin account. It's never a good idea to run a website with escalated priveliges.
        @endif
      </td>
    </tr>

    <tr {!! (!$start_settings['writable']) ? ' class="danger"' : ' class="success"' !!}>
      <td>Permissions</td>
      <td>
        @if ($start_settings['writable'])
          <i class="fas fa-check preflight-success"></i>
        @else
          <i class="fas fa-times preflight-error"></i>
        @endif
      </td>
      <td>
        @if ($start_settings['writable'])
          Yippee! Your app storage directory seems writable.
        @else
          Uh-oh. Your <code>{{ storage_path() }}</code> directory (or sub-directories within) are not writable by the web-server. Those directories need to be writable by the web server in order for the app to work.
        @endif
      </td>
    </tr>

    <tr {!! ($start_settings['debug_exposed']) ? ' class="danger"' : ' class="success"' !!}>
      <td>Debug</td>
      <td>
        @if (!$start_settings['debug_exposed'])
          <i class="fas fa-check preflight-success"></i>
        @else
          <i class="fas fa-times preflight-error"></i>
        @endif
      </td>
      <td>
        @if (!$start_settings['debug_exposed'])
          Awesomesauce. Debug is either turned off, or you're running this in a non-production environment. (Don't forget to turn it off when you're ready to go live.)
        @else
          <p>Yikes! You should turn off debug mode unless you encounter any issues. Please update your <code>APP_DEBUG</code> settings in your  <code>.env</code> file</p>
        @endif
      </td>
    </tr>

    <tr {!! ($start_settings['gd']) ? ' class="success"' : ' class="warning"' !!}>
      <td>Image Library</td>
      <td>
        @if ($start_settings['gd'])
          <i class="fas fa-check preflight-success"></i>
        @else
          <i class="fas fa-times preflight-warning"></i>
        @endif
      </td>
      <td>
        @if ($start_settings['gd'])
          <p>GD is installed. Go you!</p>
        @else
          <p>The GD library isn't installed. While this won't prevent the system from working, you won't be able to generate labels or upload images.</p>
        @endif
      </td>
    </tr>

    <tr id="mailtestrow" class="info">
      <td>Email</td>
      <td>
          <span id="mailtesticon"></span>
      </td>
        <td>
            <p>This will attempt to send a test mail to {{ config('mail.from.address') }}.</p>
            <a class="btn btn-default btn-sm pull-left" id="mailtest" style="margin-right: 10px;">Send Test</a>
            <div id="mailteststatus-text" class="text-danger"></div>
      </td>
    </tr>
  </tbody>
</table>

@stop

@section('button')
  <form action="{{ route('setup.migrate') }}" method="POST">
      @csrf
    <button class="btn btn-primary">
        {{ trans('general.setup_next') }}: {{ trans('general.setup_create_database') }}
        <i class="fa-solid fa-angles-right"></i>
    </button>
  </form>

@parent
@stop

@section('moar_scripts')
<script type="text/javascript">
    $(document).ready(function () {

        // Test Mail

        $("#mailtest").click(function(){

            $("#mailtestrow").removeClass('success').removeClass('danger').removeClass('warning').addClass('info');
            $("#mailtesticon").html('');
            $("#mailteststatus").html('Sending Test Email...');
            $('#mailteststatus-error').html('');
            $("#mailtesticon").html('<i class="fas fa-spinner fa-spin text-info"></i>');

            $.ajax({
                url: "{{ route('setup.mailtest') }}",

                success: function (result) {
                    if (result.status == 'success') {
                        $("#mailtestrow").removeClass('info').removeClass('danger').removeClass('warning');
                        $("#mailtestrow").addClass('success');
                        $("#mailtesticon").html('<i class="fas fa-check preflight-success"></i>');
                        $("#mailteststatus").html('');
                        $('#mailteststatus-error').html('');
                        $("#mailteststatus-text").removeClass('text-danger');
                        $("#mailteststatus-text").addClass('text-success');
                        $("#mailteststatus-text").html('Mail sent to {{ config('mail.from.address') }}!');
                    } else {
                        $("#mailtestrow").removeClass('success').removeClass('info').removeClass('warning');
                        $("#mailtestrow").addClass('danger');
                        $("#mailtesticon").html('<i class="fas fa-check preflight-error"></i>');
                        $("#mailtestresult").html('Something went wrong. Your email was not sent. Check your mail settings in your <code>.env</code> file.');

                    }


                },
                error: function (result) {
                    $("#mailtestrow").removeClass('success').removeClass('info').removeClass('warning');
                    $("#mailtestrow").addClass('danger');
                    $("#mailtesticon").html('');
                    $("#mailteststatus-text").html('');
                    $('#mailteststatus-error').html('');
                    $("#mailteststatus-text").removeClass('text-success');
                    $("#mailteststatus-text").addClass('text-danger');
                    $("#mailtesticon").html('<i class="fas fa-exclamation-triangle text-danger"></i>');
                    $('#mailteststatus-text').html('Mail could not be sent.');
                    if (result.responseJSON) {
                        $('#mailteststatus-error').html('Error: ' + result.responseJSON.messages);
                    } else {
                        console.dir(data);
                    }
                }

            });


        });
 });
</script>
@stop
