@use('App\Models\Manufacturer', 'Manufacturer')
@use('Illuminate\Support\Arr', 'Arr')

@props([
    'label',
    'name',
    'selected' => null,
    'required' => false,
    'multiple' => false,
    'hideNewButton' => false,
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
            data-endpoint="manufacturers"
            data-placeholder="{{ trans('general.select_manufacturer') }}"
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
                        {{ Manufacturer::find($value)?->name }}
                    </option>
                @endforeach
            @endif
        </select>
    </div>

    @unless($hideNewButton)
        <div class="col-md-1 col-sm-1 text-left">
            @can('create', Manufacturer::class)
                <a href="{{ route('modal.show', 'manufacturer') }}" data-toggle="modal" data-target="#createModal" data-select="{{ $name }}_select" class="btn btn-sm btn-theme">{{ trans('button.new') }}</a>
            @endcan
        </div>
    @endunless

    {!! $errors->first($name, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>') !!}
</div>
