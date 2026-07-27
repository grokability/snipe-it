@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/settings/general.purge_deleted') }}
    @parent
@stop

{{-- Page content --}}
@section('content')

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="box box-solid box-danger">
                <div class="box-header with-border">
                    <h2 class="box-title">
                        <x-icon type="warning"/>
                        {{ trans('admin/settings/general.purge') }}
                    </h2>
                </div>

                <form method="POST" action="{{ route('settings.purge.save') }}" accept-charset="UTF-8" autocomplete="off" class="form-horizontal" role="form">
                    {{ csrf_field() }}

                    <div class="box-body">
                        <p>{{ trans('admin/settings/general.confirm_purge_help') }}</p>

                        <x-form.row
                            name="confirm_purge"
                            :label="trans('admin/settings/general.confirm_purge')"
                        >
                            <x-slot:input>
                                <x-input.text
                                    name="confirm_purge"
                                    :value="old('confirm_purge')"
                                    :disabled="config('app.lock_passwords')"
                                />
                                <x-demo-lock>{{ trans('general.feature_disabled') }}</x-demo-lock>
                            </x-slot:input>
                        </x-form.row>
                    </div>

                    <div class="box-footer text-right">
                        <button type="submit" class="btn btn-danger" @disabled(config('app.lock_passwords'))>
                            {{ trans('admin/settings/general.purge') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@stop
