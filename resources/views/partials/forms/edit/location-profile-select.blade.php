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

    <div class="col-md-8 col-md-offset-3"><x-form.error name="location_id" /></div>

    @if ($snipeSettings->full_multiple_companies_support == '1' && $snipeSettings->scope_locations_fmcs == '1')
        @cannot('superuser')
            <div class="col-md-8 col-md-offset-3">
                <p class="help-block"><x-icon type="tip" /> {{ trans('general.fmcs_location_select_note') }}</p>
            </div>
        @endcannot
    @endif

</div>



