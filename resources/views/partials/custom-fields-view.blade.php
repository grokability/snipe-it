{{-- FIXME - this doesn't work for Assets (crap!) --}}
@if ($item->getFieldset())
    @foreach($item->getFieldset()->fields as $field)
        <div class="row">
            <div class="col-md-{{ $width }}">
                <strong>
                    {{ $field->name }}
                </strong>
            </div>
            <div class="col-md-{{ 12 - $width }}{{ (($field->format=='URL') && ($item->{$field->db_column_name()}!='')) ? ' ellipsis': '' }}">
                @if ($field->field_encrypted=='1')
                    <i class="fas fa-lock" data-tooltip="true" data-placement="top" title="{{ trans('admin/custom_fields/general.value_encrypted') }}"></i>
                @endif

                @if ($field->isFieldDecryptable($item->{$field->db_column_name()} ))
                    @can('assets.view.encrypted_custom_fields')
                        @if (($field->format=='URL') && ($item->{$field->db_column_name()}!=''))
                            <a href="{{ Helper::gracefulDecrypt($field, $item->{$field->db_column_name()}) }}" target="_new">{{ Helper::gracefulDecrypt($field, $item->{$field->db_column_name()}) }}</a>
                        @elseif (($field->format=='DATE') && ($item->{$field->db_column_name()}!=''))
                            {{ \App\Helpers\Helper::gracefulDecrypt($field, \App\Helpers\Helper::getFormattedDateObject($item->{$field->db_column_name()}, 'date', false)) }}
                        @else
                            {{ Helper::gracefulDecrypt($field, $item->{$field->db_column_name()}) }}
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

                @if ($item->{$field->db_column_name()}=='')
                    &nbsp;
                @endif
            </div>
        </div>
    @endforeach
@endif