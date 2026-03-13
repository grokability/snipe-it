<!-- partials/forms/edit/username.blade.php -->
@php
    $required = $required ?? '';
@endphp
<div class="form-group">
    <div class="col-md-3 col-xs-12">
        <label for="modal-username">{{ trans('admin/users/table.username') }}:
        </label>
    </div>
    <div class="col-md-8 col-xs-12">
        <input type='text' name="username" id='modal-username' class="form-control" maxlength="191" {{ $required ? ' required' : ''}}>
    </div>
</div>
<!-- partials/forms/edit/username.blade.php -->