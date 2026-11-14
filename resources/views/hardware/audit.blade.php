@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.audit') }}
    @parent
@stop

{{-- Page content --}}
@section('content')

    <style>
        .input-group {
            padding-left: 0px !important;
        }
    </style>

    <x-container class="col-md-8 col-md-offset-2">
        <x-form :route="route('asset.audit.store', $asset)">
            <x-box>
                <x-slot:header>
                    {{ trans('admin/hardware/form.tag') }} {{ $asset->asset_tag }}
                </x-slot:header>

                {{-- Model (read-only with fallback UI when the model
                     reference is broken). --}}
                <x-form.row :label="trans('admin/hardware/form.model')" name="model_display" input_div_class="col-md-8">
                    <x-slot:input>
                        <p class="form-control-static">
                            @if (($asset->model) && ($asset->model->name))
                                {{ $asset->model->name }}
                            @else
                                <span class="text-danger text-bold">
                                    <x-icon type="warning" />
                                    {{ trans('admin/hardware/general.model_invalid') }}
                                </span>
                                {{ trans('admin/hardware/general.model_invalid_fix') }}
                                <a href="{{ route('hardware.edit', $asset->id) }}">
                                    <strong>{{ trans('admin/hardware/general.edit') }}</strong>
                                </a>
                            @endif
                        </p>
                    </x-slot:input>
                </x-form.row>

                {{-- Asset name (read-only; only shown when set). --}}
                @if ($asset->name)
                    <x-form.row :label="trans('general.name')" name="name_display" input_div_class="col-md-8">
                        <x-slot:input>
                            <p class="form-control-static">{{ $asset->name }}</p>
                        </x-slot:input>
                    </x-form.row>
                @endif

                <x-input.location-select
                    :label="trans('general.location')"
                    name="location_id"
                    :selected="old('location_id')"
                    :companyId="$asset->company_id"
                />

                {{-- Update location + help block (kept hand-rolled so the
                     help text sits inside the input column alongside the
                     checkbox, matching pre-existing layout). --}}
                <div class="form-group">
                    <div class="col-md-8 col-md-offset-3">
                        <label class="form-control">
                            <input type="checkbox" value="1" name="update_location" {{ old('update_location') == '1' ? ' checked="checked"' : '' }}>
                            {{ trans('admin/hardware/form.asset_location') }}
                        </label>
                        <p class="help-block">{!! trans('help.audit_help') !!}</p>
                    </div>
                </div>

                {{-- Last audit (read-only). --}}
                <x-form.row :label="trans('general.last_audit')" name="last_audit_display" input_div_class="col-md-8">
                    <x-slot:input>
                        <p class="form-control-static">
                            @if ($asset->last_audit_date)
                                {{ Helper::getFormattedDateObject($asset->last_audit_date, 'datetime', false) }}
                            @else
                                {{ trans('admin/settings/general.none') }}
                            @endif
                        </p>
                    </x-slot:input>
                </x-form.row>

                {{-- Next audit date --}}
                <x-form.row
                    :label="trans('general.next_audit_date')"
                    name="next_audit_date"
                    input_div_class="col-md-8"
                >
                    <x-slot:input>
                        <x-input.datepicker
                            id="next_audit_date"
                            name="next_audit_date"
                            :value="old('next_audit_date', $next_audit_date)"
                            :placeholder="trans('general.next_audit_date')"
                            col_size_class="col-md-5"
                        />
                        <x-form.error name="next_audit_date" />
                        <p class="help-block">{!! trans('general.next_audit_date_help') !!}</p>
                    </x-slot:input>
                </x-form.row>

                <x-form.row
                    :label="trans('general.notes')"
                    name="note"
                    type="textarea"
                    :item="$asset"
                    input_div_class="col-md-8"
                />

                {{-- Audit image --}}
                <x-input.image-upload fieldname="file[0]" :helpText="trans('general.audit_images_help')" />

                {{-- Custom fields --}}
                @include('models/custom_fields_form', [
                    'fieldset' => $asset->custom_fieldset,
                        'defaultValues' => \App\Models\CustomFieldDefault::valuesFor($asset),
                    'show_custom_fields_type' => 'audit',
                ])

                <x-slot:customfooter>
                    <x-redirect_submit_options
                        index_route="hardware.index"
                        :button_label="trans('general.audit')"
                        :disabled_select="! $asset->model"
                        :options="[
                            'index' => trans('admin/hardware/form.redirect_to_all', ['type' => trans('general.assets')]),
                            'item' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.asset')]),
                            'other_redirect' => trans('general.audit_due'),
                        ]"
                    />
                </x-slot:customfooter>

            </x-box>
        </x-form>
    </x-container>

@stop
