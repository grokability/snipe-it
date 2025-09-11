<select class="js-data-ajax" data-endpoint="departments" data-placeholder="{{ trans('general.select_department') }}" name="{{ $fieldname }}" style="width: 100%" id="{{ isset($select_id) ? $select_id : $fieldname . '_department_select' }}" aria-label="{{ $fieldname }}" {{ (isset($multiple) && ($multiple=='true')) ? " multiple='multiple'" : '' }}>
    @isset ($selected)
    @if (!is_iterable($selected))
    @php
    $selected = [$selected];
    @endphp
    @endif
    @foreach ($selected as $department_id)
    <option value="{{ $department_id }}" selected="selected" role="option" aria-selected="true">
        {{ \App\Models\Department::find($department_id)->name }}
    </option>
    @endforeach
    @endisset
    @if ($department_id = old($fieldname, (isset($item)) ? $item->{$fieldname} : ''))
    <option value="{{ $department_id }}" selected="selected" role="option" aria-selected="true" role="option">
        {{ (\App\Models\Department::find($department_id)) ? \App\Models\Department::find($department_id)->name : '' }}
    </option>
    @endif
</select>