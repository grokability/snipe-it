@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/settings/general.asset_tag_title') }}
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


    <form method="POST" action="{{ route('settings.asset_tags.save') }}" accept-charset="UTF-8" autocomplete="off" class="form-horizontal" role="form">
    <!-- CSRF Token -->
    {{csrf_field()}}

    <div class="row">
        <div class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">


            <div class="panel box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">
                        <x-icon type="asset-tags"/> {{ trans('general.asset_tags') }}
                    </h2>
                </div>
                <div class="box-body">


                    <div class="col-md-11 col-md-offset-1">

                        <!-- auto ids -->
                        <div class="form-group">
                            <div class="col-md-5">
                                <strong>{{  trans('admin/settings/general.auto_increment_assets') }}</strong>
                            </div>
                            <div class="col-md-7">
                                <label class="form-control">
                                    <input type="checkbox" name="auto_increment_assets" value="1" @checked(old('auto_increment_assets', $setting->auto_increment_assets)) aria-label="auto_increment_assets">
                                    {{ trans('admin/settings/general.enabled') }}
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-5">
                                <label for="next_auto_tag_base">{{ trans('admin/settings/general.next_auto_tag_base') }}</label>
                            </div>
                            <div class="col-md-7">
                                <input class="form-control" style="width: 150px;" aria-label="next_auto_tag_base" name="next_auto_tag_base" type="text" value="{{ old('next_auto_tag_base', $setting->next_auto_tag_base) }}" id="next_auto_tag_base">
                                {!! $errors->first('next_auto_tag_base', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                            </div>
                        </div>


                        <!-- auto prefix -->
                        <div class="form-group {{ $errors->has('auto_increment_prefix') ? 'error' : '' }}">
                            <div class="col-md-5">
                                <label for="auto_increment_prefix">{{ trans('admin/settings/general.auto_increment_prefix') }}</label>
                            </div>
                            <div class="col-md-7">
                                @if ($setting->auto_increment_assets == 1)
                                    <input class="form-control" style="width: 150px;" aria-label="auto_increment_prefix" name="auto_increment_prefix" type="text" id="auto_increment_prefix" value="{{ old('auto_increment_prefix', $setting->auto_increment_prefix) }}">
                                    {!! $errors->first('auto_increment_prefix', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                @else
                                    <input class="form-control" disabled="disabled" style="width: 150px;" aria-label="auto_increment_prefix" name="auto_increment_prefix" type="text" id="auto_increment_prefix" value="{{ old('auto_increment_prefix', $setting->auto_increment_prefix) }}">
                                @endif
                            </div>
                        </div>

                        <!-- auto zerofill -->
                        <div class="form-group {{ $errors->has('zerofill_count') ? 'error' : '' }}">
                            <div class="col-md-5">
                                <label for="zerofill_count">{{ trans('admin/settings/general.zerofill_count') }}</label>
                            </div>
                            <div class="col-md-7">
                                <input class="form-control" style="width: 150px;" aria-label="zerofill_count" name="zerofill_count" type="text" value="{{ old('zerofill_count', $setting->zerofill_count) }}" id="zerofill_count">
                                {!! $errors->first('zerofill_count', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                            </div>
                        </div>

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
