@use('App\Models\Category', 'Category')
@use('Illuminate\Support\Arr', 'Arr')

@props([
    'label',
    'name',
    'selected' => null,
    'required' => false,
    'multiple' => false,
    'hideNewButton' => false,
    'categoryType' => 'assets',
])

<div
    @class([
        'form-group',
        'has-error' => $errors->has($name),
    ])
>
    <label for="{{ $name }}_select" class="col-md-3 control-label">{{ $label }}</label>
    <div class="col-md-7">
        <select
            class="js-data-ajax"
            data-endpoint="categories/{{ $categoryType }}"
            data-placeholder="{{ trans('general.select_category') }}"
            name="{{ $name }}{{ $multiple ? '[]' : '' }}"
            id="{{ $name }}_select"
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
                <a href="{{ route('modal.show', ['type' => 'category', 'category_type' => $categoryType]) }}" data-toggle="modal" data-target="#createModal" data-select="{{ $name }}_select" class="btn btn-sm btn-theme">{{ trans('button.new') }}</a>
            @endcan
        </div>
    @endunless

    {!! $errors->first($name, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>') !!}
</div>
