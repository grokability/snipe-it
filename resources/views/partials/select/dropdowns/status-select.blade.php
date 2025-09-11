<select class="js-data-ajax" data-endpoint="statuslabels" data-placeholder="{{ trans('general.select_statuslabel') }}" name="{{ $fieldname }}" style="width: 100%" id="{{ isset($select_id) ? $select_id : $fieldname . '_status_select' }}" aria-label="{{ $fieldname }}" {!! ((isset($item)) && (Helper::checkIfRequired($item, $fieldname))) ? ' required ' : '' !!}{{ (isset($multiple) && ($multiple=='true')) ? " multiple='multiple'" : '' }}>
    @isset ($selected)
    @foreach ($selected as $status_id)
    <option value="{{ $status_id }}" selected="selected" role="option" aria-selected="true">
        {{ \App\Models\Statuslabel::find($status_id)->name }}
    </option>
    @endforeach
    @endisset
    @if ($status_id = old($fieldname, (isset($item)) ? $item->{$fieldname} : ''))
    <option value="{{ $status_id }}" selected="selected" role="option" aria-selected="true" role="option">
        {{ (\App\Models\Statuslabel::find($status_id)) ? \App\Models\Statuslabel::find($status_id)->name : '' }}
    </option>
    @endif

</select>