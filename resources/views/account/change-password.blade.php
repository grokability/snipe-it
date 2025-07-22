@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.changepassword') }}
@stop

{{-- Account page content --}}
@section('content')


<div class="row">
    <div class="col-md-9">
    <form method="POST" action="{{ route('account.password.update') }}" accept-charset="UTF-8" class="form-horizontal" autocomplete="off">
    <!-- CSRF Token -->
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        <div class="box box-default">
            <div class="box-body">


    <!-- Old Password -->
    <div class="form-group {{ $errors->has('current_password') ? ' has-error' : '' }}">
        <label for="current_password" class="col-md-3 control-label"> {{ trans('general.current_password') }} </label>
        </label>
        <div class="col-md-5 required">
            <input class="form-control" type="password" name="current_password" id="current_password" required {{ (config('app.lock_passwords') ? ' disabled' : '') }}>
            {!! $errors->first('current_password', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
            @if (config('app.lock_passwords')===true)
                <p class="text-warning"><i class="fas fa-lock"></i> {{ trans('general.feature_disabled') }}</p>
            @endif
        </div>
    </div>

    <div class="form-group {{ $errors->has('password') ? ' has-error' : '' }}">
        <label for="password" class="col-md-3 control-label">{{ trans('general.new_password') }}</label>
        <div class="col-md-5 required">
            <input class="form-control" type="password" name="password" id="password" required {{ (config('app.lock_passwords') ? ' disabled' : '') }}>
            {!! $errors->first('password', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
            @if (config('app.lock_passwords')===true)
                <p class="text-warning"><i class="fas fa-lock"></i> {{ trans('general.feature_disabled') }}</p>
            @endif
        </div>
    </div>


    <div class="form-group {{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
        <label for="password_confirmation" class="col-md-3 control-label">{{ trans('general.new_password') }}</label>
        <div class="col-md-5 required">
            <input class="form-control" type="password" name="password_confirmation" id="password_confirmation"  {{ (config('app.lock_passwords') ? ' disabled' : '') }} aria-label="password_confirmation">
            {!! $errors->first('password_confirmation', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
            @if (config('app.lock_passwords')===true)
                <p class="text-warning"><i class="fas fa-lock"></i> {{ trans('general.feature_disabled') }}</p>
            @endif
        </div>
    </div>



            </div> <!-- .box-body -->
            <div class="box-footer text-right">
                <a class="btn btn-link" href="{{ URL::previous() }}">{{ trans('button.cancel') }}</a>
                <button type="submit" class="btn btn-primary"><x-icon type="checkmark" /> {{ trans('general.save') }}</button>
            </div>

        </div> <!-- .box-default -->
        </form>
    </div> <!-- .col-md-9 -->
</div> <!-- .row-->
@stop
