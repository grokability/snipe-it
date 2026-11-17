{{-- $optional opts out of the requiredness, usually only used in the asset-create screen  --}}
@php
    $optional = isset($optional) && $optional;
@endphp
<div class="form-group" id="assignto_selector"{!!  (isset($style)) ? ' style="'.e($style).'"' : ''  !!}>
    <label for="checkout_to_type" class="col-md-3 control-label">{{ trans('admin/hardware/form.checkout_to') }}</label>
    <div class="col-md-8">

        <div class="btn-group" data-toggle="buttons">
            @if ((isset($user_select)) && ($user_select!='false'))
                <label class="btn btn-theme @if((session('checkout_to_type') ?: 'user') == 'user') active @endif">
                    <input name="checkout_to_type" value="user" aria-label="checkout_to_type"
                           type="radio"
                           @unless ($optional) data-required-select="#assigned_user_select" @endunless
                           @checked((session('checkout_to_type') ?: 'user') == 'user')>
                <x-icon type="user" />
                {{ trans('general.user') }}
            </label>
            @endif
            @if ((isset($asset_select)) && ($asset_select!='false'))
                <label class="btn btn-theme @if(session('checkout_to_type') == 'asset') active @endif">
                    <input name="checkout_to_type" value="asset" aria-label="checkout_to_type"
                           type="radio"
                           @unless ($optional) data-required-select="#assigned_asset_select" @endunless
                           @checked(session('checkout_to_type') == 'asset')>
                <i class="fas fa-barcode" aria-hidden="true"></i>
                {{ trans('general.asset') }}
            </label>
            @endif
            @if ((isset($location_select)) && ($location_select!='false'))
                <label class="btn btn-theme @if(session('checkout_to_type') == 'location') active @endif">
                    <input name="checkout_to_type" value="location" aria-label="checkout_to_type"
                           type="radio"
                           @unless ($optional) data-required-select="#assigned_location_location_select" @endunless
                           @checked(session('checkout_to_type') == 'location')>
                <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                {{ trans('general.location') }}
            </label>
            @endif

            <x-form.error name="checkout_to_type" />
        </div>
    </div>
</div>
