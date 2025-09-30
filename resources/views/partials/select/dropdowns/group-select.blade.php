<select wire:ignore class="js-data-ajax livewire-select2 group-select" data-endpoint="groups"
    data-placeholder="{{ trans('general.select_group') }}" data-livewire-target="groups" {{-- adjust to your Livewire
    property name --}} name="{{ $fieldname }}" style="width:100%"
    data-livewire-component="{{ $this->getId() }}""
    id="{{ isset($select_id) ? $select_id : $fieldname . '_group_select' }}" {!! isset($item) && Helper::checkIfRequired($item, $fieldname) ? ' required' : '' !!} {{ isset($multiple) && $multiple == 'true' ? " multiple='multiple'" : '' }}
    >

    @isset($selected)
        @php
            if (!is_iterable($selected)) {
                $selected = [$selected];
            }
        @endphp
        @foreach ($selected as $group_id)
            @php $g = \App\Models\Group::find($group_id); @endphp
            @if ($g)
                <option value="{{ $group_id }}" selected="selected">{{ $g->name }}</option>
            @endif
        @endforeach
    @endisset

    @if ($group_id = old($fieldname, isset($item) ? $item->{$fieldname} : ''))
        @php $g2 = \App\Models\Group::find($group_id); @endphp
        @if ($g2)
            <option value="{{ $group_id }}" selected="selected">{{ $g2->name }}</option>
        @endif
    @endif

    @isset($otherOptions)
        @php
            if (!is_iterable($otherOptions)) {
                $otherOptions = [$otherOptions];
            }
        @endphp
        @foreach ($otherOptions as $group_id)
            @php $g = \App\Models\Group::find($group_id); @endphp
            @if ($g)
                <option value="{{ $group_id }}">{{ $g->name }}</option>
            @endif
        @endforeach
    @endisset
</select>