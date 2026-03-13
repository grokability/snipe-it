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


<div class="row">
  <!-- left column -->
  <div class="col-md-7">
    <div class="box box-default">
      <div class="box-body">
        <form class="form-horizontal" method="post" action="" autocomplete="off">
          {{ csrf_field() }}
          @include ('partials.forms.edit.user-select', ['translated_name' => trans('general.select_user'), 'fieldname' => 'user_id', 'required'=> 'true'])

          <!-- Checkout/Checkin Date -->
              <div class="form-group {{ $errors->has('checkout_at') ? 'error' : '' }}">
                  <label for="checkout_at" class="col-md-3 control-label">
                      {{ trans('admin/hardware/form.checkout_date') }}
                  </label>
                  <div class="col-md-8">
                      <div class="input-group date col-md-5" data-provide="datepicker" data-date-format="yyyy-mm-dd" data-date-end-date="0d" data-date-clear-btn="true">
                          <input type="text" class="form-control" placeholder="{{ trans('general.select_date') }}" name="checkout_at" id="checkout_at" value="{{ old('checkout_at') }}">
                          <span class="input-group-addon"><x-icon type="calendar" /></span>
                      </div>
                      {!! $errors->first('checkout_at', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
                  </div>
              </div>

              <!-- Expected Checkin Date -->
              <div class="form-group {{ $errors->has('expected_checkin') ? 'error' : '' }}">
                  <label for="expected_checkin" class="col-md-3 control-label">
                      {{ trans('admin/hardware/form.expected_checkin') }}
                  </label>
                  <div class="col-md-8">
                      <div class="input-group date col-md-5" data-provide="datepicker" data-date-format="yyyy-mm-dd" data-date-start-date="0d">
                          <input type="text" class="form-control" placeholder="{{ trans('general.select_date') }}" name="expected_checkin" id="expected_checkin" value="{{ old('expected_checkin') }}">
                          <span class="input-group-addon"><x-icon type="calendar" /></span>
                      </div>
                      {!! $errors->first('expected_checkin', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
                  </div>
              </div>


          <!-- Note -->
          <div class="form-group {{ $errors->has('note') ? 'error' : '' }}">
              <label for="note" class="col-md-3 control-label">
                  {{ trans('general.notes') }}
              </label>
            <div class="col-md-8">
              <textarea class="col-md-6 form-control" id="note" name="note">{{ old('note') }}</textarea>
              {!! $errors->first('note', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
            </div>
          </div>

      </div> <!--./box-body-->
      <div class="box-footer">
        <a class="btn btn-link" href="{{ route('kits.index') }}"> {{ trans('button.cancel') }}</a>
        <button type="submit" class="btn btn-success pull-right"><x-icon type="checkmark" /> {{ trans('general.checkout') }}</button>
      </div>
    </div>
      </form>
  </div> <!--/.col-md-7-->

  <!-- right column -->
  <div class="col-md-5" id="current_assets_box" style="display:none;">
    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title">{{ trans('admin/users/general.current_assets') }}</h3>
      </div>
      <div class="box-body">
        <div id="current_assets_content">
        </div>
      </div>
    </div>
  </div>
</div>
@stop

@section('moar_scripts')
@include('partials/assets-assigned')

@stop

@section('notifications')
@parent
<!-- included content -->
@stop
