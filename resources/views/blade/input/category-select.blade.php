@use('App\Models\Category', 'Category')
@use('Illuminate\Support\Arr', 'Arr')

{{-- Auto-hides the "New" button when rendered inside an AJAX modal.
     See x-input.company-select for the pattern. --}}
@aware(['submitToSelect2' => false])

@props([
    'label',
    'name',
    'selected' => null,
    'required' => false,
    'multiple' => false,
    'hideNewButton' => false,
    'categoryType' => 'asset',
    'id' => null,
    'help_text' => null,
])

@php
    $selectId = $id ?? $name.'_select';
    $hideNewButton = $hideNewButton || $submitToSelect2;
@endphp

<div
    @class([
        'form-group',
        'has-error' => $errors->has($name),
    ])
>
    <label for="{{ $selectId }}" class="col-md-3 control-label">{{ $label }}</label>
    <div class="col-md-7">
        <select
            class="js-data-ajax"
            data-endpoint="categories/{{ $categoryType }}"
            data-placeholder="{{ trans('general.select_category') }}"
            name="{{ $name }}{{ $multiple ? '[]' : '' }}"
            id="{{ $selectId }}"
            style="width: 100%"
            aria-label="{{ $label }}"
            @required($required)
            @if ($multiple) multiple @endif
        >
            <option value=""></option>
            @if ($selected)
                @foreach(Arr::wrap($selected) as $value)
                    <option value="{{ $value }}" selected="selected" role="option" aria-selected="true">
                        {{ Category::find($value)?->name }}
                    </option>
                @endforeach
            @endif
        </select>
    </div>

    @unless($hideNewButton)
        <div class="col-md-1 col-sm-1 text-left">
            @can('create', Category::class)
                <a href="{{ route('modal.show', ['type' => 'category', 'category_type' => $categoryType]) }}" data-toggle="modal" data-target="#createModal" data-select="{{ $selectId }}" class="btn btn-sm btn-theme">{{ trans('button.new') }}</a>
            @endcan
        </div>
    @endunless

    <div class="col-md-7 col-md-offset-3">
        <x-form.error :name="$name" />
        @if ($help_text)
            <x-form.help :name="$selectId">{!! $help_text !!}</x-form.help>
        @endif
    </div>
</div>
