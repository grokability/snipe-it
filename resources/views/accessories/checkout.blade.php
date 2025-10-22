@extends('layouts/default')

{{-- Page title --}}
@section('title')
     {{ trans('admin/accessories/general.checkout') }}
@parent
@stop
@section('header_right')
<a href="{{ URL::previous() }}" class="btn btn-primary pull-right">
  {{ trans('general.back') }}</a>
@stop


{{-- Page content --}}
@section('content')


<div class="row">
  <div class="col-md-9">
    <form class="form-horizontal" id="checkout_form" method="post" action="" autocomplete="off">
    <!-- CSRF Token -->
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="box box-default">
      @if ($accessory->id)
        <div class="box-header with-border">
          <h2 class="box-title">{{ $accessory->name }}</h2>
        </div><!-- /.box-header -->
      @endif

       <div class="box-body">
         @if ($accessory->name)
          <!-- accessory name -->
          <div class="form-group">
            <label class="col-sm-3 control-label">{{ trans('admin/accessories/general.accessory_name') }}</label>
            <div class="col-md-6">
              <p class="form-control-static">{{ $accessory->name }}</p>
            </div>
          </div>
          @endif

         @if ($accessory->company)
             <!-- accessory name -->
             <div class="form-group">
                 <label class="col-sm-3 control-label">{{ trans('general.company') }}</label>
                 <div class="col-md-6">
                     <p class="form-control-static">{{ $accessory->company->name }}</p>
                 </div>
             </div>
         @endif


         @if ($accessory->category)
          <!-- accessory name -->
          <div class="form-group">
            <label class="col-sm-3 control-label">{{ trans('admin/accessories/general.accessory_category') }}</label>
            <div class="col-md-6">
              <p class="form-control-static">{{ $accessory->category->name }}</p>
            </div>
          </div>
          @endif

             <!-- total -->
             <div class="form-group">
                 <label class="col-sm-3 control-label">{{  trans('admin/components/general.total') }}</label>
                 <div class="col-md-6">
                     <p class="form-control-static">{{ $accessory->qty }}</p>
                 </div>
             </div>

             <!-- remaining -->
             <div class="form-group">
                 <label class="col-sm-3 control-label">{{  trans('admin/components/general.remaining') }}</label>
                 <div class="col-md-6">
                     <p class="form-control-static">{{ $accessory->numRemaining() }}</p>
                 </div>
             </div>


          <!-- checkout selector -->

             @include ('partials.forms.checkout-selector', ['user_select' => 'true','asset_select' => 'true', 'location_select' => 'true'])
             @include ('partials.forms.edit.user-select', ['translated_name' => trans('general.user'), 'company_id' => $accessory->company_id, 'fieldname' => 'assigned_user', 'style' => (session('checkout_to_type') ?: 'user') == 'user' ? '' : 'display: none;'])
             @include ('partials.forms.edit.asset-select', ['translated_name' => trans('general.asset'), 'asset_selector_div_id' => 'assigned_asset', 'company_id' => $accessory->company_id, 'fieldname' => 'assigned_asset', 'unselect' => 'true', 'style' => session('checkout_to_type') == 'asset' ? '' : 'display: none;'])
             @include ('partials.forms.edit.location-select', ['translated_name' => trans('general.location'), 'fieldname' => 'assigned_location', 'company_id' => $accessory->company_id, 'style' => session('checkout_to_type') == 'location' ? '' : 'display: none;'])



             <!-- Checkout QTY -->
             <div class="form-group {{ $errors->has('checkout_qty') ? 'error' : '' }} ">
                 <label for="checkout_qty" class="col-md-3 control-label">{{ trans('general.qty') }}</label>
                 <div class="col-md-7 col-sm-12 required">
                     <div class="col-md-2" style="padding-left:0px">
                         <input class="form-control" type="number" name="checkout_qty" id="checkout_qty" value="{{ old('checkout_qty', 1) }}" min="1" max="{{ $accessory->numRemaining() }}" />
                     </div>
                 </div>
                 {!! $errors->first('checkout_qty', '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>') !!}
             </div>


             @if ($accessory->requireAcceptance() || $accessory->getEula() || ($snipeSettings->webhook_endpoint!=''))
                 <div class="form-group notification-callout">
                     <div class="col-md-8 col-md-offset-3">
                         <div class="callout callout-info">

                             @if ($accessory->requireAcceptance())
                                 <i class="far fa-envelope"></i>
                                 {{ trans('admin/categories/general.required_acceptance') }}
                                 <br>
                             @endif

                             @if ($accessory->getEula())
                                 <i class="far fa-envelope"></i>
                                 {{ trans('admin/categories/general.required_eula') }}
                                 <br>
                             @endif

                             @if ($snipeSettings->webhook_endpoint!='')
                                 <i class="fab fa-slack"></i>
                                 {{ trans('general.webhook_msg_note') }}
                             @endif
                         </div>
                     </div>
                 </div>
             @endif
          <!-- Note -->
          <div class="form-group {{ $errors->has('note') ? 'error' : '' }}">
            <label for="note" class="col-md-3 control-label">{{ trans('admin/hardware/form.notes') }}</label>
            <div class="col-md-7">
              <textarea class="col-md-6 form-control" id="note" name="note">{{ old('note', $accessory->note) }}</textarea>
              {!! $errors->first('note', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
            </div>
          </div>
       </div>
          <x-redirect_submit_options
                  index_route="accessories.index"
                  :button_label="trans('general.checkout')"
                  :options="[
                        'index' => trans('admin/hardware/form.redirect_to_all', ['type' => trans('general.accessories')]),
                        'item' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.accessory')]),
                        'target' => trans('admin/hardware/form.redirect_to_checked_out_to'),

                       ]"
          />
    </div> <!-- .box.box-default -->
  </form>
  </div> <!-- .col-md-9-->
</div> <!-- .row -->


@stop
