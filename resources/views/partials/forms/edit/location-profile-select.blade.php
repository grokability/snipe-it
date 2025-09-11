<!-- Location -->
<div id="location_id" class="form-group{{ $errors->has('location_id') ? ' has-error' : '' }}"{!! isset($style) ? ' style="' . e($style) . '"' : '' !!}>

    <label for="location_id" class="col-md-3 control-label">{{ $translated_name }}</label>
    <div class="col-md-8">
        @include('partials.select/dropdowns/location-profile-select')
    </div>

    {!! $errors->first(
        'location_id',
        '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>',
    ) !!}

</div>
