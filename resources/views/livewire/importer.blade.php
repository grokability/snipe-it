@section('title')
    {{ trans('general.import') }}
    @parent
@stop
<div>
    {{-- Livewire requires a 'master' <div>, above --}}
        <div class="row">

{{-- alert --}}
@if($message != '')
    <div class="col-md-12" class="{{ $message_type }}">
        <div class="alert alert-{{ $this->message_type }}">
            <button type="button" class="close" wire:click="$set('message','')">&times;</button>
            @if($message_type == 'success')
                <i class="fas fa-check faa-pulse animated" aria-hidden="true"></i>
            @endif
            <strong>{{-- title --}} </strong>
            {{ $message }}
        </div>
    </div>
@endif

        @if($import_errors)
          <div class="col-md-12">
            <div class="box">
                <div class="box-body">
                    <div class="alert alert-warning">

                        <i class="fa fa-warning info" aria-hidden="true"></i> <strong>{{ trans('general.warning', ['warning'=> trans('general.errors_importing')]) }}</strong>
                    </div>

                    <div class="errors-table">
                        <table class="table table-striped table-bordered" id="errors-table">
                            <thead>
                            <th>{{ trans('general.item') }}</th>
                            <th>{{ trans('admin/custom_fields/general.field') }}</th>
                            <th>{{ trans('general.error') }}</th>
                            </thead>
                            <tbody>
                            @foreach($import_errors AS $key => $actual_import_errors)
                                @foreach($actual_import_errors AS $table => $error_bag)
                                    @foreach($error_bag as $field => $error_list)
                                        <tr>
                                            <td><b>{{ $key }}</b></td>
                                            <td><b>{{ $field }}</b></td>
                                            <td>
                                                <span>{{ implode(", ",$error_list) }}</span>
                                                <br />
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
       </div>
@endif

            <div class="col-md-9">
                <div class="box">
                    <div class="box-body">
                        <div class="row">

                            <div class="col-md-12">

                                @if($progress != -1)
                                    <div class="col-md-10 col-sm-5 col-xs-12" style="height: 35px;" id='progress-container'>
                                        <div class="progress progress-striped-active" style="height: 100%;">
                                            <div id='progress-bar' class="progress-bar {{ $progress_bar_class }}" role="progressbar" style="width: {{ $progress }}%">
                                                <h4 id="progress-text">{!! $progress_message  !!}</h4>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="col-md-4 col-sm-5 col-xs-12 text-right pull-right">

                                    <!-- The fileinput-button span is used to style the file input field as button -->
                                    @if (!config('app.lock_passwords'))
                                        <span class="btn btn-primary fileinput-button">
                                        <span>{{ trans('button.select_file') }}</span>
                                         <!-- The file input field used as target for the file upload widget -->
                                        <label for="files[]"><span class="sr-only">{{ trans('admin/importer/general.select_file') }}</span></label>
                                        <input id="fileupload" type="file" name="files[]" data-url="{{ route('api.imports.index') }}" accept="text/csv" aria-label="files[]">
                                        </span>
                                    @endif

                                </div>

                            </div>



                        </div>
                        <div class="row">
                            <div class="col-md-12 table-responsive" style="padding-top: 30px;">
                                <table
                                        data-id-table="upload-table"

                                        data-side-pagination="client"
                                        id="upload-table"
                                        class="col-md-12 table table-striped snipe-table">

                                    <tr>
                                        <th>
                                            {{ trans('general.file_name') }}
                                        </th>
                                        <th>
                                            {{ trans('general.created_at') }}
                                        </th>
                                        <th>
                                            {{ trans('general.created_by') }}
                                        </th>

                                        <th>
                                            {{ trans('general.filesize') }}
                                        </th>
                                        <th class="col-md-1 text-right">
                                            <span class="sr-only">{{ trans('general.actions') }}</span>
                                        </th>
                                    </tr>

                                    @foreach($this->files as $currentFile)

                                    		<tr style="{{ ($this->activeFile && ($currentFile->id == $this->activeFile->id)) ? 'font-weight: bold' : '' }}" class="{{ ($this->activeFile && ($currentFile->id == $this->activeFile->id)) ? 'warning' : '' }}">
                                    			<td>{{ $currentFile->file_path }}</td>
                                    			<td>{{ Helper::getFormattedDateObject($currentFile->created_at, 'datetime', false) }}</td>
                                                <td>{{ ($currentFile->adminuser) ? $currentFile->adminuser->present()->fullName : '--'}}</td>
                                    			<td>{{ Helper::formatFilesizeUnits($currentFile->filesize) }}</td>
                                                <td class="col-md-1 text-right" style="white-space: nowrap;">
                                                    <button class="btn btn-sm btn-info" wire:click="selectFile({{ $currentFile->id }})" data-tooltip="true" title="{{ trans('general.import_this_file') }}">
                                                        <i class="fa-solid fa-list-check" aria-hidden="true"></i>
                                                        <span class="sr-only">{{ trans('general.import') }}</span>
                                                    </button>
                                                    <a href="#" wire:click.prevent="$set('activeFileId',null)">
                                                    <button class="btn btn-sm btn-danger" wire:click="destroy({{ $currentFile->id }})">
                                                        <i class="fas fa-trash icon-white" aria-hidden="true"></i>
                                                        <span class="sr-only">{{ trans('general.delete') }}</span>
                                                    </button>
                                                    </a>
                                    			</td>
                                    		</tr>

                                            @if( $currentFile && $this->activeFile && ($currentFile->id == $this->activeFile->id))
                                                <tr class="warning">
                                                    <td colspan="5">

                                                        <div class="form-group">

                                                                <label for="typeOfImport" class="col-md-3 col-xs-12">
                                                                    {{ trans('general.import_type') }}
                                                                </label>

                                                            <div class="col-md-9 col-xs-12">
                                                                <x-input.select
                                                                    name="typeOfImport"
                                                                    id="import_type"
                                                                    :options="$importTypes"
                                                                    :selected="$typeOfImport"
                                                                    :for-livewire="true"
                                                                    :include-empty="true"
                                                                    :data-placeholder="trans('general.select_var', ['thing' => trans('general.import_type')])"
                                                                    {{--placeholder needed so that the form-helper will put an empty option first--}}
                                                                    placeholder=""
                                                                    {{--Remove this if the list gets long enough that we need to search--}}
                                                                    data-minimum-results-for-search="-1"
                                                                    style="min-width: 350px;"
                                                                />
                                                                    @if ($typeOfImport === 'asset' && $snipeSettings->auto_increment_assets == 0)
                                                                        <p class="help-block">
                                                                            {{ trans('general.auto_incrementing_asset_tags_disabled_so_tags_required') }}
                                                                        </p>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="form-group col-md-9 col-md-offset-3">
                                                                <label class="form-control">
                                                                    <input type="checkbox" name="update" data-livewire-component="{{ $this->getId() }}" wire:model.live="update">
                                                                    {{ trans('general.update_existing_values') }}
                                                                </label>

                                                                @if ($typeOfImport === 'asset' && $snipeSettings->auto_increment_assets == 1 && $update)
                                                                    <p class="help-block">
                                                                        {{ trans('general.auto_incrementing_asset_tags_enabled_so_now_assets_will_be_created') }}
                                                                    </p>
                                                                @endif



                                                                @if ($typeOfImport === 'user')
                                                                <label class="form-control">
                                                                    <input type="checkbox" name="send_welcome" data-livewire-component="{{ $this->getId() }}" wire:model.live="send_welcome">
                                                                    {{ trans('general.send_welcome_email_to_users') }}
                                                                </label>
                                                                    <p class="help-block"> {{ trans('general.send_welcome_email_import_help') }}</p>
                                                                @endif

                                                                <label class="form-control">
                                                                    <input type="checkbox" name="run_backup" data-livewire-component="{{ $this->getId() }}" wire:model.live="run_backup">
                                                                   {{ trans('general.back_before_importing') }}
                                                                </label>

                                                            </div>


                                                            @if($statusText)
                                                                <div class="alert col-md-8 col-md-offset-3{{ $statusType == 'success' ? ' alert-success' : ($statusType == 'error' ? ' alert-danger' : ' alert-info') }}" style="padding-top: 20px;">
                                                                    {!! $statusText !!}
                                                                </div>
                                                            @endif


                                                            @if ($typeOfImport)
                                                                <div class="form-group col-md-12">
                                                                    <hr style="border-top: 1px solid lightgray">
                                                                    <h3>
                                                                        <i class="{{ Helper::iconTypeByItem($typeOfImport) }}">
                                                                        </i>
                                                                        {{ trans('general.map_fields', ['item_type' => ucwords($typeOfImport)]) }}
                                                                       </h3>
                                                                    <hr style="border-top: 1px solid lightgray">
                                                                </div>
                                                                <div class="form-group col-md-12">
                                                                    <div class="col-md-3 text-right">
                                                                        <strong>{{ trans('general.csv_header_field') }}</strong>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <strong>{{ trans('general.import_field') }}</strong>
                                                                    </div>
                                                                    <div class="col-md-5">
                                                                        <strong>{{ trans('general.sample_value') }}</strong>
                                                                    </div>
                                                                </div><!-- /div row -->

                                                                @if(! empty($headerRow))

                                                                    @foreach($headerRow as $index => $header)

                                                                        <div class="form-group col-md-12" wire:key="header-row-{{ $index }}">

                                                                            <label for="field_map.{{ $index }}" class="col-md-3 control-label text-right">{{ $header }}</label>
                                                                            <div class="col-md-4">
                                                                                <x-input.select
                                                                                    :name="'field_map.'.$index"
                                                                                    :for-livewire="true"
                                                                                    :placeholder="trans('general.importer.do_not_import')"
                                                                                    class="mappings"
                                                                                    style="min-width: 100%;"
                                                                                >
                                                                                    <option selected="selected" value="">Do Not Import</option>
                                                                                    @foreach($columnOptions[$typeOfImport] as $key => $value)
                                                                                        <option
                                                                                            value="{{ $key }}"
                                                                                            @selected(@$field_map[$index] === $key)
                                                                                            @disabled($key === '-')
                                                                                        >{{ $value }}</option>
                                                                                    @endforeach
                                                                                </x-input.select>
                                                                            </div>
									                                    @if (($this->activeFile->first_row) && (array_key_exists($index, $this->activeFile->first_row)))
                                                                            <div class="col-md-5">
                                                                                <p class="form-control-static">{{ str_limit($this->activeFile->first_row[$index], 50, '...') }}</p>
                                                                            </div>
                                                                        @else
                                                                            @php
                                                                            $statusText = trans('help.empty_file');
                                                                            $statusType = 'info';
                                                                            @endphp
                                                                        @endif
                                                                        </div><!-- /div row -->
                                                                    @endforeach
                                                                @else
                                                                    {{ trans('general.no_headers') }}
                                                                @endif

                                                                <div class="form-group col-md-12">
                                                                    <div class="col-md-3 text-left">
                                                                        <a href="#" wire:click.prevent="$set('activeFileId',null)">{{ trans('general.cancel') }}</a>
                                                                    </div>
                                                                    <div class="col-md-9">
                                                                        <button type="submit" class="btn btn-primary col-md-5" id="import">{{ trans('admin/hardware/message.import.import_button') }}</button>
                                                                        <br><br>
                                                                    </div>
                                                                </div>

                                                                @if($statusText)
                                                                    <div class="alert col-md-8 col-md-offset-3{{ $statusType == 'success' ? ' alert-success' : ($statusType == 'error' ? ' alert-danger' : ' alert-info') }}" style="padding-top: 20px;">
                                                                        {!! $statusText !!}
                                                                    </div>
                                                                @endif
                                                            @else
                                                                <div class="form-group col-md-10">
                                                                    <div class="col-md-3 text-left">
                                                                        <a href="#" wire:click.prevent="$set('activeFileId',null)">{{ trans('general.cancel') }}</a>
                                                                    </div>
                                                                </div>
                                                            @endif {{-- end of if ... $typeOfImport --}}

                                                        </div><!-- /div v-show -->                                                    </td>
                                                </tr>
                                            @endif
                                            </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <h2>{{ trans('general.importing') }}</h2>
                <p>{!!   trans('general.importing_help') !!}</p>
            </div>

        </div>
</div>
@script
    <script>

        {{-- TODO: Maybe change this to the file upload thing that's baked-in to Livewire? --}}
        $('#fileupload').fileupload({
            dataType: 'json',
            done: function(e, data) {
                $wire.$set('progress_bar_class', 'progress-bar-success');
                $wire.$set('progress_message', '<i class="fas fa-check faa-pulse animated"></i> {{ trans('general.notification_success') }}');
                $wire.$set('progress', 100);
            },
            add: function(e, data) {
                data.headers = {
                    "X-Requested-With": 'XMLHttpRequest',
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                };
                data.process().done( function () {data.submit();});
                $wire.$set('progress', 0);
                $wire.clearMessage();
            },
            progress: function(e, data) {
                $wire.$set('progress', parseInt((data.loaded / data.total * 100, 10)));
                $wire.$set('progress_message', '{{ trans('general.uploading') }}');
            },
            fail: function() {
                $wire.$set('progress_bar_class', "progress-bar-danger");
                $wire.$set('progress', 100);
                $wire.$set('progress_message', '<i class="fas fa-exclamation-triangle faa-pulse animated"></i> {{ trans('general.upload_error') }}');
            }
        })

        // For the importFile part:
        $(function () {


            // we have to hook up to the `<tr id='importer-file'>` at the root of this display,
            // because the #import button isn't visible until you click an import_type
            $('#upload-table').on('click', '#import', function () {
                if (!$wire.$get('typeOfImport')) {
                    $wire.$set('statusType', 'error');
                    $wire.$set('statusText', "An import type is required... "); //TODO: translate?
                    return;
                }
                $wire.$set('statusType', 'pending');
                $wire.$set('statusText', '<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> {{ trans('admin/hardware/form.processing_spinner') }}');
                $wire.generate_field_map().then(function (mappings_raw) {
                    var mappings = JSON.parse(mappings_raw)
                    // console.warn("Here is the mappings:")
                    // console.dir(mappings)
                    // console.warn("Uh, active file id is, I guess: "+$wire.$get('activeFile.id'))
                    var file_id = $wire.$get('activeFileId');
                    $.post({
                        {{-- I want to do something like: route('api.imports.importFile', $activeFile->id) }} --}}
                        url: "api/v1/imports/process/"+file_id, // maybe? Good a guess as any..FIXME. HARDCODED DUMB FILE
                        contentType: 'application/json',
                        data: JSON.stringify({
                            'import-update': !!$wire.$get('update'),
                            'send-welcome': !!$wire.$get('send_welcome'),
                            'import-type': $wire.$get('typeOfImport'),
                            'run-backup': !!$wire.$get('run_backup'),
                            'column-mappings': mappings
                        }),
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                        }
                    }).done( function (body) {
                        // Success
                        $wire.$set('statusType', "success");
                        $wire.$set('statusText', "{{ trans('general.success_redirecting') }}");
                        // console.dir(body)
                        window.location.href = body.messages.redirect_url;
                    }).fail( function (jqXHR, textStatus, error) {
                        // Failure
                        var body = jqXHR.responseJSON
                        if((body) && (body.status) && body.status == 'import-errors') {
                            $wire.$dispatch('importError', body.messages);
                            $wire.$set('import_errors', body.messages);

                            $wire.$set('statusType', 'error');
                            $wire.$set('statusText', "Error");

                        //  If Slack/notifications hits API thresholds, we *do* 500, but we never
                        //  actually surface that info.
                        //
                        // A 500 on notifications doesn't mean your import failed, so this is a confusing state.
                        //
                        //  Ideally we'd have a message like "Your import worked, but not all
                        // notifications could be sent".
                        } else {
                            console.warn("Not import-errors, just regular errors - maybe API limits")
                            $wire.$set('message_type', "warning");
                            if ((body) && (error in body)) {
                                $wire.$set('message', body.error ? body.error : "Unknown error - might just be throttling by notifications.");
                            } else {
                                $wire.$set('message', "{{ trans('general.importer_generic_error') }}");
                            }

                        }
                        $wire.$set('activeFileId', null); //$wire.$set('hideDetails')
                    });
                })
                return false;
            });})

    </script>
@endscript
