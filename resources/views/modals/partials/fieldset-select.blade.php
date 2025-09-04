<!-- partials/modals/partials/fieldset-select.blade.php -->
<div class="dynamic-form-row">
    <div class="col-md-4 col-xs-12"><label for="modal-fieldset_id">{{ trans('admin/models/general.fieldset') }}:</label></div>
    <div class="col-md-8 col-xs-12">
        <x-input.select
            name="fieldset_id"
            id="modal-fieldset_id"
            :options="Helper::customFieldsetList()"
            :selected="old('fieldset_id')"
            style="width:100%;"
        />
    </div>
</div>
<!-- partials/modals/partials/fieldset-select.blade.php -->
