<!-- modals/partials/categories-select.blade.php -->
@php
    $required = $required ?? '';
@endphp
<div class="dynamic-form-row">
    <div class="col-md-4 col-xs-12"><label for="modal-category_id">{{ trans('general.category') }}:</label></div>
    <div class="col-md-8 col-xs-12 required">
        <select class="js-data-ajax" data-endpoint="categories/asset" name="category_id" style="width: 100%" id="modal-category_id" {{$required ? 'required' : ''}}></select>
    </div>
</div>
<!-- modals/partials/categories-select.blade.php -->