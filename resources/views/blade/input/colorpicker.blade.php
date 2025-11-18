@props([
    'item' => null,
    'name' => 'color',
    'id' => 'color',
])

<!-- Colorpicker -->
<div id="color" class="input-group colorpicker-component row col-md-5">
    <input class="form-control" placeholder="#FF0000" aria-label="{{ $name }}" name="{{ $name }}" type="text" id="{{ $id }}" value="{{ old($name, ($item->{$name} ?? '')) }}">
    <span class="input-group-addon"><i></i></span>
</div>

<!-- /.input group -->
