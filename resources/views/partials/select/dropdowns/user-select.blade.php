<select class="js-data-ajax" data-endpoint="users" data-placeholder="{{ trans('general.select_user') }}" name="{{ $fieldname }}" style="width: 100%" id="{{ isset($select_id) ? $select_id : $fieldname . '_user_select' }}" aria-label="{{ $fieldname }}" {{  ((isset($required)) && ($required=='true')) ? ' required' : '' }}>
    @if ($user_id = old($fieldname, (isset($item)) ? $item->{$fieldname} : ''))
    <option value="{{ $user_id }}" selected="selected" role="option" aria-selected="true" role="option">
        {{ (\App\Models\User::find($user_id)) ? \App\Models\User::find($user_id)->present()->fullName : '' }}
    </option>
    @else
    <option value="" role="option">{{ trans('general.select_user') }}</option>
    @endif
</select>