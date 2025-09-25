<select class="js-data-ajax" data-endpoint="groups"
    data-placeholder="{{ trans('general.select_group') }}" name="{{ $fieldname }}" style="width: 100%"
    id="{{ isset($select_id) ? $select_id : $fieldname . '_group_select' }}"
    {!! isset($item) && Helper::checkIfRequired($item, $fieldname) ? ' required ' : '' !!}{{ isset($multiple) && $multiple == 'true' ? " multiple='multiple'" : '' }}>
    @isset($selected)
        @if (!is_iterable($selected))
            @php
                $selected = [$selected];
            @endphp
        @endif
        @foreach ($selected as $group_id)
            <option value="{{ $group_id }}" selected="selected" role="option" aria-selected="true" role="option">
                {{ \App\Models\Group::find($group_id)->name }}
            </option>
        @endforeach
    @endisset
    @if ($group_id = old($fieldname, isset($item) ? $item->{$fieldname} : ''))
        <option value="{{ $group_id }}" selected="selected" role="option" aria-selected="true" role="option">
            {{ \App\Models\Group::find($group_id) ? \App\Models\Group::find($group_id)->name : '' }}
        </option>
    @endif
</select>
