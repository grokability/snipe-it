<!-- Location -->
<div id="location_id" class="form-group{{ $errors->has('location_id') ? ' has-error' : '' }}"{!!  (isset($style)) ? ' style="'.e($style).'"' : ''  !!}>

    <label for="location_id" class="col-md-3 control-label">{{ $translated_name }}</label>
    <div class="col-md-8">
        <select class="js-data-ajax" data-endpoint="locations" data-placeholder="{{ trans('general.select_location') }}" name="location_id" style="width: 100%" id="location_id_location_select" aria-label="location_id">
            @if ($location_id = old('location_id', (isset($user)) ? $user->location_id : ''))
                <option value="{{ $location_id }}" selected="selected" role="option" aria-selected="true"  role="option">
                    {{ (\App\Models\Location::find($location_id)) ? \App\Models\Location::find($location_id)->name : '' }}
                </option>
            @else
                <option value=""  role="option">{{ trans('general.select_location') }}</option>
            @endif
        </select>
    </div>

    {!! $errors->first('location_id', '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>') !!}

</div>



