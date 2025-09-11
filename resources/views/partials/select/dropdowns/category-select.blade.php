<select class="js-data-ajax" data-endpoint="categories/{{ isset($category_type) ? $category_type : 'assets' }}"
    data-placeholder="{{ trans('general.select_category') }}" name="{{ $fieldname }}" style="width: 100%"
    id="{{ isset($select_id) ? $select_id : $fieldname . '_category_select' }}"
    {!! isset($item) && Helper::checkIfRequired($item, $fieldname) ? ' required ' : '' !!}{{ isset($multiple) && $multiple == 'true' ? " multiple='multiple'" : '' }}>
    @isset($selected)
        @if (!is_iterable($selected))
            @php
                $selected = [$selected];
            @endphp
        @endif
        @foreach ($selected as $category_id)
            <option value="{{ $category_id }}" selected="selected" role="option" aria-selected="true" role="option">
                {{ \App\Models\Category::find($category_id)->name }}
            </option>
        @endforeach
    @endisset
    @if ($category_id = old($fieldname, isset($item) ? $item->{$fieldname} : ''))
        <option value="{{ $category_id }}" selected="selected" role="option" aria-selected="true" role="option">
            {{ \App\Models\Category::find($category_id) ? \App\Models\Category::find($category_id)->name : '' }}
        </option>
    @endif
</select>
