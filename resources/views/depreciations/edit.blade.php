@extends('layouts/edit-form', [
    'createText' => trans('admin/depreciations/general.create') ,
    'updateText' => trans('admin/depreciations/general.update'),
    'helpPosition'  => 'right',
    'helpText' => trans('help.depreciations'),
    'formAction' => (isset($item->id)) ? route('depreciations.update', ['depreciation' => $item->id]) : route('depreciations.store'),
])

{{-- Page content --}}
@section('inputFields')

@include ('partials.forms.edit.name', ['translated_name' => trans('admin/depreciations/general.depreciation_name')])
<!-- Months -->
<div class="form-group {{ $errors->has('months') ? ' has-error' : '' }}">
    <label for="months" class="col-md-3 control-label">
        {{ trans('admin/depreciations/general.number_of_months') }}
    </label>
    <div class="col-md-9 col-sm-12">
        <input class="form-control" type="number" min="0" max="3600" name="months" id="months" value="{{ old('months', $item->months) }}" style="width: 90px;"{!!  (\App\Helpers\Helper::checkIfRequired($item, 'months')) ? ' required' : '' !!} />
        {!! $errors->first('months', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
    </div>
</div>

<!-- Depreciation Minimum -->
<div class="form-group {{ $errors->has('depreciation_min') ? ' has-error' : '' }}">
    <label for="depreciation_min" class="col-md-3 control-label">
        {{ trans('admin/depreciations/general.depreciation_min') }}
    </label>
    <div class="col-md-9" style="display: flex;">
        <input class="form-control" name="depreciation_min" id="depreciation_min" required type="number" value="{{ old('depreciation_min', $item->depreciation_min) }}" style="width: 90px; margin-right: 15px; display: inline-block;" />
        <select class="form-control select2" name="depreciation_type" id="depreciation_type" data-minimum-results-for-search="Infinity" style="width: 150px; display: inline-block;">
            <option value="amount" {{ old('depreciation_type', $item->depreciation_type) == 'amount' ? 'selected' : '' }}>{{ trans('general.depreciation_options.amount') }}</option>
            <option value="percent" {{ old('depreciation_type', $item->depreciation_type) == 'percent' ? 'selected' : '' }}>{{ trans('general.depreciation_options.percent') }}</option>
        </select>
        {!! $errors->first('depreciation_min', '<span class="col-md-7 col-md-offset-3 alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
    </div>

</div>
@stop
