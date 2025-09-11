<select class="js-data-ajax" data-endpoint="models" data-placeholder="{{ trans('general.select_model') }}"
    name="{{ $fieldname }}" style="width: 100%" id="{{ isset($select_id) ? $select_id : $fieldname . '_model_select' }}"
    aria-label="{{ $fieldname }}" {{ isset($field_req) || (isset($required) && $required == 'true') ? ' required' : '' }}{{ isset($multiple) && $multiple == 'true' ? " multiple='multiple'" : '' }}>
    @isset($selected)
    @if (!is_iterable($selected))
    @php
    $selected = [$selected];
    @endphp
    @endif
    @foreach ($selected as $model_id)
    <option value="{{ $model_id }}" selected="selected" role="option" aria-selected="true">
        {{ \App\Models\AssetModel::find($model_id)->name }}
    </option>
    @endforeach
    @endisset
    @if ($model_id = old($fieldname, $item->{$fieldname} ?? (request($fieldname) ?? '')))
    <option value="{{ $model_id }}" selected="selected">
        {{ \App\Models\AssetModel::find($model_id) ? \App\Models\AssetModel::find($model_id)->name : '' }}
    </option>
    @endif

</select>