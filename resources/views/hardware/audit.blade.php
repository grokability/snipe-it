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

    <div class="row">
        <!-- left column -->
        <div class="col-md-8 col-md-offset-2">
            <div class="box box-default">

                <form method="POST" action="{{ route('asset.audit.store', $asset) }}" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data">

                    <div class="box-header with-border">
                        <h2 class="box-title"> {{ trans('admin/hardware/form.tag') }} {{ $asset->asset_tag }}</h2>
                    </div>
                    <div class="box-body">
                    {{csrf_field()}}

                        <!-- Asset model -->
                            <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                <label class="col-sm-3 control-label">
                                    {{ trans('admin/hardware/form.model') }}
                                </label>
                                <div class="col-md-8">
                                    <p class="form-control-static">
                                        @if (($asset->model) && ($asset->model->name))
                                            {{ $asset->model->name }}
                                        @else
                                            <span class="text-danger text-bold">
                                              <x-icon type="warning" />
                                              {{ trans('admin/hardware/general.model_invalid')}}
                                            </span>
                                            {{ trans('admin/hardware/general.model_invalid_fix')}}
                                            <a href="{{ route('hardware.edit', $asset->id) }}">
                                                <strong>{{ trans('admin/hardware/general.edit') }}</strong>
                                            </a>
                                        @endif
                                    </p>
                                </div>
                            </div>


                    <!-- Asset Name -->

                        @if ($asset->name)
                        <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                            <label for="name" class="col-sm-3 control-label">
                                {{ trans('general.name') }}
                            </label>
                            <div class="col-md-8">
                                <p class="form-control-static">{{ $asset->name }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Locations -->
                    @include ('partials.forms.edit.location-select', ['translated_name' => trans('general.location'), 'fieldname' => 'location_id'])

                    <!-- Update location -->
                        <div class="form-group">

                            <div class="col-md-8 col-md-offset-3">
                                <label class="form-control">
                                    <input type="checkbox" value="1" name="update_location" {{ old('update_location') == '1' ? ' checked="checked"' : '' }}> {{ trans('admin/hardware/form.asset_location') }}
                                </label>
                                <p class="help-block">{!! trans('help.audit_help') !!}</p>
                            </div>

                        </div>


                        <!-- Show last audit date -->
                        <div class="form-group">
                            <label class="control-label col-md-3">
                                {{ trans('general.last_audit') }}
                            </label>
                            <div class="col-md-8">

                                <p class="form-control-static">
                                    @if ($asset->last_audit_date)
                                        {{ Helper::getFormattedDateObject($asset->last_audit_date, 'datetime', false) }}
                                    @else
                                        {{ trans('admin/settings/general.none') }}
                                    @endif
                                </p>
                            </div>
                        </div>


                        <!-- Next Audit -->
                        <div class="form-group{{ $errors->has('next_audit_date') ? ' has-error' : '' }}">
                            <label for="next_audit_date" class="col-sm-3 control-label">
                                {{ trans('general.next_audit_date') }}
                            </label>
                            <div class="col-md-8">
                                <div class="input-group date col-md-5" data-provide="datepicker" data-date-format="yyyy-mm-dd" data-date-clear-btn="true">
                                    <input type="text" class="form-control" placeholder="{{ trans('general.next_audit_date') }}" name="next_audit_date" id="next_audit_date" value="{{ old('next_audit_date', $next_audit_date) }}">
                                    <span class="input-group-addon"><x-icon type="calendar" /></span>
                                </div>
                                {!! $errors->first('next_audit_date', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                 <p class="help-block">{!! trans('general.next_audit_date_help') !!}</p>
                            </div>
                        </div>


                        <!-- Note -->
                        <div class="form-group{{ $errors->has('note') ? ' has-error' : '' }}">
                            <label for="note" class="col-sm-3 control-label">
                                {{ trans('general.notes') }}
                            </label>
                            <div class="col-md-8">
                                <textarea class="col-md-6 form-control" id="note" name="note">{{ old('note', $asset->note) }}</textarea>
                                {!! $errors->first('note', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>

                        <!-- Audit Image -->
                        @include ('partials.forms.edit.image-upload', ['help_text' => trans('general.audit_images_help')])

                        <!-- Custom fields -->
                        @include("models/custom_fields_form", [
                                'model' => $asset->model,
                                'show_custom_fields_type' => 'audit'
                        ])


                    </div> <!--/.box-body-->

                    <x-redirect_submit_options
                            index_route="hardware.index"
                            :button_label="trans('general.audit')"
                            :disabled_select="!$asset->model"
                            :options="[
                                'index' => trans('admin/hardware/form.redirect_to_all', ['type' => trans('general.assets')]),
                                'item' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.asset')]),
                                'other_redirect' => trans('general.audit_due')
                               ]"
                    />

                </form>
            </div>
        </div> <!--/.col-md-7-->
    </div>

@stop
