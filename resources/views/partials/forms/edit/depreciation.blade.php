<!-- Depreciation -->
<div class="form-group {{ $errors->has('depreciation_id') ? ' has-error' : '' }}">
    <label for="depreciation_id" class="col-md-3 control-label">{{ trans('general.depreciation') }}</label>
    <div class="col-md-7">
        <x-input.select
            name="depreciation_id"
            id="depreciation_id"
            :options="$depreciation_list"
            :selected="old('depreciation_id', $item->depreciation_id)"
            style="width:350px;"
            aria-label="depreciation_id"
        />
        {!! $errors->first('depreciation_id', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
    </div>
</div>
