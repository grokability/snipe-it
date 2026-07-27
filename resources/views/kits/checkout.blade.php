@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/kits/general.checkout') }}
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

            <x-form id="checkout_form" route="{{ url()->current() }}">

                <x-box>

                    <x-input.user-select
                        :label="trans('general.select_user')"
                        name="user_id"
                        :selected="old('user_id')"
                        required
                    />

                    <x-form.row
                        :label="trans('admin/hardware/form.checkout_date')"
                        name="checkout_at"
                        type="datetimepicker"
                        input_div_class="col-md-4"
                    />

                    <x-form.row
                        :label="trans('admin/hardware/form.expected_checkin')"
                        name="expected_checkin"
                        type="datetimepicker"
                        :default_now="false"
                        input_div_class="col-md-4"
                    />

                    <x-form.row
                        :label="trans('general.notes')"
                        name="note"
                    >
                        <x-slot:input>
                            <textarea class="form-control" id="note" name="note">{{ old('note') }}</textarea>
                        </x-slot:input>
                    </x-form.row>

                    <x-slot:customfooter>
                        <div class="box-footer">
                            <a class="btn btn-link" href="{{ route('kits.index') }}">{{ trans('button.cancel') }}</a>
                            <button type="submit" class="btn btn-success pull-right"><x-icon type="checkmark"/> {{ trans('general.checkout') }}</button>
                        </div>
                    </x-slot:customfooter>

                </x-box>

            </x-form>

        </x-page-column>

        <x-page-column class="col-md-5">
            <livewire:checkout-target-panel type="assets" />
        </x-page-column>

    </x-container>
@stop

@section('notifications')
    @parent
@stop
