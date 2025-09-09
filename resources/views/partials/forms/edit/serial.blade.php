<!-- Serial -->
<div class="form-group {{ $errors->has('serial') ? ' has-error' : '' }}">
    <label for="{{ $fieldname }}" class="col-md-3 control-label">{{ trans('admin/hardware/form.serial') }} </label>
    <div class="col-md-7 col-sm-12">
        <input class="form-control" type="text" name="{{ $fieldname }}" id="{{ $fieldname }}" value="{{ old((isset($old_val_name) ? $old_val_name : $fieldname), $item->serial) }}" {{  (Helper::checkIfRequired($item, 'serial') || ($item->model && $item->model->require_serial)) ? ' required' : '' }} maxlength="191" />
        @error($old_val_name ?? $fieldname)
        <span class="alert-msg" aria-hidden="true">
                <i class="fas fa-times" aria-hidden="true"></i> {{ $message }}
            </span>
        @enderror
    </div>
</div>
