@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.bulkaudit') }}
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
            <x-form id="audit-form">
                <x-box>

                    {{-- Audit by tag, serial, or 2D barcode scan. Serial
                         is disabled unless unique_serial is on. The 2D
                         barcode option only shows when the install has
                         2D barcodes enabled. --}}
                    <x-form.row
                        :label="trans('general.audit_by_field')"
                        name="audit_by_field"
                        :help_text="trans('general.audit_by_field_help')"
                        help_icon="tip"
                        input_div_class="col-md-8"
                    >
                        <x-slot:input>
                            <select name="audit_by_field" data-minimum-results-for-search="Infinity" id="audit_by_field" style="width: 100% !important" class="form-control select2" aria-label="audit_by_field" required>
                                <option value="asset_tag">{{ trans('general.asset_tag') }}</option>
                                <option value="serial" {{ ($settings->unique_serial != '1') ? 'disabled' : '' }}>{{ trans('general.serial_number') }}</option>
                                @if ($settings->label2_2d_type !== 'none')
                                    <option value="2d_barcode">{{ trans('general.audit_by_2d_barcode') }}</option>
                                @endif
                            </select>
                        </x-slot:input>
                    </x-form.row>

                    {{-- Tag, serial, or 2D barcode input. Hand-rolled because
                         the <label> needs a JS-targeted id (audit_key_label)
                         plus data-* attributes carrying the per-option
                         translations the audit-by-field switcher script
                         swaps between. x-form.row's label wrapper doesn't
                         accept pass-through attributes. --}}
                    <div class="form-group {{ $errors->has('audit_key') ? 'error' : '' }}">
                        <label
                            for="audit_key"
                            id="audit_key_label"
                            class="col-md-3 control-label"
                            data-asset-tag-label="{{ trans('general.asset_tag') }}"
                            data-serial-label="{{ trans('general.serial_number') }}"
                            data-2d-barcode-label="{{ trans('general.audit_by_2d_barcode') }}"
                        >{{ trans('general.asset_tag') }}</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="audit_key" id="audit_key" required value="{{ old('audit_key') }}">
                            <x-form.error name="audit_key" />
                        </div>
                    </div>

                    <x-input.location-select
                        :label="trans('general.location')"
                        name="location_id"
                        :selected="old('location_id')"
                    />

                    {{-- Update-location checkbox. The label carries an
                         inline popover trigger (icon + Bootstrap popover
                         data-* attributes) alongside the checkbox text,
                         which doesn't fit x-form.checkbox-row's escaped
                         :label prop — stays hand-rolled. --}}
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-md-9">
                            <label class="form-control">
                                <input type="checkbox" value="1" name="update_location" {{ old('update_location') == '1' ? ' checked="checked"' : '' }}>
                                <span>{{ trans('admin/hardware/form.asset_location') }}
                                    <a href="#" class="text-dark-gray" tabindex="0" role="button" data-toggle="popover" data-trigger="focus" title="<i class='far fa-life-ring'></i> {{ trans('general.more_info') }}" data-html="true" data-content="{{ trans('general.quickscan_bulk_help') }}">
                                        <x-icon type="more-info" />
                                    </a>
                                </span>
                            </label>
                        </div>
                    </div>

                    {{-- Next audit date --}}
                    <x-form.row
                        :label="trans('general.next_audit_date')"
                        name="next_audit_date"
                        input_div_class="col-md-9"
                    >
                        <x-slot:input>
                            <x-input.datepicker
                                id="next_audit_date"
                                name="next_audit_date"
                                :value="old('next_audit_date', $next_audit_date)"
                                :placeholder="trans('general.next_audit_date')"
                                col_size_class="col-md-5"
                            />
                        </x-slot:input>
                    </x-form.row>

                    <x-form.row
                        :label="trans('admin/hardware/form.notes')"
                        name="note"
                        type="textarea"
                        input_div_class="col-md-8"
                    />

                    {{-- Clear name on successful audit --}}
                    <x-form.checkbox-row
                        name="clear_name"
                        :label="trans('general.clear_name')"
                    />

                    <x-slot:customfooter>
                        <div class="box-footer">
                            <a class="btn btn-link" href="{{ route('hardware.index') }}">{{ trans('button.cancel') }}</a>
                            <x-button.submit id="audit_button" class="btn-success pull-right" :label="trans('general.audit')" />
                        </div>
                    </x-slot:customfooter>

                </x-box>
            </x-form>
        </x-page-column>

        <x-page-column class="col-md-6">
            <div class="box box-default" id="audited-div" style="display: none">
                <div class="box-header with-border">
                    <h2 class="box-title">
                        {{ trans('general.bulkaudit_status') }} (<span id="audit-counter">0</span> {{ trans('general.assets_audited') }})
                    </h2>
                </div>
                <div class="box-body">
                    <table id="audited" class="table table-striped snipe-table">
                        <thead>
                            <tr>
                                <th scope="col">{{ trans('general.audit') }}</th>
                                <th scope="col">{{ trans('general.bulkaudit_status') }}</th>
                                <th scope="col">{{ trans('general.status') }}</th>
                                <th scope="col">{{ trans('general.notes') }}</th>
                                <th scope="col"></th>
                            </tr>
                            <tr id="audit-loader" style="display: none;">
                                <td colspan="3">
                                    <x-icon type="spinner" />
                                    {{ trans('admin/hardware/form.processing') }}
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


{{-- Page-specific glue: the audit flow (AJAX form submit, dynamic result
     table, optional audio feedback gated on the user's enable_sounds
     preference) is deeply specific to this screen, so it stays inline
     rather than polluting snipeit.js with quickscan-only handlers. Label
     swap and initial focus are trivial DOM wiring that come along for
     the ride so we don't split this into two spots. --}}
@section('moar_scripts')
    <script nonce="{{ csrf_token() }}">
        // Swap the audit_key label text between "Asset Tag", "Serial
        // Number", and "2D Barcode" as the user toggles the by-field
        // select. All three translations ride on the label element's
        // data attributes so this script doesn't need to embed them.
        $(document.body).on('change', '#audit_by_field', function () {
            var $label = $('#audit_key_label');
            var dataKey = {
                serial: 'serial-label',
                '2d_barcode': '2d-barcode-label',
            }[this.value] || 'asset-tag-label';
            $label.text($label.data(dataKey));
        });

        $('#audit-form').submit(function (event) {
            $('#audited-div').show();
            $('#audit-loader').show();

            event.preventDefault();

            var formData = $('#audit-form').serializeArray();
            var audit_key = $('#audit_key').val();

            $.ajax({
                url: '{{ route('api.asset.audit.legacy') }}',
                type: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                dataType: 'json',
                data: formData,
                success: function (data) {
                    if (data.status == 'success') {
                        $('#audited tbody').prepend("<tr class='success'><td>" + data.payload.audit_by_field + ': ' + data.payload.audit_key + "</td><td>" + data.messages + "</td><td>" + data.payload.status_label + " (" + data.payload.status_type + ")</td><td>" + data.payload.note + "</td><td><i class='fas fa-check' style='font-size:18px;'></i></td></tr>");

                        @if ($user?->enable_sounds)
                            var audio = new Audio('{{ config('app.url') }}/sounds/success.mp3');
                            audio.play();
                        @endif

                        incrementOnSuccess();
                    } else {
                        handleAuditFail(data);
                    }
                    $('input#audit_key').val('');
                },
                error: function (data) {
                    handleAuditFail(data, audit_key);
                },
                complete: function () {
                    $('#audit-loader').hide();
                    // Quickscan audit is a tight scan loop. Restore the submit
                    // button immediately so the next scan can fire without
                    // waiting for the global double-submit safety timeout.
                    window.enableSubmitsIn('#audit-form');
                },
            });

            return false;
        });

        function handleAuditFail(data) {
            @if ($user?->enable_sounds)
                var audio = new Audio('{{ config('app.url') }}/sounds/error.mp3');
                audio.play();
            @endif

            let messages = '';
            if ((data.messages) && (data.messages)) {
                for (let x in data.messages) {
                    messages += data.messages[x];
                }
            }

            $('#audited tbody').prepend("<tr class='danger'><td>" + data.payload.audit_by_field + ': ' + data.payload.audit_key + "</td><td>" + messages + "</td><td></td><td></td><td><i class='fas fa-times' style='font-size:18px;'></i></td></tr>");
        }

        function incrementOnSuccess() {
            var x = parseInt($('#audit-counter').html());
            var y = x + 1;
            $('#audit-counter').html(y);
        }

        $('#audit_key').focus();
    </script>
@stop
