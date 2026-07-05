@props([
    'item',
    'field',
])

@if (!empty($item->{$field->db_column_name()}))
    <x-copy-to-clipboard copy_what="{{ $field->id }}">
    </x-copy-to-clipboard>
    {{-- Hidden span used as copy target --}}
    {{-- It's tempting to break out the HTML into separate lines for this, but it results in extra spaces being added onto the end of the copied value --}}
    {{-- For markdown fields, position: absolute removes it from normal flow so it can't create an anonymous block box --}}
    @if (($field->field_encrypted=='1') && (Gate::allows('assets.view.encrypted_custom_fields')))
        <span class="js-copy-{{ $field->id }} visually-hidden hidden-print" style="font-size: 0;{{ $field->element === 'markdown-textarea' ? ' position: absolute;' : '' }}">{{ ($field->isFieldDecryptable($item->{$field->db_column_name()}) ? Helper::gracefulDecrypt($field, $item->{$field->db_column_name()}) : $item->{$field->db_column_name()}) }}</span>
    @elseif (($field->field_encrypted=='1') && (Gate::denies('assets.view.encrypted_custom_fields')))
        <span class="js-copy-{{ $field->id }} visually-hidden hidden-print" style="font-size: 0;{{ $field->element === 'markdown-textarea' ? ' position: absolute;' : '' }}">{{ strtoupper(trans('admin/custom_fields/general.encrypted')) }}</span>
    @else
        <span class="js-copy-{{ $field->id }} visually-hidden hidden-print" style="font-size: 0;{{ $field->element === 'markdown-textarea' ? ' position: absolute;' : '' }}">{{ $item->{$field->db_column_name()} }}</span>
    @endif

@endif

@if (($field->field_encrypted=='1') && ($item->{$field->db_column_name()}!='') && (Gate::allows('assets.view.encrypted_custom_fields')))
    <i class="fas fa-lock fa-fw pull-right" style="font-size: 16px;" data-tooltip="true" data-placement="top" title="{{ trans('admin/custom_fields/general.value_encrypted') }}" onclick="showHideEncValue(this)" id="text-{{ $field->id }}"></i>
@endif

@if ($field->isFieldDecryptable($item->{$field->db_column_name()} ))
    @can('assets.view.encrypted_custom_fields')
        @php
            $fieldSize = strlen(\App\Helpers\Helper::gracefulDecrypt($field, $item->{$field->db_column_name()}))
        @endphp
        @if ($fieldSize > 0)
            <span id="text-{{ $field->id }}-to-hide" style="font-size: 20px;vertical-align:middle;">***********</span>
            @if (($field->format=='URL') && ($item->{$field->db_column_name()}!=''))
                <span class="js-copy-{{ $field->id }} hidden-print"
                      id="text-{{ $field->id }}-to-show"
                      style="font-size: 0;">
                <a href="{{ Helper::gracefulDecrypt($field, $item->{$field->db_column_name()}) }}"
                   target="_new">{{ Helper::gracefulDecrypt($field, $item->{$field->db_column_name()}) }}</a>
            </span>
            @elseif (($field->format=='DATE') && ($item->{$field->db_column_name()}!=''))
                <span class="js-copy-{{ $field->id }} hidden-print"
                      id="text-{{ $field->id }}-to-show"
                      style="font-size: 0;">{{ \App\Helpers\Helper::gracefulDecrypt($field, \App\Helpers\Helper::getFormattedDateObject($item->{$field->db_column_name()}, 'date', false)) }}</span>
            @elseif ($field->element == 'markdown-textarea')
                <div class="js-copy-{{ $field->id }} hidden-print markdown-field-content"
                     id="text-{{ $field->id }}-to-show"
                     data-markdown="true"
                     style="display: none;">{!! Helper::renderMarkdown(Helper::gracefulDecrypt($field, $item->{$field->db_column_name()})) !!}</div>
            @else
                <span class="js-copy-{{ $field->id }} hidden-print"
                      id="text-{{ $field->id }}-to-show"
                      style="font-size: 0;">{{ Helper::gracefulDecrypt($field, $item->{$field->db_column_name()}) }}</span>
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
    @elseif (($field->element == 'markdown-textarea') && ($item->{$field->db_column_name()} != ''))
        <div class="markdown-field-content">{!! Helper::renderMarkdown($item->{$field->db_column_name()}) !!}</div>
    @else
        {!! nl2br(e($item->{$field->db_column_name()})) !!}
    @endif

@endif
