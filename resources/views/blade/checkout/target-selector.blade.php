@props([
    'item',
    'optional' => false,
    'style' => null,
])

@php
    use App\Helpers\Helper;
    use App\Models\Asset;
    use App\Models\Location;
    use App\Models\User;

    $allowed = $item::allowedCheckoutTargetTypes();
    $allowsUser = in_array(User::class, $allowed, true);
    $allowsAsset = in_array(Asset::class, $allowed, true);
    $allowsLocation = in_array(Location::class, $allowed, true);

    // Default to the first entry declared on the contract when the
    // session has no prior choice. Short-string form ('user'/'asset'/
    // 'location') to match what the backend expects on the request.
    $defaultType = isset($allowed[0]) ? Helper::shortTypeName($allowed[0]) : 'user';
    $selectedType = session('checkout_to_type', $defaultType);
@endphp

<div class="form-group" id="assignto_selector" @if ($style) style="{{ $style }}" @endif>
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
