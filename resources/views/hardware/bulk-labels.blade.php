@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.bulk_labels') }}
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
        <x-page-column class="col-md-7">

            <x-form id="checkout_form" route="{{ url()->current() }}" data-disable-empty-on-submit data-autofocus-select2-search>

                <x-box header="{{ trans('general.bulk_labels') }}">

                    @include ('partials.forms.edit.asset-select', [
                        'translated_name' => trans('general.assets'),
                        'fieldname' => 'selected_assets[]',
                        'multiple' => true,
                        'required' => true,
                        'select_id' => 'assigned_assets_select',
                        'asset_selector_div_id' => 'assets_to_checkout_div',
                        'asset_ids' => old('selected_assets'),
                    ])


                    <x-form.row
                        :label="trans('general.labels_offset')"
                        name="labels_offset"
                    >
                        <x-slot:input>
                            <input
                                class="form-control"
                                name="labels_offset"
                                id="labels_offset"
                                type="number"
                                :title="{{trans('general.label_print_offset')}}"
                                step="1"
                                min="0"
                                input_div_class="col-md-4"
                            />
                        </x-slot:input>
                    </x-form.row>


                    <x-slot:customfooter>
                        <div class="box-footer">
                            <a class="btn btn-link" href="{{ URL::previous() }}">{{ trans('button.cancel') }}</a>
                            <button type="submit" class="btn btn-primary pull-right"><x-icon type="checkmark"/> {{ trans('general.generate_labels') }}</button>
                        </div>
                    </x-slot:customfooter>

                </x-box>

            </x-form>

        </x-page-column>


    </x-container>
@stop

