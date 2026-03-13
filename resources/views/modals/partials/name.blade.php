<!-- modals/partials/name.blade.php -->
@php
    $required = $required ?? '';
@endphp
<div class="dynamic-form-row">
    <div class="col-md-4 col-xs-12"><label for="modal-name">{{ trans('general.name') }}:
        </label></div>
    <div class="col-md-8 col-xs-12"><input type='text' name="name" id='modal-name' class="form-control" {{$required ? 'required' : ''}}></div>
</div>
<!-- modals/partials/name.blade.php -->