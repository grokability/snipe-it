{{-- $optional opts out of the requiredness, usually only used in the asset-create screen.

     Tab visibility is driven by the item's Checkoutable contract
     (App\Models\Contracts\Checkoutable::allowedCheckoutTargetTypes).
     Order from the contract is UI-significant: the first allowed
     target type becomes the default-selected tab when the session has
     no prior choice. Add a tab to the picker by adding the target
     class to the model's allowedCheckoutTargetTypes(), not by editing
     this template. --}}
@php
    $optional = isset($optional) && $optional;
    $allowed = $item::allowedCheckoutTargetTypes();
    $allowsUser = in_array(\App\Models\User::class, $allowed, true);
    $allowsAsset = in_array(\App\Models\Asset::class, $allowed, true);
    $allowsLocation = in_array(\App\Models\Location::class, $allowed, true);

    // Default to the first entry declared on the contract when the
    // session has no prior choice. Short-string form ('user'/'asset'/
    // 'location') to match what the backend expects on the request.
    $defaultType = match ($allowed[0] ?? null) {
        \App\Models\User::class => 'user',
        \App\Models\Asset::class => 'asset',
        \App\Models\Location::class => 'location',
        default => 'user',
    };
    $selectedType = session('checkout_to_type', $defaultType);
@endphp
<div class="form-group" id="assignto_selector"{!!  (isset($style)) ? ' style="'.e($style).'"' : ''  !!}>
    <label for="checkout_to_type" class="col-md-3 control-label">{{ trans('admin/hardware/form.checkout_to') }}</label>
    <div class="col-md-8">

        <div class="btn-group" data-toggle="buttons">
            @if ($allowsUser)
                <label class="btn btn-theme @if($selectedType == 'user') active @endif">
                    <input name="checkout_to_type" value="user" aria-label="checkout_to_type"
                           type="radio"
                           @unless ($optional) data-required-select="#assigned_user_select" @endunless
                           @checked($selectedType == 'user')>
                <x-icon type="user" />
                {{ trans('general.user') }}
            </label>
            @endif
            @if ($allowsAsset)
                <label class="btn btn-theme @if($selectedType == 'asset') active @endif">
                    <input name="checkout_to_type" value="asset" aria-label="checkout_to_type"
                           type="radio"
                           @unless ($optional) data-required-select="#assigned_asset_select" @endunless
                           @checked($selectedType == 'asset')>
                <i class="fas fa-barcode" aria-hidden="true"></i>
                {{ trans('general.asset') }}
            </label>
            @endif
            @if ($allowsLocation)
                <label class="btn btn-theme @if($selectedType == 'location') active @endif">
                    <input name="checkout_to_type" value="location" aria-label="checkout_to_type"
                           type="radio"
                           @unless ($optional) data-required-select="#assigned_location_location_select" @endunless
                           @checked($selectedType == 'location')>
                <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                {{ trans('general.location') }}
            </label>
            @endif

            <x-form.error name="checkout_to_type" />
        </div>
    </div>
</div>
