@extends('layouts/default')

{{-- Page title --}}
@section('title')
     {{ trans('admin/hardware/general.bulk_checkout') }}
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
      <div class="box-header with-border">
        <h2 class="box-title"> {{ trans('admin/hardware/form.tag') }} </h2>
      </div>
      <div class="box-body">
        <form class="form-horizontal" method="post" action="" autocomplete="off">
          {{ csrf_field() }}

            @if ($removed_assets->isNotEmpty())
                <div class="box box-solid box-warning">
                    <div class="box-header with-border">
                        <span class="box-title col-xs-12">Warning</span>
                    </div>
                    <div class="box-body">
                        <p>{{ trans('general.assigned_assets_removed') }}</p>
                        <ul>
                            @foreach($removed_assets as $removed_asset)
                                <li>
                                    <a href="{{ route('hardware.show', $removed_asset->id) }}">
                                        {{ $removed_asset->present()->fullName }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @include ('partials.forms.edit.asset-select', [
           'translated_name' => trans('general.assets'),
           'fieldname' => 'selected_assets[]',
           'multiple' => true,
           'required' => true,
           'asset_status_type' => 'RTD',
           'select_id' => 'assigned_assets_select',
           'asset_selector_div_id' => 'assets_to_checkout_div',
           'asset_ids' => old('selected_assets')
         ])


            <!-- Status -->
            <div class="form-group {{ $errors->has('status_id') ? 'error' : '' }}">
                <label for="status_id" class="col-md-3 control-label">
                    {{ trans('admin/hardware/form.status') }}
                </label>
                <div class="col-md-7 required">
                    <x-input.select
                            name="status_id"
                            :options="$statusLabel_list"
                            :selected="old('status_id', $status_id ?? null)"
                            style="width: 100%;"
                            aria-label="status_id"
                    />
                    {!! $errors->first('status_id', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                </div>
            </div>


            <!-- Checkout selector -->


          @include ('partials.forms.checkout-selector', ['user_select' => 'true','asset_select' => 'true', 'location_select' => 'true'])
          @include ('partials.forms.edit.user-select', ['translated_name' => trans('general.user'), 'fieldname' => 'assigned_user', 'style' => session('checkout_to_type') == 'user' ? '' : 'display: none;'])
            <!-- We have to pass unselect here so that we don't default to the asset that's being checked out. We want that asset to be pre-selected everywhere else. -->
          @include ('partials.forms.edit.asset-select', ['translated_name' => trans('general.asset'), 'asset_selector_div_id' => 'assigned_asset', 'fieldname' => 'assigned_asset', 'unselect' => 'true', 'style' => session('checkout_to_type') == 'asset' ? '' : 'display: none;'])
          @include ('partials.forms.edit.location-select', ['translated_name' => trans('general.location'), 'fieldname' => 'assigned_location', 'style' => session('checkout_to_type') == 'location' ? '' : 'display: none;'])

          <!-- Checkout/Checkin Date -->
              <div class="form-group {{ $errors->has('checkout_at') ? 'error' : '' }}">
                  <label for="checkout_at" class="col-sm-3 control-label">
                      {{ trans('admin/hardware/form.checkout_date') }}
                  </label>
                  <div class="col-md-8">
                      <div class="input-group date col-md-5" data-provide="datepicker" data-date-format="yyyy-mm-dd" data-date-end-date="0d" data-date-clear-btn="true">
                          <input type="text" class="form-control" placeholder="{{ trans('general.select_date') }}" name="checkout_at" id="checkout_at" value="{{ old('checkout_at') }}">
                          <span class="input-group-addon"><x-icon type="calendar" /></span>
                      </div>
                      {!! $errors->first('checkout_at', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                  </div>
              </div>

              <!-- Expected Checkin Date -->
              <div class="form-group {{ $errors->has('expected_checkin') ? 'error' : '' }}">
                  <label for="expected_checkin" class="col-sm-3 control-label">
                      {{ trans('admin/hardware/form.expected_checkin') }}
                  </label>
                  <div class="col-md-8">
                      <div class="input-group date col-md-5" data-provide="datepicker" data-date-format="yyyy-mm-dd" data-date-start-date="0d" data-date-clear-btn="true">
                          <input type="text" class="form-control" placeholder="{{ trans('general.select_date') }}" name="expected_checkin" id="expected_checkin" value="{{ old('expected_checkin') }}">
                          <span class="input-group-addon"><x-icon type="calendar" /></span>
                      </div>
                      {!! $errors->first('expected_checkin', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                  </div>
              </div>


          <!-- Note -->
          <div class="form-group {{ $errors->has('note') ? 'error' : '' }}">
              <label for="note" class="col-sm-3 control-label">
                  {{ trans('general.notes') }}
              </label>
            <div class="col-md-8">
              <textarea class="col-md-6 form-control" id="note" name="note">{{ old('note') }}</textarea>
              {!! $errors->first('note', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
            </div>
          </div>



      </div> <!--./box-body-->
      <div class="box-footer">
        <a class="btn btn-link" href="{{ URL::previous() }}"> {{ trans('button.cancel') }}</a>
        <button type="submit" class="btn btn-primary pull-right"><x-icon type="checkmark" /> {{ trans('general.checkout') }}</button>
      </div>
    </div>
      </form>
  </div> <!--/.col-md-7-->

  <!-- right column -->
  <div class="col-md-5" id="current_assets_box" style="display:none;">
    <div class="box box-primary">
      <div class="box-header with-border">
        <h2 class="box-title">{{ trans('admin/users/general.current_assets') }}</h2>
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
<script nonce="{{ csrf_token() }}">
    $(function () {
        //if there's already a user selected, make sure their checked-out assets show up
        // (if there isn't one, it won't do anything)
        $('#assigned_user').change();

        // Add the disabled attribute to empty inputs on submit to handle the case where someone does not pick a status ID
        // and the form is submitted with an empty status ID which will fail validation via the form request
        $("form").submit(function() {
            $(this).find(":input").filter(function(){ return !this.value; }).attr("disabled", "disabled");
            return true; // ensure form still submits
        });

        setTimeout(function () {
            const $searchField = $('.select2-search__field');
            const $results = $('.select2-results');

            // Focus the search input
            $searchField.focus();

            // Hide results initially
            $results.hide();

            // Show results when a user starts typing
            $searchField.on('input', function () {
                $results.show();
            });
        }, 0);
    });
</script>

@stop
