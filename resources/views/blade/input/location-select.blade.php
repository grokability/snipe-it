@use('App\Models\Location', 'Location')
@use('Illuminate\Support\Arr', 'Arr')

@props([
    'label',
    'name',
    'selected',
    'required' => false,
    'multiple' => false,
    'helpText' => null,
    'hideNewButton' => false,
])

@php
    $id = $name . '_location_select';

    // User provided id, overwrite the default
    if ($attributes->has('id')) {
        $id = $attributes->get('id');
    }
@endphp

<div
    @class([
       'form-group',
       'has-error' => $errors->has($name),
    ])
>

    <label for="{{ $name }}" class="col-md-3 control-label">{{ $label }}</label>
    <div class="col-md-7">
        <select
            class="js-data-ajax"
            data-endpoint="locations"
            data-placeholder="{{ trans('general.select_location') }}"
            name="{{ $name }}"
            style="width: 100%"
            id="{{ $id }}"
            aria-label="{{ $name }}"
            @required($required)
            @if ($multiple)
                multiple
            @endif
        >
            @if ($selected)
                @foreach(Arr::wrap($selected) as $value)
                    <option value="{{ $value }}" selected="selected" role="option" aria-selected="true"  role="option">
                        {{ optional(Location::find($value))->name }}
                    </option>
                @endforeach
            @endif
        </select>
    </div>

    <div class="col-md-1 col-sm-1 text-left">
        @unless($hideNewButton)
            @can('create', Location::class)
                <a href='{{ route('modal.show', 'location') }}' data-toggle="modal" data-target="#createModal" data-select='{{ $name }}_location_select' class="btn btn-sm btn-primary">{{ trans('button.new') }}</a>
            @endcan
        @endunless
    </div>

    {!! $errors->first($name, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>') !!}

    @if ($helpText)
        <div class="col-md-7 col-sm-11 col-md-offset-3">
            <p class="help-block">{{ $helpText }}</p>
        </div>
    @endif

</div>
