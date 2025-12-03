@props([
    'item' => null,
    'name' => 'color',
    'id' => 'color',
    'div_id' => null,
    'placeholder'=> 'FF0000',
])

<!-- Colorpicker -->
<div {{ $attributes->merge(['class' => 'color input-group colorpicker-component row col-md-5']) }} id="{{ $div_id }}">
    <input class="form-control" placeholder="{{ $placeholder }}" aria-label="{{ $name }}" name="{{ $name }}" type="text" id="{{ $id }}" value="{{ old($name, ($item->{$name} ?? '')) }}" {{ (config('app.lock_passwords')===true) ? ' disabled' : '' }}>
    <span class="input-group-addon"><i style="border: 1px solid #b8b6b6"> </i></span>
</div>

@if (config('app.lock_passwords')===true)
    <div class="row">
    <div class="col-md-12">
        <p class="text-warning">
            <x-icon type="locked" /> {{ trans('general.feature_disabled') }}
        </p>
    </div>
    </div>

@endif
<!-- /.input group -->
