<select class="js-data-ajax" data-endpoint="suppliers" data-placeholder="{{ trans('general.select_supplier') }}" name="{{ $fieldname }}" style="width: 100%" id="{{ isset($select_id) ? $select_id : $fieldname . '_suppplier_select' }}" aria-label="{{ $fieldname }}" {{ (isset($multiple) && ($multiple=='true')) ? " multiple='multiple'" : '' }}{{ (isset($item) && (Helper::checkIfRequired($item, $fieldname))) ? ' required' : '' }}>
    @isset ($selected)
    @foreach ($selected as $supplier_id)
    <option value="{{ $supplier_id }}" selected="selected" role="option" aria-selected="true">
        {{ \App\Models\Supplier::find($supplier_id)->name }}
    </option>
    @endforeach
    @endisset
    @if ($supplier_id = old($fieldname, (isset($item)) ? $item->{$fieldname} : ''))
    <option value="{{ $supplier_id }}" selected="selected" role="option" aria-selected="true" role="option">
        {{ (\App\Models\Supplier::find($supplier_id)) ? \App\Models\Supplier::find($supplier_id)->name : '' }}
    </option>
    @endif
</select>