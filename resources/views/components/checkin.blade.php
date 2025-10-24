@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/components/general.checkin') }}
    @parent
@stop


@section('header_right')
    <a href="{{ URL::previous() }}" class="btn btn-primary pull-right">
        {{ trans('general.back') }}</a>
@stop

{{-- Page content --}}
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-7">
            <form class="form-horizontal" method="post" action="{{ route('components.checkin.store', [$component_assets->id, 'backto' => 'asset']) }}" autocomplete="off">
                {{csrf_field()}}

                <div class="box box-default">
                    <div class="box-header with-border">
                        <h2 class="box-title"> {{ $component->name }}</h2>
                    </div>
                    <div class="box-body">

                        <!-- Checked out to -->
                        <div class="form-group">
                            <label class="col-sm-2 control-label">{{ trans('general.checkin_from') }}</label>
                            <div class="col-md-6">
                                <p class="form-control-static">{{ $asset->present()->fullName }}</p>
                            </div>
                        </div>


                        <!-- Qty -->
                        <div class="form-group {{ $errors->has('checkin_qty') ? 'error' : '' }}">
                            <label for="checkin_qty" class="col-md-2 control-label">{{ trans('general.qty') }}</label>
                            <div class="col-md-3 text-right">
                                <input type="text" class="form-control" name="checkin_qty" aria-label="checkin_qty" value="{{ old('assigned_qty', $component_assets->assigned_qty) }}">
                            </div>
                            <div class="col-md-9 col-md-offset-2">
                            <p class="help-block">{{ trans('admin/components/general.checkin_limit', ['assigned_qty' => $component_assets->assigned_qty]) }}</p>
                            {!! $errors->first('checkin_qty', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i>
                            :message</span>') !!}
                            </div>
                        </div>

                        <!-- Note -->
                        <div class="form-group {{ $errors->has('note') ? 'error' : '' }}">
                            <label for="note" class="col-md-2 control-label">{{ trans('admin/hardware/form.notes') }}</label>
                            <div class="col-md-7">
                                <textarea class="col-md-6 form-control" id="note" name="note">{{ old('note', $component->note) }}</textarea>
                                {!! $errors->first('note', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>
                        <x-redirect_submit_options
                                index_route="components.index"
                                :button_label="trans('general.checkin')"
                                :options="[
                                'index' => trans('admin/hardware/form.redirect_to_all', ['type' => trans('general.components')]),
                                'item' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.component')]),
                               ]"
                        />
                    </div> <!-- /.box-->
            </form>
        </div> <!-- /.col-md-7-->
    </div>


@stop
