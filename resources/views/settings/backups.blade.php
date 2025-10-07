@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/settings/general.backups') }}
@parent
@stop

@section('header_right')
    <a href="{{ route('settings.index') }}" class="btn btn-default pull-right" style="margin-left: 5px;">
      {{ trans('general.back') }}
    </a>

    <form method="POST" style="display: inline">
            @csrf
            <button class="btn btn-primary {{ (config('app.lock_passwords')) ? ' disabled': '' }}">{{ trans('admin/settings/general.generate_backup') }}</button>
      </form>

@stop

{{-- Page content --}}
@section('content')

    <div class="modal modal-warning fade" tabindex="-1" role="dialog" id="backupRestoreModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" role="form">
                    <div class="modal-header">
                        <h4 class="modal-title">Modal title</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>{{ trans('admin/settings/message.backup.restore_warning') }}</p>
                        <div class="form-group">
                            <label class="form-control">
                                <input type="checkbox" name="clean" {{ config('backup.sanitize_by_default') ? "checked='checked'" : "" }}>{{ trans('admin/settings/general.backups_clean') }}
                            </label>
                            <p class="help-block modal-help">{{ trans('admin/settings/general.backups_clean_helptext') }}</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        {{ csrf_field() }}
                        {{ method_field('POST') }}
                        <button type="button" class="btn btn-default pull-left"
                                data-dismiss="modal">{{ trans('general.cancel') }}</button>
                        <button type="submit" class="btn btn-outline">{{ trans('general.yes') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row">

  <div class="col-md-8">
    
    <div class="box box-default">
      <div class="box-body">
       
        
          
        <div class="table-responsive">
          
            <table
                    data-cookie="true"
                    data-cookie-id-table="system-backups"
                    data-id-table="system-backups"
                    data-side-pagination="client"
                    data-sort-order="desc"
                    data-sort-name="modified_display"
                    id="system-backups"
                    class="table table-striped snipe-table">
            <thead>
              <tr>
              <th data-sortable="true">{{ trans('general.file_name') }}</th>
              <th data-sortable="true" data-field="modified_display" data-sort-name="modified_value">{{ trans('admin/settings/table.created') }}</th>
              <th data-field="modified_value" data-visible="false"></th>
              <th data-sortable="true">{{ trans('admin/settings/table.size') }}</th>
              <th>{{ trans('table.actions') }}</th>
              </tr>
            </thead>
            <tbody>
            @foreach ($files as $file)
            <tr>
              <td>
                  <a href="{{ route('settings.backups.download', [$file['filename']]) }}">
                      {{ $file['filename'] }}
                  </a>
              </td>
              <td>{{ $file['modified_display'] }} </td>
              <td>{{ $file['modified_value'] }} </td>
              <td>{{ $file['filesize'] }}</td>
              <td>

                  @can('superadmin')
                      @if (config('app.allow_backup_delete')=='true')
                      <a data-html="false"
                         class="btn delete-asset btn-danger btn-sm {{ (config('app.lock_passwords')) ? ' disabled': '' }}" 
                         data-toggle="modal" href="{{ route('settings.backups.destroy', $file['filename']) }}" 
                         data-content="{{ trans('admin/settings/message.backup.delete_confirm') }}" 
                         data-title="{{ trans('general.delete') }}  {{ e($file['filename']) }}?"
                         onClick="return false;">
                          <i class="fas fa-trash icon-white" aria-hidden="true"></i>
                          <span class="sr-only">{{ trans('general.delete') }}</span>
                      </a>
                      @else
                          <a href="#"
                             class="btn delete-asset btn-danger btn-sm disabled">
                              <i class="fas fa-trash icon-white" aria-hidden="true"></i>
                              <span class="sr-only">{{ trans('general.delete') }}</span>
                          </a>
                      @endif

                          <a data-html="true"
                             href="{{ route('settings.backups.restore', $file['filename']) }}"
                             class="btn btn-warning btn-sm restore-backup {{ (config('app.lock_passwords')) ? ' disabled': '' }}"
                             data-target="#backupRestoreModal"
                             data-title="{{ trans('admin/settings/message.backup.restore_confirm', array('filename' => e($file['filename']))) }}"
                             onClick="return false;">
                      <i class="fas fa-retweet" aria-hidden="true"></i>
                      <span class="sr-only">{{ trans('general.restore') }}</span>
                    </a>
                     
                  @endcan
              </td>
            </tr>
            @endforeach
            </tbody>
          </table>
      </div> <!-- end table-responsive div -->
    </div> <!-- end box-body div -->
</div> <!-- end box div -->
</div> <!-- end col-md div -->

   <!-- side address column -->
  <div class="col-md-4">

    <div class="box box-default">
      <div class="box-header with-border">
        <h2 class="box-title">
            <x-icon type="backups"/>
          {{ trans('admin/settings/general.backups_upload') }}
        </h2>
        <div class="box-tools pull-right">
        </div>
      </div><!-- /.box-header -->

      <div class="box-body">

        <p>
          {!! trans('admin/settings/general.backups_path', ['path'=> 'storage/app/backups']) !!}
        </p>

        @if (config('app.lock_passwords')===true)
        <p class="alert alert-warning"><i class="fas fa-lock"></i> {{ trans('general.feature_disabled') }}</p>
          @else

      <form method="POST" action="{{ route('settings.backups.upload') }}" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data">
        @csrf

        
      <div class="form-group {{ $errors->has((isset($fieldname) ? $fieldname : 'file')) ? 'has-error' : '' }}" style="margin-bottom: 0px;">
        <div class="col-md-8 col-xs-8">
          
          
             <!-- displayed on screen -->
            <label class="btn btn-default col-md-12 col-xs-12" aria-hidden="true">
              <x-icon type="paperclip" />
                {{ trans('button.select_file')  }}
                <input type="file" name="file" class="js-uploadFile" id="uploadFile" data-maxsize="{{ Helper::file_upload_max_size() }}" accept="application/zip" style="display:none;" aria-label="file" aria-hidden="true">
            </label>
        </div>
        <div class="col-md-4 col-xs-4">
            <button class="btn btn-primary col-md-12 col-xs-12" id="uploadButton" disabled>
                {{ trans('button.upload') }}
                <span id="uploadIcon"></span>
            </button>
        </div>
        <div class="col-md-12">
          
          <p class="label label-default col-md-12" style="font-size: 120%!important; margin-top: 10px; margin-bottom: 10px;" id="uploadFile-info"></p>
          <p class="help-block" style="margin-top: 10px;" id="uploadFile-status">{{ trans_choice('general.filetypes_accepted_help', 1, ['size' => Helper::file_upload_max_size_readable(), 'types' => '.zip']) }}</p>     
          {!! $errors->first('file', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
            
        </div>  
            
    </div>
    
    </form>
    @endif  
      </div>
    </div>

    <div class="box box-warning">
      <div class="box-header with-border">
        <h2 class="box-title">
            <x-icon type="warning" class="text-orange"/> {{ trans('admin/settings/general.backups_restoring') }}
        </h2>
        <div class="box-tools pull-right">
        </div>
      </div><!-- /.box-header -->
      <div class="box-body">
        
      <p>
        {!! trans('admin/settings/general.backups_restore_warning', ['app_name' => config('app.name') ]) !!}
      </p>
        
      <p class="text-danger" style="font-weight: bold; font-size: 120%;">
        {{ trans('admin/settings/general.backups_logged_out') }}
      </p>

      <p>
        {{ trans('admin/settings/general.backups_large') }} 
      </p>
      
    </div>
  </div>
    
        </div> <!-- end col-md-12 form div -->
   </div> <!-- end form group div -->



@stop

@section('moar_scripts')

    @include ('partials.bootstrap-table')

    
    <script>
      /*
      * This just disables the upload button via JS unless they have actually selected a file.
      *
      * Todo: - key off the javascript response for JS file upload info as well, so that if it fails that 
      * check (file size and type) we should also leave it disabled.
      */

      $(document).ready(function() {
        
        $("#uploadFile").on('change',function(event){

            if ($('#uploadFile').val().length == 0) {
                $("#uploadButton").attr("disabled", true);
                $("#uploadIcon").html('');
            } else {
              $('#uploadButton').removeAttr('disabled');

                $("#uploadButton").click(function(){
                    $("#uploadIcon").html('<i class="fas fa-spinner spin"></i>');
                });
            }

        });
      });

      // due to dynamic loading, we have to use the below 'weird' way of adding event handlers instead of just saying
      // $('.restore-backup').on( .....
      $('table').on('click', '.restore-backup', function (event) {
          event.preventDefault();
          var modal = $('#backupRestoreModal');
          modal.find('.modal-title').text($(this).data('title'));
          modal.find('form').attr('action', $(this).attr('href'));
          modal.modal({
              show: true
          });
          return false;
      })
  </script>
@stop

