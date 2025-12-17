<select class="js-data-ajax select2" data-endpoint="licenses" data-placeholder="{{ trans('general.select_license') }}" name="{{ $fieldname }}" style="width: 100%" id="{{ (isset($select_id)) ? $select_id : 'assigned_license_select' }}" {{ (isset($multiple)) ? ' multiple' : '' }}{{  ((isset($required) && ($required =='true'))) ?  ' required' : '' }}>

    @if ((!isset($unselect)) && ($license_id = old($fieldname, (isset($license) ? $license->id : (isset($item) ? $item->{$fieldname} : '')))))
    <option value="{{ $license_id }}" selected="selected">
        {{ (\App\Models\License::find($license_id)) ? \App\Models\License::find($license_id)->present()->fullName : '' }}
    </option>
    @else
    @if(!isset($multiple))
    <option value="">{{ trans('general.select_license') }}</option>
    @endif
    @endif
</select>