@extends('layouts/edit-form', [
    'createText' => 'Create Maintenance Schedule',
    'updateText' => 'Update Maintenance Schedule',
    'formAction' => route('maintenance.scheduler.store'),
])

@section('inputFields')

<!-- Title -->
<div class="form-group {{ $errors->has('title') ? ' has-error' : '' }}">
    <label for="title" class="col-md-3 control-label">
        {{ trans('general.name') }} <span class="text-danger">*</span>
    </label>
    <div class="col-md-7">
        <input class="form-control" type="text" name="title" id="title" value="{{ old('title') }}" required>
        {!! $errors->first('title', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times"></i> :message</span>') !!}
    </div>
</div>

<!-- Asset -->
<div class="form-group {{ $errors->has('asset_id') ? ' has-error' : '' }}">
    <label for="asset_id" class="col-md-3 control-label">
        {{ trans('general.asset') }} <span class="text-danger">*</span>
    </label>
    <div class="col-md-7">
        <select class="form-control select2" name="asset_id" id="asset_id" style="width: 100%" required>
            <option value="">Select Asset</option>
            @foreach($assets as $asset)
                <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
                    {{ $asset->asset_tag }} - {{ $asset->name }}
                </option>
            @endforeach
        </select>
        {!! $errors->first('asset_id', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times"></i> :message</span>') !!}
    </div>
</div>

<!-- Predefined Kit -->
<div class="form-group {{ $errors->has('predefined_kit_id') ? ' has-error' : '' }}">
    <label for="predefined_kit_id" class="col-md-3 control-label">
        Predefined Kit
    </label>
    <div class="col-md-7">
        <select class="form-control select2" name="predefined_kit_id" id="predefined_kit_id" style="width: 100%">
            <option value="">None</option>
            @foreach($kits as $kit)
                <option value="{{ $kit->id }}" {{ old('predefined_kit_id') == $kit->id ? 'selected' : '' }}>
                    {{ $kit->name }}
                </option>
            @endforeach
        </select>
        {!! $errors->first('predefined_kit_id', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times"></i> :message</span>') !!}
    </div>
</div>

<!-- Description -->
<div class="form-group {{ $errors->has('description') ? ' has-error' : '' }}">
    <label for="description" class="col-md-3 control-label">
        {{ trans('general.notes') }}
    </label>
    <div class="col-md-7">
        <textarea class="form-control" name="description" id="description" rows="3">{{ old('description') }}</textarea>
        {!! $errors->first('description', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times"></i> :message</span>') !!}
    </div>
</div>

<!-- Frequency -->
<div class="form-group {{ $errors->has('frequency') ? ' has-error' : '' }}">
    <label for="frequency" class="col-md-3 control-label">
        Frequency <span class="text-danger">*</span>
    </label>
    <div class="col-md-7">
        <select class="form-control" name="frequency" id="frequency" required>
            <option value="">Select Frequency</option>
            <option value="daily" {{ old('frequency') == 'daily' ? 'selected' : '' }}>Daily</option>
            <option value="weekly" {{ old('frequency') == 'weekly' ? 'selected' : '' }}>Weekly</option>
            <option value="monthly" {{ old('frequency') == 'monthly' ? 'selected' : '' }}>Monthly</option>
            <option value="quarterly" {{ old('frequency') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
            <option value="yearly" {{ old('frequency') == 'yearly' ? 'selected' : '' }}>Yearly</option>
        </select>
        {!! $errors->first('frequency', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times"></i> :message</span>') !!}
    </div>
</div>

<!-- Frequency Interval -->
<div class="form-group {{ $errors->has('frequency_interval') ? ' has-error' : '' }}">
    <label for="frequency_interval" class="col-md-3 control-label">
        Every <span class="text-danger">*</span>
    </label>
    <div class="col-md-7">
        <input class="form-control" type="number" name="frequency_interval" id="frequency_interval" 
               value="{{ old('frequency_interval', 1) }}" min="1" required>
        <span class="help-block">How many intervals? (e.g., every 2 weeks)</span>
        {!! $errors->first('frequency_interval', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times"></i> :message</span>') !!}
    </div>
</div>

<!-- Start Date -->
<div class="form-group {{ $errors->has('start_date') ? ' has-error' : '' }}">
    <label for="start_date" class="col-md-3 control-label">
        Start Date <span class="text-danger">*</span>
    </label>
    <div class="col-md-7">
        <input class="form-control" type="date" name="start_date" id="start_date" 
               value="{{ old('start_date', date('Y-m-d')) }}" required>
        {!! $errors->first('start_date', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times"></i> :message</span>') !!}
    </div>
</div>

<!-- Priority -->
<div class="form-group {{ $errors->has('priority') ? ' has-error' : '' }}">
    <label for="priority" class="col-md-3 control-label">
        Priority <span class="text-danger">*</span>
    </label>
    <div class="col-md-7">
        <select class="form-control" name="priority" id="priority" required>
            <option value="">Select Priority</option>
            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
            <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
            <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>Critical</option>
        </select>
        {!! $errors->first('priority', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times"></i> :message</span>') !!}
    </div>
</div>

<!-- Assigned To -->
<div class="form-group {{ $errors->has('assigned_to') ? ' has-error' : '' }}">
    <label for="assigned_to" class="col-md-3 control-label">
        Assigned To
    </label>
    <div class="col-md-7">
        <select class="form-control select2" name="assigned_to" id="assigned_to" style="width: 100%">
            <option value="">Unassigned</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                    {{ $user->first_name }} {{ $user->last_name }}
                </option>
            @endforeach
        </select>
        {!! $errors->first('assigned_to', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times"></i> :message</span>') !!}
    </div>
</div>

<!-- Supplier -->
<div class="form-group {{ $errors->has('supplier_id') ? ' has-error' : '' }}">
    <label for="supplier_id" class="col-md-3 control-label">
        Supplier
    </label>
    <div class="col-md-7">
        <select class="form-control select2" name="supplier_id" id="supplier_id" style="width: 100%">
            <option value="">None</option>
            @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                    {{ $supplier->name }}
                </option>
            @endforeach
        </select>
        {!! $errors->first('supplier_id', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times"></i> :message</span>') !!}
    </div>
</div>

<!-- Estimated Duration -->
<div class="form-group {{ $errors->has('estimated_duration') ? ' has-error' : '' }}">
    <label for="estimated_duration" class="col-md-3 control-label">
        Estimated Duration (minutes)
    </label>
    <div class="col-md-7">
        <input class="form-control" type="number" name="estimated_duration" id="estimated_duration" 
               value="{{ old('estimated_duration') }}" min="1">
        {!! $errors->first('estimated_duration', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times"></i> :message</span>') !!}
    </div>
</div>

<!-- Notes -->
<div class="form-group {{ $errors->has('notes') ? ' has-error' : '' }}">
    <label for="notes" class="col-md-3 control-label">
        Additional Notes
    </label>
    <div class="col-md-7">
        <textarea class="form-control" name="notes" id="notes" rows="3">{{ old('notes') }}</textarea>
        {!! $errors->first('notes', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times"></i> :message</span>') !!}
    </div>
</div>

@stop
