@props([
    'item',
    'field',
])

@if (!empty($item->{$field->db_column_name()}))
    <x-copy-to-clipboard copy_what="{{ $field->id }}">
    </x-copy-to-clipboard>
    {{-- Hidden span used as copy target --}}
    {{-- It's tempting to break out the HTML into separate lines for this, but it results in extra spaces being added onto the end of the copied value --}}
    @if (($field->field_encrypted=='1') && (Gate::allows('assets.view.encrypted_custom_fields')))
        <span class="js-copy-{{ $field->id }} visually-hidden hidden-print" style="font-size: 0px;">{{ ($field->isFieldDecryptable($item->{$field->db_column_name()}) ? Helper::gracefulDecrypt($field, $item->{$field->db_column_name()}) : $item->{$field->db_column_name()}) }}</span>
    @elseif (($field->field_encrypted=='1') && (Gate::denies('assets.view.encrypted_custom_fields')))
        <span class="js-copy-{{ $field->id }} visually-hidden hidden-print" style="font-size: 0px;">{{ strtoupper(trans('admin/custom_fields/general.encrypted')) }}</span>
    @else
        <span class="js-copy-{{ $field->id }} visually-hidden hidden-print" style="font-size: 0px;">{{ $item->{$field->db_column_name()} }}</span>
    @endif

@endif

@if (($field->field_encrypted=='1') && ($item->{$field->db_column_name()}!='') && (Gate::allows('assets.view.encrypted_custom_fields')))
    <i class="fas fa-lock" data-tooltip="true" data-placement="top" title="{{ trans('admin/custom_fields/general.value_encrypted') }}" onclick="showHideEncValue(this)" id="text-{{ $field->id }}"></i>
@endif

@if ($field->isFieldDecryptable($item->{$field->db_column_name()} ))
    @can('assets.view.encrypted_custom_fields')
        @php
            $fieldSize = strlen(Helper::gracefulDecrypt($field, $item->{$field->db_column_name()}))
        @endphp
        @if ($fieldSize > 0)
            <span id="text-{{ $field->id }}-to-hide">***********</span>
            @if (($field->format=='URL') && ($item->{$field->db_column_name()}!=''))
                <span class="js-copy-{{ $field->id }} hidden-print"
                      id="text-{{ $field->id }}-to-show"
                      style="font-size: 0px;">
                                                                                <a href="{{ Helper::gracefulDecrypt($field, $item->{$field->db_column_name()}) }}"
                                                                                   target="_new">{{ Helper::gracefulDecrypt($field, $item->{$field->db_column_name()}) }}</a>
                                                                            </span>
            @elseif (($field->format=='DATE') && ($item->{$field->db_column_name()}!=''))
                <span class="js-copy-{{ $field->id }} hidden-print"
                      id="text-{{ $field->id }}-to-show"
                      style="font-size: 0px;">{{ \App\Helpers\Helper::gracefulDecrypt($field, \App\Helpers\Helper::getFormattedDateObject($item->{$field->db_column_name()}, 'date', false)) }}</span>
            @else
                <span class="js-copy-{{ $field->id }} hidden-print"
                      id="text-{{ $field->id }}-to-show"
                      style="font-size: 0px;">{{ Helper::gracefulDecrypt($field, $item->{$field->db_column_name()}) }}</span>
            @endif
        @endif
    @else
        {{ strtoupper(trans('admin/custom_fields/general.encrypted')) }}
    @endcan

@else
    @if (($field->format=='BOOLEAN') && ($item->{$field->db_column_name()}!=''))
        {!! ($item->{$field->db_column_name()} == 1) ? "<span class='fas fa-check-circle' style='color:green' />" : "<span class='fas fa-times-circle' style='color:red' />" !!}
    @elseif (($field->format=='URL') && ($item->{$field->db_column_name()}!=''))
        <a href="{{ $item->{$field->db_column_name()} }}" target="_new">{{ $item->{$field->db_column_name()} }}</a>
    @elseif (($field->format=='DATE') && ($item->{$field->db_column_name()}!=''))
        {{ \App\Helpers\Helper::getFormattedDateObject($item->{$field->db_column_name()}, 'date', false) }}
    @else
        {!! nl2br(e($item->{$field->db_column_name()})) !!}
    @endif

@endif
