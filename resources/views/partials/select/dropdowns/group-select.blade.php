<select
    class="js-data-ajax livewire-select2"
    data-placeholder="{{ trans('general.select_group') }}"
    name="{{ $fieldname }}@if(isset($multiple) && $multiple)@endif"
    style="width: 100%"
    data-livewire-component="{{ $this->getId() }}"
    wire:model="groupSelect"
    wire:ignore.self
    id="{{ isset($select_id) ? $select_id : $fieldname . '_group_select' }}"
    @if(isset($item) && Helper::checkIfRequired($item, $fieldname)) required @endif
    @if(isset($multiple) && $multiple) multiple="multiple" @endif
>
    @isset($selected)
        @php
            if (!is_iterable($selected)) {
                $selected = [$selected];
            }
        @endphp
        @foreach ($selected as $group_id)
            <option value="{{ $group_id }}" selected="selected" role="option" aria-selected="true">
                {{ \App\Models\Group::find($group_id)->name }}
            </option>
        @endforeach
    @endisset

    @isset($otherOptions)
        @foreach ($otherOptions as $group_id)
            <option value="{{ $group_id }}" role="option">
                {{ \App\Models\Group::find($group_id)->name }}
            </option>
        @endforeach
    @endisset

    @if ($group_id = old($fieldname, isset($item) ? $item->{$fieldname} : ''))
        <option value="{{ $group_id }}" selected="selected" role="option" aria-selected="true">
            {{ \App\Models\Group::find($group_id) ? \App\Models\Group::find($group_id)->name : '' }}
        </option>
    @endif
</select>