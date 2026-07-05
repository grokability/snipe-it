@use('App\Helpers\Helper')

@props([
    'item' => null,
    'name' => 'qty',
    'label' => null,
    'min' => 0,
    'max' => null,
    'value' => null,
    'help_text' => null,
])

<div
    @class([
        'form-group',
        'has-error' => $errors->has($name),
    ])
>
    <label for="{{ $name }}" class="col-md-3 control-label">
        {{ $label ?? trans('general.quantity') }}
    </label>
    <div class="col-md-9">
        <div class="col-md-3" style="padding-left: 0">
            <input
                class="form-control"
                type="number"
                name="{{ $name }}"
                id="{{ $name }}"
                aria-label="{{ $label ?? trans('general.quantity') }}"
                value="{{ old($name, $value ?? $item?->{$name} ?? '') }}"
                min="{{ $min }}"
                @if ($max) max="{{ $max }}" @endif
                maxlength="5"
                @required($item && Helper::checkIfRequired($item, $name))
            />
        </div>
        <div class="col-md-12" style="padding-left: 0">
            {!! $errors->first($name, '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
            @if ($help_text)
                <p class="help-block">{{ $help_text }}</p>
            @endif
        </div>
    </div>
</div>
