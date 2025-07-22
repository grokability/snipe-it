@extends('layouts/default')

{{-- Page title --}}
@section('title')
     {{ trans('admin/licenses/general.checkin') }}
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
        <div class="col-md-8">
            <form class="form-horizontal" method="post" action="{{ route('licenses.checkin.save', ['licenseId'=>$licenseSeat->id, 'backTo'=>$backto] ) }}" autocomplete="off">
                {{csrf_field()}}

                <div class="box box-default">
                    <div class="box-header with-border">
                        <h2 class="box-title"> {{ $licenseSeat->license->name }}</h2>
                    </div>
                    <div class="box-body">

            <!-- license name -->
            <div class="form-group">
                <label class="col-sm-3 control-label">{{ trans('admin/hardware/form.name') }}</label>
                <div class="col-md-8">
                    <p class="form-control-static">{{ $licenseSeat->license->name }}</p>
                </div>
            </div>

            <!-- Category -->
            <div class="form-group">
                <label class="col-sm-3 control-label">{{ trans('general.category') }}</label>
                <div class="col-md-9">
                    <p class="form-control-static">{{ $licenseSeat->license->category->name }}</p>
                </div>
            </div>

            <!-- Serial -->
            @can('viewKeys', $licenseSeat->license)
            <div class="form-group">
                <label class="col-sm-3 control-label">{{ trans('admin/licenses/form.license_key') }}
                    <i class="fa-regular fa-clipboard js-copy-link hidden-print" data-clipboard-target=".js-copy-key" aria-hidden="true" data-tooltip="true" data-placement="top" title="{{ trans('general.copy_to_clipboard') }}">
                        <span class="sr-only">{{ trans('general.copy_to_clipboard') }}</span>
                    </i>
                </label>
                <div class="col-md-8">
                    <p class="form-control-static">
                       <code style="white-space: pre-wrap"><span class="js-copy-key">{{ $licenseSeat->license->serial }}</span></code>
                    </p>
                </div>
            </div>
            @endcan

            <!-- Note -->
            <div class="form-group {{ $errors->has('notes') ? 'error' : '' }}">
                <label for="note" class="col-md-3 control-label">{{ trans('general.checkin_note') }}</label>
                <div class="col-md-8">
                    <textarea class="form-control" id="notes" name="notes" rows="5"></textarea>
                    {!! $errors->first('notes', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                </div>
            </div>
                        <x-redirect_submit_options
                                index_route="licenses.index"
                                :button_label="trans('general.checkin')"
                                :options="[
                                'index' => trans('admin/hardware/form.redirect_to_all', ['type' => trans('general.licenses')]),
                                'item' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.license')]),
                                'target' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.user')]),
                               ]"
                        />
                    </div> <!-- /.box-->
            </form>
        </div> <!-- /.col-md-7-->
    </div>


@stop
