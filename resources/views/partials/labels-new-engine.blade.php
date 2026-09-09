<!-- New Settings -->
<fieldset name="select-template">
    <x-form.legend>
        {{ trans('admin/settings/general.select_template') }}
    </x-form.legend>

    <!-- Template -->
    <div class="form-group{{ $errors->has('label2_template') ? ' has-error' : '' }}">

        <div class="col-md-12">
            <x-container>
                <x-box name="label-templates">
                    <x-table.labels/>
                </x-box>
            </x-container>
        </div>
    </div>
</fieldset>

<fieldset name="label-preview">
    <x-form.legend>
        {{ trans('admin/settings/general.label2_label_preview') }}: <code
                id="label2_preview_template">{{ $setting->label2_template_name }}</code>
    </x-form.legend>
    <div class="col-md-12" style="margin-bottom: 10px;">
        @include('partials.label2-preview')
    </div>
</fieldset>

<fieldset name="field-definitions">
    <x-form.legend help_text="{!! trans('admin/settings/general.label2_fields_help') !!}">
        {{ trans('admin/settings/general.label_fields') }}
    </x-form.legend>
    <div class="form-group {{ $errors->has('label2_fields') ? ' has-error' : '' }}">
        <div class="col-md-12">
            @include('partials.label2-field-definitions', [
                'name' => 'label2_fields',
                'value' => old('label2_fields', $setting->label2_fields),
                'customFields' => $customFields,
                'template' => $setting->label2_template,
            ])
            <x-form.error name="label2_fields"/>
        </div>
    </div>
</fieldset>

<fieldset name="label-settings">
    <x-form.legend help_text="{{ trans('admin/settings/general.labels_title_help') }}">
        {{ trans('admin/settings/general.labels_title') }}
    </x-form.legend>

    <div class="form-group{{ $errors->has('label2_title') ? ' has-error' : '' }}">
        <div class="col-md-3 text-right">
            <label for="label2_title" class="control-label">{{ trans('admin/settings/general.label2_title') }}</label>
        </div>
        <div class="col-md-7">
            <input class="form-control" name="label2_title" type="text" id="label2_title" value="{{ old('label2_title', $setting->label2_title) }}">
            <x-form.error name="label2_title" />
            <p class="help-block">{!! trans('admin/settings/general.label2_title_help') !!}</p>
            <p class="help-block">
                {!! trans('admin/settings/general.label2_title_help_phold') !!}.<br />
                {!! trans('admin/settings/general.help_asterisk_bold') !!}.<br />
                {!!
                    trans('admin/settings/general.help_blank_to_use', [
                        'setting_name' => trans('admin/settings/general.barcodes').' &gt; '.trans('admin/settings/general.qr_text'),
                    ])
                !!}
            </p>
        </div>
    </div>

    <div class="form-group{{ $errors->has('label2_asset_logo') ? ' has-error' : '' }}">
        <div class="col-md-7 col-md-offset-3">
            <label class="form-control" for="label2_asset_logo">
                <input type="checkbox" value="1" name="label2_asset_logo" id="label2_asset_logo" @checked(old('label2_asset_logo', $setting->label2_asset_logo))>
                {{ trans('admin/settings/general.label2_asset_logo') }}
            </label>
            <p class="help-block">
                {!! trans('admin/settings/general.label2_asset_logo_help', ['setting_name' => trans('admin/settings/general.brand').' &gt; '.trans('admin/settings/general.logo_labels.logo')]) !!}
            </p>
        </div>
    </div>

    <div class="form-group{{ $errors->has('label2_1d_type') ? ' has-error' : '' }}">
        <div class="col-md-3 text-right">
            <label for="label2_1d_type" class="control-label">{{ trans('admin/settings/general.label2_1d_type') }}</label>
        </div>
        <div class="col-md-7">
            @php
                $select1DValues = [
                    'C128'  => 'C128',
                    'C39'   => 'C39',
                    'EAN5'  => 'EAN5',
                    'EAN13' => 'EAN13',
                    'UPCA'  => 'UPCA',
                    'UPCE'  => 'UPCE',
                    'none'  => trans('admin/settings/general.none'),
                ];
            @endphp
            <x-input.select
                name="label2_1d_type"
                id="label2_1d_type"
                :options="$select1DValues"
                :selected="old('label2_1d_type', $setting->label2_1d_type)"
                class="col-md-4"
            />
            <x-form.error name="label2_1d_type" />
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

    <div class="form-group{{ $errors->has('label2_2d_type') ? ' has-error' : '' }}">
        <div class="col-md-3 text-right">
            <label for="label2_2d_type" class="control-label">{{ trans('admin/settings/general.label2_2d_type') }}</label>
        </div>
        <div class="col-md-7">
            @php
                $select2DValues = [
                    'QRCODE' => 'QRCODE',
                    'PDF417' => 'PDF417',
                    'DATAMATRIX' => 'DATAMATRIX',
                    'none' => trans('admin/settings/general.none'),
                ];
            @endphp
            <x-input.select
                name="label2_2d_type"
                id="label2_2d_type"
                :options="$select2DValues"
                :selected="old('label2_2d_type', $setting->label2_2d_type)"
                class="col-md-4"
            />
            <x-form.error name="label2_2d_type" />
            <p class="help-block">
                {{ trans('admin/settings/general.label2_2d_type_help', ['current' => $setting->barcode_type]) }}.
                {!! trans('admin/settings/general.help_default_will_use') !!}
            </p>
        </div>
    </div>

    <div class="form-group{{ $errors->has('label2_2d_prefix') ? ' has-error' : '' }}">
        <div class="col-md-3 text-right">
            <label for="label2_2d_prefix" class="control-label">{{ trans('admin/settings/general.label2_2d_prefix') }}</label>
        </div>
        <div class="col-md-7">
            <input class="form-control" name="label2_2d_prefix" type="text" id="label2_2d_prefix" value="{{ old('label2_2d_prefix', $setting->label2_2d_prefix) }}">
            <x-form.error name="label2_2d_prefix" />
            <p class="help-block">{!! trans('admin/settings/general.label2_2d_prefix_help') !!}</p>
        </div>
    </div>

    <div class="form-group{{ $errors->has('label2_2d_target') ? ' has-error' : '' }}">
        <div class="col-md-3 text-right">
            <label for="label2_2d_target" class="control-label">{{ trans('admin/settings/general.label2_2d_target') }}</label>
        </div>
        <div class="col-md-9">
            <x-input.select
                name="label2_2d_target"
                id="label2_2d_target"
                style="min-width:50%"
                :options="[
                    'hardware_id' => config('app.url').'/hardware/{id} ('.trans('admin/settings/general.default').')',
                    'ht_tag' => config('app.url').'/ht/{asset_tag}',
                    'location' => config('app.url').'/locations/{location_id}',
                    'plain_asset_id' => trans('admin/settings/general.asset_id'),
                    'plain_asset_tag' => trans('general.asset_tag'),
                    'plain_serial_number' => trans('general.serial_number'),
                    'plain_model_number' => trans('general.model_no'),
                    'plain_model_name' => trans('general.asset_model'),
                    'plain_manufacturer_name' => trans('general.manufacturer'),
                    'plain_location_name' => trans('general.location'),
                ]"
                :selected="old('label2_2d_target', $setting->label2_2d_target)"
                class="col-md-4"
            />
            <x-form.error name="label2_2d_target" />
            <p class="help-block">{{ trans('admin/settings/general.label2_2d_target_help') }}</p>
        </div>
    </div>

    <div class="form-group{{ $errors->has('label2_empty_row_count') ? ' has-error' : '' }}">
        <div class="col-md-3 text-right">
            <label for="label2_empty_row_count" class="control-label">{{ trans('admin/settings/general.empty_row_count') }}</label>
        </div>
        <div class="col-md-9 col-xl-2 col-lg-2">
            <input
                class="form-control"
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
            <x-form.error name="label2_empty_row_count" />
        </div>
    </div>
</fieldset>




@include('partials.bootstrap-table')


