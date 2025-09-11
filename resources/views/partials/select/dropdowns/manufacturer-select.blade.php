<select class="js-data-ajax" data-endpoint="manufacturers" data-placeholder="{{ trans('general.select_manufacturer') }}" name="{{ $fieldname }}" style="width: 100%" id="{{ isset($select_id) ? $select_id : $fieldname . '_manufacturer_select' }}" aria-label="{{ $fieldname }}" {!! ((isset($item)) && (Helper::checkIfRequired($item, $fieldname))) ? ' required ' : '' !!}{{ (isset($multiple) && ($multiple=='true')) ? " multiple='multiple'" : '' }}>
    @isset ($selected)
    @if (!is_iterable($selected))
    @php
    $selected = [$selected];
    @endphp
    @endif
    @foreach ($selected as $manufacturer_id)
    <option value="{{ $manufacturer_id }}" selected="selected" role="option" aria-selected="true" role="option">
        {{ \App\Models\Manufacturer::find($manufacturer_id)->name }}
    </option>
    @endforeach
    @endisset
    @if ($manufacturer_id = old($fieldname, (isset($item)) ? $item->{$fieldname} : ''))
    <option value="{{ $manufacturer_id }}" selected="selected" role="option" aria-selected="true" role="option">
        {{ (\App\Models\Manufacturer::find($manufacturer_id)) ? \App\Models\Manufacturer::find($manufacturer_id)->name : '' }}
    </option>
    @else
    {!! (!isset($multiple) || ($multiple=='false')) ? '<option value="" role="option">'.trans('general.select_manufacturer').'</option>' : '' !!}
    @endif

</select>