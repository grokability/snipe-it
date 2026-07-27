@props([
    'item' => null,
    'name' => 'color',
    'id' => 'color',
    'div_id' => null,
    'placeholder' => '#FF0000',
    'default' => null,
])

<!-- Colorpicker -->
<div {{ $attributes->merge(['class' => 'color input-group colorpicker-component row col-md-5']) }} id="{{ $div_id }}">
    <input class="form-control" placeholder="{{ $placeholder }}" aria-label="{{ $name }}" name="{{ $name }}" type="text" id="{{ $id }}" value="{{ old($name, $item->{$name} ?? $default ?? '') }}" @disabled(config('app.lock_passwords'))>
    <span class="input-group-addon"><i style="border: 1px solid #b8b6b6"> </i></span>
</div>

{{-- Callsite grid reset: the colorpicker's outer div is col-md-5, so
     a demo-lock notice placed as a sibling would inherit that narrow
     column and wrap its text. Wrap in a fresh row + col-md-12 so the
     notice has a full-width parent to sit in. --}}
<div class="row">
    <div class="col-md-12">
        <x-demo-lock>{{ trans('general.feature_disabled') }}</x-demo-lock>
    </div>
</div>
<!-- /.input group -->
