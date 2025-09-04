<!-- Datepicker -->
<div class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}">
    <label for="{{ $fieldname }}" class="col-md-3 control-label">{{ $translated_name }}</label>
    <div class="input-group col-md-4">
        <x-input.datepicker
                name="{{ $fieldname }}"
                value="{{ old($fieldname, ($item->{$fieldname}) ? date('Y-m-d', strtotime($item->{$fieldname})) : '') }}"
                placeholder="{{ trans('general.select_date') }}"
                required="{{ Helper::checkIfRequired($item, 'start_date') }}"
        />
        {!! $errors->first($fieldname, '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
    </div>
    @if  (isset($help_text))
        <div class="col-md-8 col-md-offset-3">
            <p class="help-block">
                {!!  $help_text !!}
            </p>
        </div>
    @endif
</div>

<!-- /Datepicker -->