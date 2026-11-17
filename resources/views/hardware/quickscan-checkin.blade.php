@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.quickscan_checkin') }}
    @parent
@stop

{{-- Page content --}}
@section('content')

    <style>
        .input-group {
            padding-left: 0px !important;
        }
    </style>

    <x-container columns="2">

        <x-page-column class="col-md-6">
            <x-form :route="route('hardware/quickscancheckin')" id="checkin-form">
                <x-box>
                    <x-slot:header>
                        {{ trans('admin/hardware/general.bulk_checkin') }}
                    </x-slot:header>

                    {{-- Look up by asset_tag or serial. Serial is only
                         offered when the installation enforces unique
                         serials, since checkin-by-serial would otherwise
                         match ambiguously. Mirrors the audit-side
                         quickscan flow. --}}
                    <x-form.row
                        :label="trans('general.checkin_by_field')"
                        name="checkin_by_field"
                        :help_text="trans('general.checkin_by_field_help')"
                        help_icon="tip"
                        input_div_class="col-md-8"
                    >
                        <x-slot:input>
                            <select name="checkin_by_field" id="checkin_by_field" data-minimum-results-for-search="Infinity" style="width: 100% !important" class="form-control select2" aria-label="checkin_by_field" required>
                                <option value="asset_tag">{{ trans('general.asset_tag') }}</option>
                                <option value="serial" {{ ($snipeSettings->unique_serial != '1') ? 'disabled' : '' }}>{{ trans('general.serial_number') }}</option>
                            </select>
                        </x-slot:input>
                    </x-form.row>

                    {{-- Tag or serial input. Hand-rolled because the
                         label carries an id + swap data attributes that
                         x-form.row's label wrapper doesn't expose.
                         Matches the quickscan.blade.php pattern. --}}
                    <div class="form-group {{ $errors->has('checkin_key') ? 'error' : '' }}">
                        <label
                            for="checkin_key"
                            id="checkin_key_label"
                            class="col-md-3 control-label"
                            data-asset-tag-label="{{ trans('general.asset_tag') }}"
                            data-serial-label="{{ trans('general.serial_number') }}"
                        >{{ trans('general.asset_tag') }}</label>
                        <div class="col-md-9">
                            <div class="input-group col-md-11 required">
                                <input type="text" class="form-control" name="checkin_key" id="checkin_key" value="{{ old('checkin_key') }}" required>
                            </div>
                            <x-form.error name="checkin_key" />
                        </div>
                    </div>

                    {{-- Status --}}
                    <x-form.row
                        :label="trans('admin/hardware/form.status')"
                        name="status_id"
                        input_div_class="col-md-7"
                    >
                        <x-slot:input>
                            <x-input.select
                                name="status_id"
                                id="status_id"
                                :options="$statusLabel_list"
                                style="width:100%"
                                aria-label="status_id"
                            />
                        </x-slot:input>
                    </x-form.row>

                    <x-input.location-select
                        :label="trans('general.location')"
                        name="location_id"
                        :selected="old('location_id')"
                    />

                    <x-form.row
                        :label="trans('admin/hardware/form.notes')"
                        name="note"
                        type="textarea"
                        input_div_class="col-md-8"
                    />

                    {{-- Clear name on successful checkin --}}
                    <x-form.checkbox-row
                        name="clear_name"
                        :label="trans('general.clear_name')"
                    />

                    <x-slot:customfooter>
                        <div class="box-footer">
                            <a class="btn btn-link" href="{{ route('hardware.index') }}">{{ trans('button.cancel') }}</a>
                            <x-button.submit id="checkin_button" class="btn-success pull-right" :label="trans('general.checkin')" />
                        </div>
                    </x-slot:customfooter>
                </x-box>
            </x-form>
        </x-page-column>

        <x-page-column class="col-md-6">
            <div class="box box-default" id="checkedin-div" style="display: none">
                <div class="box-header with-border">
                    <h2 class="box-title">
                        {{ trans('general.quickscan_checkin_status') }} (<span id="checkin-counter">0</span> {{ trans('general.assets_checked_in_count') }})
                    </h2>
                </div>
                <div class="box-body">
                    <table id="checkedin" class="table table-striped snipe-table">
                        <thead>
                            <tr>
                                <th scope="col">{{ trans('general.asset_tag') }}</th>
                                <th scope="col">{{ trans('general.asset_model') }}</th>
                                <th scope="col">{{ trans('general.model_no') }}</th>
                                <th scope="col">{{ trans('general.quickscan_checkin_status') }}</th>
                                <th scope="col"></th>
                            </tr>
                            <tr id="checkin-loader" style="display: none;">
                                <td colspan="3">
                                    <x-icon type="spinner" /> {{ trans('general.processing') }}...
                                </td>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </x-page-column>

    </x-container>

@stop


{{-- Page-specific glue: AJAX checkin flow, dynamic result table, optional
     audio feedback. Same rationale as quickscan.blade.php — deeply specific
     to this screen, kept inline rather than polluting snipeit.js with
     quickscan-checkin-only handlers. --}}
@section('moar_scripts')
    <script nonce="{{ csrf_token() }}">
        $(document.body).on('change', '#checkin_by_field', function () {
            var $label = $('#checkin_key_label');
            var newText = this.value === 'serial'
                ? $label.data('serial-label')
                : $label.data('asset-tag-label');
            $label.text(newText);
        });

        $('#checkin-form').submit(function (event) {
            $('#checkedin-div').show();
            $('#checkin-loader').show();

            event.preventDefault();

            var formData = $('#checkin-form').serializeArray();

            $.ajax({
                url: '{{ route('api.asset.checkinbytag') }}',
                type: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                dataType: 'json',
                data: formData,
                success: function (data) {
                    if (data.status == 'success') {
                        $('#checkedin tbody').prepend("<tr class='success'><td>" + data.payload.asset_tag + "</td><td>" + data.payload.model + "</td><td>" + data.payload.model_number + "</td><td>" + data.messages + "</td><td><i class='fas fa-check text-success'></i></td></tr>");

                        @if ($user?->enable_sounds)
                            var audio = new Audio('{{ config('app.url') }}/sounds/success.mp3');
                            audio.play();
                        @endif

                        incrementOnSuccess();
                    } else {
                        handlecheckinFail(data);
                    }
                    $('input#checkin_key').val('');
                },
                error: function (data) {
                    handlecheckinFail(data);
                },
                complete: function () {
                    $('#checkin-loader').hide();
                    // Quickscan is a tight scan loop. Restore the submit
                    // button immediately so the next scan can fire without
                    // waiting for the global double-submit safety timeout.
                    window.enableSubmitsIn('#checkin-form');
                },
            });

            return false;
        });

        function handlecheckinFail(data) {
            @if ($user?->enable_sounds)
                var audio = new Audio('{{ config('app.url') }}/sounds/error.mp3');
                audio.play();
            @endif

            var asset_tag = data.payload.asset_tag || '';
            var model = data.payload.model || '';
            var model_number = data.payload.model_number || '';
            var messages = data.messages || '';

            $('#checkedin tbody').prepend("<tr class='danger'><td>" + asset_tag + "</td><td>" + model + "</td><td>" + model_number + "</td><td>" + messages + "</td><td><i class='fas fa-times text-danger'></i></td></tr>");
        }

        function incrementOnSuccess() {
            var x = parseInt($('#checkin-counter').html());
            var y = x + 1;
            $('#checkin-counter').html(y);
        }

        $('#checkin_key').focus();
    </script>
@stop
