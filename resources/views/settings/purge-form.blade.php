@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/settings/general.purge_deleted') }}
    @parent
@stop

@section('header_right')
    <a href="{{ route('settings.index') }}" class="btn btn-default"> {{ trans('general.back') }}</a>
@stop


{{-- Page content --}}
@section('content')

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="box box-solid box-danger">
                <div class="box-header with-border">
                    <h2 class="box-title">
                        <x-icon type="warning"/>
                        {{ trans('admin/settings/general.purge') }}</h2>
                </div>
                <form method="POST" action="{{ route('settings.purge.save') }}" accept-charset="UTF-8" autocomplete="off" class="form-horizontal" role="form">
            <!-- CSRF Token -->
                {{csrf_field()}}
                <div class="box-body">
                    <p>{{ trans('admin/settings/general.confirm_purge_help') }}</p>
                    <div class="col-md-3{{ $errors->has('confirm_purge') ? 'error' : '' }}">
                        <label for="confirm_purge">{{ trans('admin/settings/general.confirm_purge') }}</label>
                    </div>
                    <div class="col-md-9{{ $errors->has('confirm_purge') ? 'error' : '' }}">
                        @if (config('app.lock_passwords')===true)
                            <input class="form-control" disabled="true" name="confirm_purge" type="text" id="confirm_purge" value="{{ old('confirm_purge') }}">
                        @else
                            <input class="form-control" name="confirm_purge" type="text" id="confirm_purge" value="{{ old('confirm_purge') }}">
                        @endif

                        @if (config('app.lock_passwords')===true)
                            <p class="text-warning"><i class="fas fa-lock"></i> {{ trans('general.feature_disabled') }}</p>
                        @endif
                    </div>
                </div>
                <div class="box-footer text-right">
                    <button type="submit" class="btn btn-danger" {{ (config('app.lock_passwords')===true) ? ' disabled' : '' }}>{{ trans('admin/settings/general.purge') }}</button>
                </div> <!--/box-footer-->
                </form>
            </div> <!--/.box-solid-->
        </div><!-- /.col-md-8-->
    </div><!--/.row-->

@stop
