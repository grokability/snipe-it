<select 
    class="js-data-ajax" 
    data-endpoint="companies" 
    data-placeholder="{{ trans('general.select_company') }}" 
    name="{{ $fieldname }}" 
    style="width: 100%" 
    id="{{ isset($select_id) ? $select_id : $fieldname . '_company_select' }}"
    aria-label="{{ $fieldname }}"
    {{ (isset($multiple) && ($multiple=='true')) ? " multiple='multiple'" : '' }}
    {{ isset($disabled) && $disabled ? 'disabled' : '' }}
>
    @isset($selected)
        @foreach ($selected as $company_id)
            <option value="{{ $company_id }}" selected="selected" role="option" aria-selected="true">
                {{ \App\Models\Company::find($company_id)->name }}
            </option>
        @endforeach
    @endisset

    @if ($company_id = old($fieldname, (isset($item)) ? $item->{$fieldname} : ''))
        <option value="{{ $company_id }}" selected="selected" role="option" aria-selected="true">
            {{ (\App\Models\Company::find($company_id)) ? \App\Models\Company::find($company_id)->name : '' }}
        </option>
    @else
        {!! (!isset($multiple) || ($multiple=='false')) ? '<option value="" role="option">'.trans('general.select_company').'</option>' : '' !!}
    @endif
</select>
