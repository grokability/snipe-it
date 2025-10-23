@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/settings/general.labels_title') }}
    @parent
@stop

@section('header_right')
    <a href="{{ route('settings.index') }}" class="btn btn-primary"> {{ trans('general.back') }}</a>
@stop


{{-- Page content --}}
@section('content')

    <style>
        .checkbox label {
            padding-right: 40px;
        }
    </style>

    <form method="POST" action="{{ route('settings.labels.save') }}" accept-charset="UTF-8" id="settingsForm" autocomplete="off" class="form-horizontal" role="form">
    <!-- CSRF Token -->
    {{csrf_field()}}

    <div class="row">
        <div class="col-sm-10 col-sm-offset-1 col-md-10">

            <div class="panel box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">
                        <x-icon type="labels"/>
                        {{ trans('admin/settings/general.labels') }}
                    </h2>
                </div>
                <div class="box-body">

                    <div class="col-md-12">

                        <!-- New Label Engine -->
                        <div class="form-group" {{ $errors->has('label2_enable') ? 'error' : '' }}">

                            <div class="col-md-7 col-md-offset-3">

                                <label class="form-control">
                                    <input type="checkbox" value="1" name="label2_enable"{{ ((old('label2_enable') == '1') || ($setting->label2_enable) == '1') ? ' checked="checked"' : '' }} aria-label="label2_enable">
                                    <label for="label2_enable">{{ trans('admin/settings/general.label2_enable') }}</label>
                                </label>

                                {!! $errors->first('label2_enable', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}

                                <p class="help-block">
                                    {!! trans('admin/settings/general.label2_enable_help') !!}
                                </p>



                            </div>
                        </div>


                        @if ($setting->label2_enable)
                            <!-- New Settings -->

                            <!-- Template -->
                            <div class="form-group{{ $errors->has('label2_template') ? ' has-error' : '' }}">

                                <div class="col-md-9 col-md-offset-3">
                                    <table

                                        data-columns="{{ \App\Presenters\LabelPresenter::dataTableLayout() }}"
                                        data-cookie="true"
                                        data-cookie-id-table="label2TemplateTable"
                                        data-id-table="label2TemplateTable"


                                        data-select-item-name="label2_template"
                                        data-id-field="name"



                                        data-side-pagination="server"
                                        data-sort-name="name"
                                        data-sort-order="asc"
                                        data-url="{{ route('api.labels.index') }}"
                                        id="label2TemplateTable"
                                        class="table table-striped snipe-table"
                                    ></table>
                                    <script>
                                        document.addEventListener('DOMContentLoaded', () => {
                                            const chosenLabel = "{{ old('label2_template', $chosenLabel ?? '') }}";
                                            $('#label2TemplateTable').on('load-success.bs.table', (e) => {
                                                if (chosenLabel) {
                                                    $('input[name="label2_template"][value="' + chosenLabel + '"]').prop('checked', true);
                                                }
                                                let form = document.getElementById('settingsForm');
                                                form.dispatchEvent(new Event('change'));
                                            });
                                        });
                                    </script>
                                </div>
                            </div>

                            <!-- Title -->
                            <div class="form-group{{ $errors->has('label2_title') ? ' has-error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="label2_title" class="control-label">{{trans('admin/settings/general.label2_title')}}</label>
                                </div>
                                <div class="col-md-7">
                                    <input class="form-control" aria-label="label2_title" name="label2_title" type="text" id="label2_title" value="{{ old('label2_title', $setting->label2_title) }}">
                                    {!! $errors->first('label2_title', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                    <p class="help-block">{!! trans('admin/settings/general.label2_title_help') !!}</p>
                                    <p class="help-block">
                                        {!! trans('admin/settings/general.label2_title_help_phold') !!}.<br />
                                        {!! trans('admin/settings/general.help_asterisk_bold') !!}.<br />
                                        {!!
                                            trans('admin/settings/general.help_blank_to_use', [
                                                'setting_name' => trans('admin/settings/general.barcodes').' &gt; '.trans('admin/settings/general.qr_text')
                                            ])
                                        !!}
                                    </p>
                                </div>
                            </div>

                            <!-- Use Asset Logo -->
                            <div class="form-group" {{ $errors->has('label2_asset_logo') ? 'error' : '' }}">
                                <div class="col-md-7 col-md-offset-3">

                                    <label class="form-control">
                                        <input type="checkbox" value="1" name="label2_asset_logo"{{ ((old('label2_asset_logo') == '1') || ($setting->label2_asset_logo) == '1') ? ' checked="checked"' : '' }} aria-label="label2_asset_logo">
                                        <label for="label2_asset_logo">{{ trans('admin/settings/general.label2_asset_logo') }}</label>
                                    </label>
                                    <p class="help-block">
                                        {!! trans('admin/settings/general.label2_asset_logo_help', ['setting_name' => trans('admin/settings/general.brand').' &gt; '.trans('admin/settings/general.label_logo')]) !!}
                                    </p>

                                </div>
                            </div>
                        @endif
                        @if($setting->label2_enable == 0)
                            @if ($is_gd_installed)
                            <!-- barcode -->
                            <div class="form-group">
                                <div class="col-md-9 col-md-offset-3">
                                    <label class="form-control">
                                        <input type="checkbox" name="alt_barcode_enabled" value="1" @checked(old('alt_barcode_enabled', $setting->alt_barcode_enabled)) aria-label="alt_barcode_enabled"/>
                                        {{ trans('admin/settings/general.display_alt_barcode') }}
                                    </label>
                                </div>
                            </div>
                            @endif
                        @endif
                        
                            <!-- 1D Barcode Type -->
                            <div class="form-group{{ $errors->has('label2_1d_type') ? ' has-error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="label2_1d_type" class="control-label">{{ trans('admin/settings/general.label2_1d_type') }}</label>
                                </div>
                                <div class="col-md-7">
                                    @php
                                        $select1DValues = [
                                            'C128'    => 'C128',
                                            'C39'     => 'C39',
                                            'EAN5'    => 'EAN5',
                                            'EAN13'   => 'EAN13',
                                            'UPCA'    => 'UPCA',
                                            'UPCE'    => 'UPCE',
                                            'none'    => trans('admin/settings/general.none'),
                                        ];
                                    @endphp
                                    <x-input.select
                                        name="label2_1d_type"
                                        id="label2_1d_type"
                                        :options="$select1DValues"
                                        :selected="old('label2_1d_type', $setting->label2_1d_type)"
                                        class="col-md-4"
                                        aria-label="label2_1d_type"
                                    />
                                    {!! $errors->first('label2_1d_type', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                    <p class="help-block">
                                        {{ trans('admin/settings/general.label2_1d_type_help') }}.
                                        {!!
                                            trans('admin/settings/general.help_default_will_use', [
                                                'default' => trans('admin/settings/general.default'),
                                                'setting_name' => trans('admin/settings/general.barcodes').' &gt; '.trans('admin/settings/general.alt_barcode_type'),
                                            ])
                                        !!}
                                    </p>
                                </div>
                            </div>
                                
                @if($setting->label2_enable == 0)

                        <!-- qr code -->
                        <div class="form-group">
                            <div class="col-md-9 col-md-offset-3">
                                <label class="form-control">
                                    <input type="checkbox" name="qr_code" value="1" @checked(old('qr_code', $setting->qr_code)) aria-label="qr_code" />
                                    {{ trans('admin/settings/general.display_qr') }}
                                </label>
                            </div>
                        </div>
                    @endif
                                <!-- 2D Barcode Type -->
                                <div class="form-group{{ $errors->has('label2_2d_type') ? ' has-error' : '' }}">
                                    <div class="col-md-3 text-right">
                                        <label for="label2_2d_type" class="control-label">{{ trans('admin/settings/general.label2_2d_type') }}</label>
                                    </div>
                                    <div class="col-md-7">
                                        @php
                                            $select2DValues = [
                                                'QRCODE'     => 'QRCODE',
                                            ];
                                            if ($setting->label2_enable == 1) {
                                                $select2DValues['PDF417'] = 'PDF417';
                                            }
                                             $select2DValues = array_merge($select2DValues, [
                                                'DATAMATRIX' => 'DATAMATRIX',
                                                'none'       => trans('admin/settings/general.none'),
                                            ]);
                                        @endphp
                                        <x-input.select
                                            name="label2_2d_type"
                                            id="label2_2d_type"
                                            :options="$select2DValues"
                                            :selected="old('label2_2d_type', $setting->label2_2d_type)"
                                            class="col-md-4"
                                            aria-label="label2_2d_type"
                                        />
                                        {!! $errors->first('label2_2d_type', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                        <p class="help-block">
                                            {{ trans('admin/settings/general.label2_2d_type_help', ['current' => $setting->barcode_type]) }}.
                                            {!!
                                                trans('admin/settings/general.help_default_will_use')
                                            !!}
                                        </p>
                                    </div>
                                </div>

                    @if($setting->label2_enable == 0)
                                <!-- QR Text -->
                                <div class="form-group{{ $errors->has('qr_text') ? ' has-error' : '' }}">
                                    <div class="col-md-3 text-right">
                                        <label for="qr_text" class="control-label">{{ trans('admin/settings/general.qr_text') }}</label>
                                    </div>
                                    <div class="col-md-7">
                                        @if ($setting->qr_code == 1)
                                            <input
                                                class="form-control"
                                                placeholder="Property of Your Company"
                                                rel="txtTooltip"
                                                title="Extra text that you would like to display on your labels."
                                                data-toggle="tooltip"
                                                data-placement="top"
                                                name="qr_text"
                                                type="text"
                                                id="qr_text"
                                                value="{{ old('qr_text', $setting->qr_text) }}"
                                            >
                                        @else
                                            <input
                                                class="form-control"
                                                disabled="disabled"
                                                placeholder="Property of Your Company"
                                                name="qr_text"
                                                type="text"
                                                id="qr_text"
                                                value="{{ old('qr_text', $setting->qr_text) }}"
                                            >
                                            <p class="help-block">{{ trans('admin/settings/general.qr_help') }}</p>
                                        @endif
                                        {!! $errors->first('qr_text', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                    </div>
                                </div>

                                <!-- Nuke barcode cache -->
                                <div class="form-group">
                                    <div class="col-md-3 text-right">
                                        <label for="purge_barcodes" class="control-label">{{ trans('admin/settings/general.purge_barcodes') }}</label>
                                    </div>
                                    <div class="col-md-7">
                                        <a class="btn btn-default btn-sm pull-left" id="purgebarcodes" style="margin-right: 10px;">
                                            {{ trans('admin/settings/general.barcode_delete_cache') }}
                                        </a>
                                        <span id="purgebarcodesicon"></span>
                                        <span id="purgebarcodesresult"></span>
                                        <span id="purgebarcodesstatus"></span>
                                        {!! $errors->first('purgebarcodes', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                        <p class="help-block">{{ trans('admin/settings/general.barcodes_help') }}</p>
                                    </div>
                                </div>
                       @endif
                        @if ($setting->label2_enable)
                            <!-- 2D Barcode Target -->
                            <div class="form-group{{ $errors->has('label2_2d_target') ? ' has-error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="label2_2d_target" class="control-label">{{ trans('admin/settings/general.label2_2d_target') }}</label>
                                </div>
                                <div class="col-md-9">
                                    <x-input.select
                                        name="label2_2d_target"
                                        id="label2_2d_target"
                                        :options="['hardware_id'=>'/hardware/{id} ('.trans('admin/settings/general.default').')',
                                                   'ht_tag'=>'/ht/{asset_tag}',
                                                   'location' => '/location/{location_id}',
                                                   ]"
                                        :selected="old('label2_2d_target', $setting->label2_2d_target)"
                                        class="col-md-4"
                                        aria-label="label2_2d_target"
                                    />
                                    {!! $errors->first('label2_2d_target', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                    <p class="help-block">{{ trans('admin/settings/general.label2_2d_target_help') }}</p>
                                </div>
                            </div>
                            <!-- Field offset -->
                            <div class="form-group{{ $errors->has('label2_empty_row_count') ? ' has-error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="label2_empty_row_count" class="control-label">{{ trans('admin/settings/general.empty_row_count') }}</label>
                                </div>
                                <div class="col-md-9 col-xl-2 col-lg-2">
                                        <input
                                                class="form-control"
                                                aria-label="Empty Row Count"
                                                name="label2_empty_row_count"
                                                type="number"
                                                id="label2_empty_row_count"
                                                min="0"
                                                max="5"
                                                value="{{ old('label2_empty_row_count', $setting->label2_empty_row_count) }}"
                                        >
                                </div>

                                <div class="col-md-9 col-md-offset-3">
                                    <p class="help-block">{!! trans('admin/settings/general.empty_row_count_help') !!}</p>
                                    {!! $errors->first('label2_empty_row_count', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                </div>
                            </div>
                            <div class="col-md-9 col-md-offset-3" style="margin-bottom: 10px;">
                                @include('partials.label2-preview')
                            </div>
                            <!-- Fields -->
                            <div class="form-group {{ $errors->has('label2_fields') ? 'error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="label2_fields">{{ trans('admin/settings/general.label2_fields') }}</label>
                                </div>
                                <div class="col-md-9">
                                    @include('partials.label2-field-definitions', [ 'name' => 'label2_fields', 'value' => old('label2_fields', $setting->label2_fields), 'customFields' => $customFields, 'template' => $setting->label2_template])
                                    {!! $errors->first('label2_fields', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                    <p class="help-block">{{ trans('admin/settings/general.label2_fields_help') }}</p>
                                </div>
                            </div>

                            @include('partials.bootstrap-table')

                        @else

                            <!-- Hidden version of new settings -->
                            <input name="label2_template" type="hidden" value="{{ old('label2_template', $setting->label2_template) }}" />
                            <input name="label2_title" type="hidden" value="{{ old('label2_title', $setting->label2_title) }}" />
                            <input name="label2_asset_logo" type="hidden" value="{{ old('label2_asset_logo', $setting->label2_asset_logo) }}" />
                            <input name="label2_fields" type="hidden" value="{{ old('label2_fields', $setting->label2_fields) }}" />
                        @endif

                        @if ($setting->label2_enable && ($setting->label2_template != 'DefaultLabel'))
                            <!-- Hidden version of legacy settings -->
                            <input name="labels_per_page" type="hidden" value="{{ old('labels_per_page', $setting->labels_per_page) }}" />
                            <input name="labels_fontsize" type="hidden" value="{{ old('labels_fontsize', $setting->labels_fontsize) }}" />
                            <input name="labels_width" type="hidden" value="{{ old('labels_width', $setting->labels_width) }}" />
                            <input name="labels_height" type="hidden" value="{{ old('labels_height', $setting->labels_height) }}" />
                            <input name="labels_display_sgutter" type="hidden" value="{{ old('labels_display_sgutter', $setting->labels_display_sgutter) }}" />
                            <input name="labels_display_bgutter" type="hidden" value="{{ old('labels_display_bgutter', $setting->labels_display_bgutter) }}" />
                            <input name="labels_pmargin_top" type="hidden" value="{{ old('labels_pmargin_top', $setting->labels_pmargin_top) }}" />
                            <input name="labels_pmargin_bottom" type="hidden" value="{{ old('labels_pmargin_bottom', $setting->labels_pmargin_bottom) }}" />
                            <input name="labels_pmargin_left" type="hidden" value="{{ old('labels_pmargin_left', $setting->labels_pmargin_left) }}" />
                            <input name="labels_pmargin_right" type="hidden" value="{{ old('labels_pmargin_right', $setting->labels_pmargin_right) }}" />
                            <input name="labels_pagewidth" type="hidden" value="{{ old('labels_pagewidth', $setting->labels_pagewidth) }}" />
                            <input name="labels_pageheight" type="hidden" value="{{ old('labels_pageheight', $setting->labels_pageheight) }}" />
                            <input name="labels_display_name" type="hidden" value="{{ old('labels_display_name', $setting->labels_display_name) }}" />
                            <input name="labels_display_serial" type="hidden" value="{{ old('labels_display_serial', $setting->labels_display_serial) }}" />
                            <input name="labels_display_tag" type="hidden" value="{{ old('labels_display_tag', $setting->labels_display_tag) }}" />
                            <input name="labels_display_model" type="hidden" value="{{ old('labels_display_model', $setting->labels_display_model) }}" />
                            <input name="labels_display_company_name" type="hidden" value="{{ old('labels_display_company_name', $setting->labels_display_company_name) }}" />
                        @else
                            <!-- Legacy settings -->
                    <style>
                        .checkbox label {
                            padding-right: 40px;
                        }
                    </style>

                    <!-- CSRF Token -->
                    {{csrf_field()}}

                            <div class="form-group{{ $errors->has('labels_per_page') ? ' has-error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="labels_per_page" class="control-label">{{ trans('admin/settings/general.labels_per_page') }}</label>
                                </div>
                                <div class="col-md-9">
                                    <input class="form-control" style="width: 100px;" aria-label="labels_per_page" name="labels_per_page" type="text" value="{{ old('labels_per_page', $setting->labels_per_page) }}" id="labels_per_page">
                                    {!! $errors->first('labels_per_page', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('labels_fontsize') ? ' has-error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="labels_fontsize" class="control-label">{{ trans('admin/settings/general.labels_fontsize') }}</label>
                                </div>
                                <div class="col-md-2">
                                    <div class="input-group">
                                        <input class="form-control" aria-label="labels_fontsize" name="labels_fontsize" type="text" value="{{ old('labels_fontsize', $setting->labels_fontsize) }}" id="labels_fontsize">
                                        <div class="input-group-addon">{{ trans('admin/settings/general.text_pt') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-9 col-md-offset-3">
                                    {!! $errors->first('labels_fontsize', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('labels_width') ? ' has-error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="labels_width" class="control-label">{{ trans('admin/settings/general.label_dimensions') }}</label>
                                </div>
                                <div class="col-md-3 text-right">
                                    <div class="input-group">
                                        <input class="form-control" aria-label="labels_width" name="labels_width" type="text" value="{{ old('labels_width', $setting->labels_width) }}" id="labels_width">
                                        <div class="input-group-addon">{{ trans('admin/settings/general.width_w') }}</div>
                                    </div>
                                    {!! $errors->first('labels_width', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                </div>
                                <div class="col-md-3 text-right">
                                    <div class="input-group">
                                        <input class="form-control" aria-label="labels_height" name="labels_height" type="text" value="{{ old('labels_height', $setting->labels_height) }}">
                                        <div class="input-group-addon">{{ trans('admin/settings/general.height_h') }}</div>
                                    </div>
                                    {!! $errors->first('labels_height', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('labels_display_sgutter') ? ' has-error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="labels_display_sgutter">{{ trans('admin/settings/general.label_gutters') }}</label>
                                </div>
                                <div class="col-md-3 text-right">
                                    <div class="input-group">
                                        <input class="form-control" aria-label="labels_display_sgutter" name="labels_display_sgutter" type="text" value="{{ old('labels_display_sgutter', $setting->labels_display_sgutter) }}" id="labels_display_sgutter">
                                        <div class="input-group-addon">{{ trans('admin/settings/general.horizontal') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3 text-right">
                                    <div class="input-group">
                                        <input class="form-control" aria-label="labels_display_bgutter" name="labels_display_bgutter" type="text" value="{{ old('labels_display_bgutter', $setting->labels_display_bgutter) }}">
                                        <div class="input-group-addon">{{ trans('admin/settings/general.vertical') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-9 col-md-offset-3">
                                    {!! $errors->first('labels_display_sgutter', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                    {!! $errors->first('labels_display_bgutter', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('labels_pmargin_top') ? ' has-error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="labels_pmargin_top">{{ trans('admin/settings/general.page_padding') }}</label>
                                </div>
                                <div class="col-md-3 text-right">
                                    <div class="input-group" style="margin-bottom: 15px;">
                                        <input class="form-control" aria-label="labels_pmargin_top" name="labels_pmargin_top" type="text" value="{{ old('labels_pmargin_top', $setting->labels_pmargin_top) }}" id="labels_pmargin_top">
                                        <div class="input-group-addon">{{ trans('admin/settings/general.top') }}</div>
                                    </div>
                                    <div class="input-group">
                                        <input class="form-control" aria-label="labels_pmargin_right" name="labels_pmargin_right" type="text" value="{{ old('labels_pmargin_right', $setting->labels_pmargin_right) }}">
                                        <div class="input-group-addon">{{ trans('admin/settings/general.right') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3" style="margin-left: 10px; ">
                                    <div class="input-group" style="margin-bottom: 15px;">
                                        <input class="form-control" aria-label="labels_pmargin_bottom" name="labels_pmargin_bottom" type="text" value="{{ old('labels_pmargin_bottom', $setting->labels_pmargin_bottom) }}">
                                        <div class="input-group-addon">{{ trans('admin/settings/general.bottom') }}</div>
                                    </div>
                                    <div class="input-group">
                                        <input class="form-control" aria-label="labels_pmargin_left" name="labels_pmargin_left" type="text" value="{{ old('labels_pmargin_left', $setting->labels_pmargin_left) }}">
                                        <div class="input-group-addon">{{ trans('admin/settings/general.left') }}</div>
                                    </div>

                                </div>
                            </div>

                            <div class="form-group{{ (($errors->has('labels_pageheight')) || $errors->has('labels_pagewidth')) ? ' has-error' : '' }}">
                                <div class="col-md-3 text-right">
                                    <label for="labels_pagewidth" class="control-label">{{ trans('admin/settings/general.page_dimensions') }}</label>
                                </div>
                                <div class="col-md-3 text-right">
                                    <div class="input-group">
                                        <input class="form-control" aria-label="labels_pagewidth" name="labels_pagewidth" type="text" value="{{ old('labels_pagewidth', $setting->labels_pagewidth) }}" id="labels_pagewidth">
                                        <div class="input-group-addon">{{ trans('admin/settings/general.width_w') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3 form-group" style="margin-left: 10px">
                                    <div class="input-group">
                                        <input class="form-control" aria-label="labels_pageheight" name="labels_pageheight" type="text" value="{{ old('labels_pageheight', $setting->labels_pageheight) }}">
                                        <div class="input-group-addon">{{ trans('admin/settings/general.height_h') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-9 col-md-offset-3">
                                    {!! $errors->first('labels_pagewidth', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                    {!! $errors->first('labels_pageheight', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                </div>
                            </div>
                        @endif
                        @if(!$setting->label2_enable)
                            <div class="form-group">
                                <div class="col-md-3 text-right">
                                    <label for="labels_display" class="control-label">{{ trans('admin/settings/general.label_fields') }}</label>
                                </div>
                                <div class="col-md-9">
                                        <label class="form-control">
                                            <input type="checkbox" name="labels_display_name" value="1" @checked(old('labels_display_name',   $setting->labels_display_name)) aria-label="labels_display_name" />
                                            {{ trans('admin/hardware/form.name') }}
                                        </label>
                                        <label class="form-control">
                                            <input type="checkbox" name="labels_display_serial" value="1" @checked(old('labels_display_serial',   $setting->labels_display_serial)) aria-label="labels_display_serial" />
                                            {{ trans('admin/hardware/form.serial') }}
                                        </label>
                                        <label class="form-control">
                                            <input type="checkbox" name="labels_display_tag" value="1" @checked(old('labels_display_tag',   $setting->labels_display_tag)) aria-label="labels_display_tag" />
                                            {{ trans('admin/hardware/form.tag') }}
                                        </label>
                                        <label class="form-control">
                                            <input type="checkbox" name="labels_display_model" value="1" @checked(old('labels_display_model',   $setting->labels_display_model)) aria-label="labels_display_model" />
                                            {{ trans('admin/hardware/form.model') }}
                                        </label>
                                        <label class="form-control">
                                            <input type="checkbox" name="labels_display_company_name" value="1" @checked(old('labels_display_company_name',   $setting->labels_display_company_name)) aria-label="labels_display_company_name"/>
                                            {{ trans('admin/companies/table.name') }}
                                        </label>
                                </div> <!--/.col-md-9-->
                            </div> <!--/.form-group-->
                        @endif
                    </div>

                </div> <!--/.box-body-->
                <div class="box-footer">
                    <div class="text-left col-md-6">
                        <a class="btn btn-link text-left" href="{{ route('settings.index') }}">{{ trans('button.cancel') }}</a>
                    </div>
                    <div class="text-right col-md-6">
                        <button type="submit" class="btn btn-primary"><x-icon type="checkmark" /> {{ trans('general.save') }}</button>
                    </div>

                </div>
            </div> <!-- /box -->
        </div> <!-- /.col-md-8-->
    </div> <!-- /.row-->

    </form>

@stop

@push('js')
    <script nonce="{{ csrf_token() }}">
        // Delete barcodes
        $("#purgebarcodes").click(function(){
            $("#purgebarcodesrow").removeClass('text-success');
            $("#purgebarcodesrow").removeClass('text-danger');
            $("#purgebarcodesicon").html('');
            $("#purgebarcodesstatus").html('');
            $('#purgebarcodesstatus-error').html('');
            $("#purgebarcodesicon").html('<i class="fas fa-spinner spin"></i> {{ trans('admin/settings/general.barcodes_spinner') }}');
            $.ajax({
                url: '{{ route('api.settings.purgebarcodes') }}',
                type: 'POST',
                headers: {
                    "X-Requested-With": 'XMLHttpRequest',
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                },
                data: {},
                dataType: 'json',

                success: function (data) {
                    console.dir(data);
                    $("#purgebarcodesicon").html('');
                    $("#purgebarcodesstatus").html('');
                    $('#purgebarcodesstatus-error').html('');
                    $("#purgebarcodesstatus").removeClass('text-danger');
                    $("#purgebarcodesstatus").addClass('text-success');
                    if (data.message) {
                        $("#purgebarcodesstatus").html('<i class="fas fa-check text-success"></i> ' + data.message);
                    }
                },

                error: function (data) {

                    $("#purgebarcodesicon").html('');
                    $("#purgebarcodesstatus").html('');
                    $('#purgebarcodesstatus-error').html('');
                    $("#purgebarcodesstatus").removeClass('text-success');
                    $("#purgebarcodesstatus").addClass('text-danger');
                    $("#purgebarcodesicon").html('<i class="fas fa-exclamation-triangle text-danger"></i>');
                    $('#purgebarcodesstatus').html('Files could not be deleted.');
                    if (data.responseJSON) {
                        $('#purgebarcodesstatus-error').html('Error: ' + data.responseJSON.messages);
                    } else {
                        console.dir(data);
                    }

                }


            });
        });

    </script>
    {{-- Can't use @script here because we're not in a livewire component so let's manually load --}}
    @livewireScripts
@endpush
