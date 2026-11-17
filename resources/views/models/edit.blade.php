@extends('layouts/default')

{{-- Page title --}}
@section('title')
    @if ($item->id)
        {{ trans('admin/models/table.update') }}
    @else
        {{ trans('admin/models/table.create') }}
    @endif
    @parent
@stop

{{-- Page content --}}
@section('content')

    <x-container class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-sm-12 col-sm-offset-0">

        <x-form :$item route="{{ ($item->id) ? route('models.update', ['model' => $item->id]) : route('models.store') }}">

            <x-box top_submit>
                @if ($item->id)
                    <x-slot:header>{{ $item->name }}</x-slot:header>
                @endif

                <x-form.row
                    :label="trans('admin/models/table.name')"
                    :$item
                    name="name"
                    required
                />

                <x-input.category-select
                    :label="trans('admin/categories/general.category_name')"
                    name="category_id"
                    :selected="old('category_id', $item->category_id)"
                    required
                    categoryType="asset"
                />

                <x-input.manufacturer-select
                    :label="trans('general.manufacturer')"
                    name="manufacturer_id"
                    :selected="old('manufacturer_id', $item->manufacturer_id)"
                />

                <x-form.row
                    :label="trans('general.model_no')"
                    :$item
                    name="model_number"
                    input_div_class="col-md-7"
                />

                <x-form.row
                    :label="trans('admin/models/general.mfg_marketing_name')"
                    :$item
                    name="mfg_marketing_name"
                    input_div_class="col-md-7"
                />

                <x-form.row
                    :label="trans('admin/models/general.mfg_release_date')"
                    :$item
                    name="mfg_release_date"
                    type="datepicker"
                    input_div_class="col-md-4"
                />

                <x-form.row
                    :label="trans('admin/models/general.mfg_discontinued_date')"
                    :$item
                    name="mfg_discontinued_date"
                    type="datepicker"
                    input_div_class="col-md-4"
                />

                <x-form.row
                    :label="trans('general.depreciation')"
                    name="depreciation_id"
                    input_div_class="col-md-7"
                >
                    <x-slot:input>
                        <x-input.select
                            name="depreciation_id"
                            id="depreciation_id"
                            :options="$depreciation_list"
                            :selected="old('depreciation_id', $item->depreciation_id)"
                            style="width:350px;"
                            aria-label="depreciation_id"
                        />
                    </x-slot:input>
                </x-form.row>

                <x-input.minimum-quantity :item="$item" />

                <x-form.checkbox-row
                    name="require_serial"
                    :label="trans('admin/hardware/general.require_serial')"
                    :item="$item"
                    :info_tooltip_text="trans('admin/hardware/general.require_serial_help')"
                />

                <x-form.row
                    :label="trans('general.eol')"
                    name="eol"
                    input_div_class="col-md-3 col-sm-4 col-xs-7"
                >
                    <x-slot:input>
                        <div class="input-group">
                            <input class="form-control" type="text" name="eol" id="eol" value="{{ old('eol', $item->eol ?? '') }}" aria-label="eol" />
                            <span class="input-group-addon">{{ trans('general.months') }}</span>
                        </div>
                    </x-slot:input>
                </x-form.row>

                {{-- Custom Fieldset --}}
                {{-- If $item->id is null we are cloning the model and we need the $model_id variable --}}
                @livewire('custom-field-set-default-values-for-model', ['model_id' => $item->id ?? $model_id ?? null])

                <x-form.row
                    :label="trans('general.notes')"
                    :$item
                    name="notes"
                    type="textarea"
                    input_div_class="col-md-7 col-sm-12"
                />

                <x-form.checkbox-row
                    name="requestable"
                    :label="trans('admin/models/general.requestable')"
                    :item="$item"
                />

                <x-input.image-upload :item="$item" :imagePath="app('models_upload_path')" :clonedModel="$cloned_model ?? null" />

            </x-box>

        </x-form>

    </x-container>

@stop
