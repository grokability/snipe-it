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
        <div class="col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">


            <div class="panel box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">
                        <x-icon type="asset-tags"/> {{ trans('general.asset_tags') }}
                    </h2>
                </div>
                <div class="box-body">


                    <div class="col-md-12">

                        <!-- auto ids -->
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-3">
                                <label class="form-control">
                                    <input type="checkbox" id="auto_increment_assets" name="auto_increment_assets" value="1" @checked(old('auto_increment_assets', $setting->auto_increment_assets)) aria-label="auto_increment_assets">
                                    {{  trans('admin/settings/general.auto_increment_assets') }}
                                </label>
                            </div>
                        </div>

                        <div class="form-group">

                            <label for="next_auto_tag_base" class="col-md-3 control-label">{{ trans('admin/settings/general.next_auto_tag_base') }}</label>

                            <div class="col-md-8">
                                <input class="form-control" style="width: 200px;" aria-label="next_auto_tag_base" name="next_auto_tag_base" type="text" value="{{ old('next_auto_tag_base', $setting->next_auto_tag_base) }}" id="next_auto_tag_base">
                                {!! $errors->first('next_auto_tag_base', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                            </div>
                        </div>


                        <!-- auto prefix -->
                        <div class="form-group {{ $errors->has('auto_increment_prefix') ? 'error' : '' }}">

                            <label for="auto_increment_prefix" class="col-md-3 control-label">{{ trans('admin/settings/general.auto_increment_prefix') }}</label>

                            <div class="col-md-8">
                                <input class="form-control" disabled maxlength="100" style="width: 200px;" aria-label="auto_increment_prefix" name="auto_increment_prefix" type="text" id="auto_increment_prefix" value="{{ old('auto_increment_prefix', $setting->auto_increment_prefix) }}">
                                {!! $errors->first('auto_increment_prefix', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}

                            </div>
                        </div>

                        <!-- auto zerofill -->
                        <div class="form-group {{ $errors->has('zerofill_count') ? 'error' : '' }}">

                            <label for="zerofill_count" class="col-md-3 control-label">{{ trans('admin/settings/general.zerofill_count') }}</label>

                            <div class="col-md-7">
                                <input class="form-control" maxlength="100" style="width: 200px;" aria-label="zerofill_count" name="zerofill_count" type="text" value="{{ old('zerofill_count', $setting->zerofill_count) }}" id="zerofill_count">
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

    @section('moar_scripts')
        <script>
        if ($("#auto_increment_assets").is(':checked')) {
            // Hide here instead of fadeout on pageload to prevent what looks like Flash Of Unstyled Content (FOUC)
            $("#auto_increment_prefix").prop('disabled', false);
        }

        $("#auto_increment_assets").change(function () {

            if ($("#auto_increment_assets").is(':checked')) {
                $("#auto_increment_prefix").prop('disabled', false);
            } else {
                $("#auto_increment_prefix").prop('disabled', true);
            }
        });
        </script>
    @endsection


@stop
