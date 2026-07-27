@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/settings/general.asset_tag_title') }}
    @parent
@stop

{{-- Page content --}}
@section('content')

    <style>
        .checkbox label {
            padding-right: 40px;
        }
    </style>

    <x-container class="col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
        <x-form route="{{ route('settings.asset_tags.save') }}">
            <x-box>
                <x-slot:header>
                    <x-icon type="asset-tags"/> {{ trans('general.asset_tags') }}
                </x-slot:header>

                <x-form.checkbox-row
                    name="auto_increment_assets"
                    :label="trans('admin/settings/general.auto_increment_assets')"
                    :item="$setting"
                    data-toggle="disable-when-unchecked"
                    data-disable-target="#auto_increment_prefix"
                />

                <x-form.row
                    :label="trans('admin/settings/general.next_auto_tag_base')"
                    :item="$setting"
                    name="next_auto_tag_base"
                />

                <x-form.row
                    :label="trans('admin/settings/general.auto_increment_prefix')"
                    :item="$setting"
                    name="auto_increment_prefix"
                >
                    <x-slot:input>
                        <input
                            class="form-control"
                            id="auto_increment_prefix"
                            name="auto_increment_prefix"
                            type="text"
                            maxlength="100"
                            aria-label="auto_increment_prefix"
                            value="{{ old('auto_increment_prefix', $setting->auto_increment_prefix) }}"
                            @disabled(! old('auto_increment_assets', $setting->auto_increment_assets))
                        />
                    </x-slot:input>
                </x-form.row>

                <x-form.row
                    :label="trans('admin/settings/general.zerofill_count')"
                    :item="$setting"
                    name="zerofill_count"
                    type="number"
                    input_div_class="col-md-2"
                    min="1"
                    max="99999"
                />
            </x-box>
        </x-form>
    </x-container>

@stop
