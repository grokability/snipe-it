@extends('layouts/edit-form', [
    'createText' => 'Edit Work Order',
    'updateText' => 'Update Work Order',
    'formAction' => route('maintenance.workorders.update', $workorder->id),
    'item' => $workorder,
])

@section('inputFields')
{{ method_field('PUT') }}
<!-- Title -->
<div class="form-group {{ $errors->has('title') ? ' has-error' : '' }}">
    <label for="title" class="col-md-3 control-label">
        Title <span class="text-danger">*</span>
    </label>
    <div class="col-md-7 col-sm-12">
        <input class="form-control" type="text" name="title" id="title" value="{{ old('title', $workorder->title) }}" required>
        {!! $errors->first('title', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times"></i> :message</span>') !!}
    </div>
</div>

    <!-- Asset -->
    <div class="form-group {{ $errors->has('asset_id') ? ' has-error' : '' }}">
        <label for="asset_id" class="col-md-3 control-label">
            Asset <span class="text-danger">*</span>
        </label>
        <div class="col-md-7 col-sm-12">
            <select name="asset_id" id="asset_id" class="form-control select2" required>
                <option value="">Select Asset</option>
                @foreach($assets as $asset)
                    <option value="{{ $asset->id }}" {{ old('asset_id', $workorder->asset_id) == $asset->id ? 'selected' : '' }}>
                        {{ $asset->asset_tag }} - {{ $asset->name }}
                    </option>
                @endforeach
            </select>
            {!! $errors->first('asset_id', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- Maintenance Schedule -->
    <div class="form-group {{ $errors->has('maintenance_schedule_id') ? ' has-error' : '' }}">
        <label for="maintenance_schedule_id" class="col-md-3 control-label">
            From Schedule
        </label>
        <div class="col-md-7 col-sm-12">
            <select name="maintenance_schedule_id" id="maintenance_schedule_id" class="form-control select2">
                <option value="">None (Ad-hoc)</option>
                @foreach($schedules as $schedule)
                    <option value="{{ $schedule->id }}" {{ old('maintenance_schedule_id', $workorder->maintenance_schedule_id) == $schedule->id ? 'selected' : '' }}>
                        {{ $schedule->title }}
                    </option>
                @endforeach
            </select>
            {!! $errors->first('maintenance_schedule_id', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- Predefined Kit -->
    <div class="form-group {{ $errors->has('predefined_kit_id') ? ' has-error' : '' }}">
        <label for="predefined_kit_id" class="col-md-3 control-label">
            Predefined Kit
        </label>
        <div class="col-md-7 col-sm-12">
            <select name="predefined_kit_id" id="predefined_kit_id" class="form-control select2">
                <option value="">None</option>
                @foreach($kits as $kit)
                    <option value="{{ $kit->id }}" {{ old('predefined_kit_id', $workorder->predefined_kit_id) == $kit->id ? 'selected' : '' }}>
                        {{ $kit->name }}
                    </option>
                @endforeach
            </select>
            {!! $errors->first('predefined_kit_id', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- Description -->
    <div class="form-group {{ $errors->has('description') ? ' has-error' : '' }}">
        <label for="description" class="col-md-3 control-label">
            Description
        </label>
        <div class="col-md-7 col-sm-12">
            <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $workorder->description) }}</textarea>
            {!! $errors->first('description', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- Type -->
    <div class="form-group {{ $errors->has('type') ? ' has-error' : '' }}">
        <label for="type" class="col-md-3 control-label">
            Type <span class="text-danger">*</span>
        </label>
        <div class="col-md-7 col-sm-12">
            <select name="type" id="type" class="form-control" required>
                <option value="">Select Type</option>
                <option value="preventive" {{ old('type', $workorder->type) == 'preventive' ? 'selected' : '' }}>Preventive</option>
                <option value="corrective" {{ old('type', $workorder->type) == 'corrective' ? 'selected' : '' }}>Corrective</option>
                <option value="inspection" {{ old('type', $workorder->type) == 'inspection' ? 'selected' : '' }}>Inspection</option>
                <option value="emergency" {{ old('type', $workorder->type) == 'emergency' ? 'selected' : '' }}>Emergency</option>
            </select>
            {!! $errors->first('type', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- Priority -->
    <div class="form-group {{ $errors->has('priority') ? ' has-error' : '' }}">
        <label for="priority" class="col-md-3 control-label">
            Priority <span class="text-danger">*</span>
        </label>
        <div class="col-md-7 col-sm-12">
            <select name="priority" id="priority" class="form-control" required>
                <option value="">Select Priority</option>
                <option value="low" {{ old('priority', $workorder->priority) == 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ old('priority', $workorder->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ old('priority', $workorder->priority) == 'high' ? 'selected' : '' }}>High</option>
                <option value="critical" {{ old('priority', $workorder->priority) == 'critical' ? 'selected' : '' }}>Critical</option>
            </select>
            {!! $errors->first('priority', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- Status -->
    <div class="form-group {{ $errors->has('status') ? ' has-error' : '' }}">
        <label for="status" class="col-md-3 control-label">
            Status <span class="text-danger">*</span>
        </label>
        <div class="col-md-7 col-sm-12">
            <select name="status" id="status" class="form-control" required>
                <option value="">Select Status</option>
                <option value="pending" {{ old('status', $workorder->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="in_progress" {{ old('status', $workorder->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="on_hold" {{ old('status', $workorder->status) == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                <option value="completed" {{ old('status', $workorder->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ old('status', $workorder->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            {!! $errors->first('status', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- Assigned To -->
    <div class="form-group {{ $errors->has('assigned_to') ? ' has-error' : '' }}">
        <label for="assigned_to" class="col-md-3 control-label">
            Assigned To
        </label>
        <div class="col-md-7 col-sm-12">
            <select name="assigned_to" id="assigned_to" class="form-control select2">
                <option value="">Unassigned</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('assigned_to', $workorder->assigned_to) == $user->id ? 'selected' : '' }}>
                        {{ $user->getFullNameAttribute() }}
                    </option>
                @endforeach
            </select>
            {!! $errors->first('assigned_to', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- Supplier -->
    <div class="form-group {{ $errors->has('supplier_id') ? ' has-error' : '' }}">
        <label for="supplier_id" class="col-md-3 control-label">
            Supplier
        </label>
        <div class="col-md-7 col-sm-12">
            <select name="supplier_id" id="supplier_id" class="form-control select2">
                <option value="">None</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ old('supplier_id', $workorder->supplier_id) == $supplier->id ? 'selected' : '' }}>
                        {{ $supplier->name }}
                    </option>
                @endforeach
            </select>
            {!! $errors->first('supplier_id', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- Scheduled Start -->
    <div class="form-group {{ $errors->has('scheduled_start') ? ' has-error' : '' }}">
        <label for="scheduled_start" class="col-md-3 control-label">
            Scheduled Start
        </label>
        <div class="col-md-7 col-sm-12">
            <input type="datetime-local" name="scheduled_start" id="scheduled_start" class="form-control" 
                   value="{{ old('scheduled_start', $workorder->scheduled_start ? $workorder->scheduled_start->format('Y-m-d\TH:i') : '') }}">
            {!! $errors->first('scheduled_start', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- Scheduled End -->
    <div class="form-group {{ $errors->has('scheduled_end') ? ' has-error' : '' }}">
        <label for="scheduled_end" class="col-md-3 control-label">
            Scheduled End
        </label>
        <div class="col-md-7 col-sm-12">
            <input type="datetime-local" name="scheduled_end" id="scheduled_end" class="form-control" 
                   value="{{ old('scheduled_end', $workorder->scheduled_end ? $workorder->scheduled_end->format('Y-m-d\TH:i') : '') }}">
            {!! $errors->first('scheduled_end', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- Actual Start -->
    <div class="form-group {{ $errors->has('actual_start') ? ' has-error' : '' }}">
        <label for="actual_start" class="col-md-3 control-label">
            Actual Start
        </label>
        <div class="col-md-7 col-sm-12">
            <input type="datetime-local" name="actual_start" id="actual_start" class="form-control" 
                   value="{{ old('actual_start', $workorder->actual_start ? $workorder->actual_start->format('Y-m-d\TH:i') : '') }}">
            {!! $errors->first('actual_start', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- Actual End -->
    <div class="form-group {{ $errors->has('actual_end') ? ' has-error' : '' }}">
        <label for="actual_end" class="col-md-3 control-label">
            Actual End
        </label>
        <div class="col-md-7 col-sm-12">
            <input type="datetime-local" name="actual_end" id="actual_end" class="form-control" 
                   value="{{ old('actual_end', $workorder->actual_end ? $workorder->actual_end->format('Y-m-d\TH:i') : '') }}">
            {!! $errors->first('actual_end', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- Estimated Duration -->
    <div class="form-group {{ $errors->has('estimated_duration') ? ' has-error' : '' }}">
        <label for="estimated_duration" class="col-md-3 control-label">
            Estimated Duration (minutes)
        </label>
        <div class="col-md-7 col-sm-12">
            <input type="number" name="estimated_duration" id="estimated_duration" class="form-control" 
                   value="{{ old('estimated_duration', $workorder->estimated_duration) }}" min="1">
            {!! $errors->first('estimated_duration', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- Estimated Cost -->
    <div class="form-group {{ $errors->has('estimated_cost') ? ' has-error' : '' }}">
        <label for="estimated_cost" class="col-md-3 control-label">
            Estimated Cost
        </label>
        <div class="col-md-7 col-sm-12">
            <input type="number" name="estimated_cost" id="estimated_cost" class="form-control" 
                   value="{{ old('estimated_cost', $workorder->estimated_cost) }}" min="0" step="0.01">
            {!! $errors->first('estimated_cost', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- Actual Duration -->
    <div class="form-group {{ $errors->has('actual_duration') ? ' has-error' : '' }}">
        <label for="actual_duration" class="col-md-3 control-label">
            Actual Duration (minutes)
        </label>
        <div class="col-md-7 col-sm-12">
            <input type="number" name="actual_duration" id="actual_duration" class="form-control" 
                   value="{{ old('actual_duration', $workorder->actual_duration) }}" min="1" step="1">
            {!! $errors->first('actual_duration', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- Actual Cost -->
    <div class="form-group {{ $errors->has('actual_cost') ? ' has-error' : '' }}">
        <label for="actual_cost" class="col-md-3 control-label">
            Actual Cost
        </label>
        <div class="col-md-7 col-sm-12">
            <input type="number" name="actual_cost" id="actual_cost" class="form-control" 
                   value="{{ old('actual_cost', $workorder->actual_cost) }}" min="0" step="0.01">
            {!! $errors->first('actual_cost', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- Completed By -->
    <div class="form-group {{ $errors->has('completed_by') ? ' has-error' : '' }}">
        <label for="completed_by" class="col-md-3 control-label">
            Completed By
        </label>
        <div class="col-md-7 col-sm-12">
            <select name="completed_by" id="completed_by" class="form-control select2">
                <option value="">Select User</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('completed_by', $workorder->completed_by) == $user->id ? 'selected' : '' }}>
                        {{ $user->getFullNameAttribute() }}
                    </option>
                @endforeach
            </select>
            {!! $errors->first('completed_by', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- Work Performed -->
    <div class="form-group {{ $errors->has('work_performed') ? ' has-error' : '' }}">
        <label for="work_performed" class="col-md-3 control-label">
            Work Performed
        </label>
        <div class="col-md-7 col-sm-12">
            <textarea name="work_performed" id="work_performed" class="form-control" rows="3">{{ old('work_performed', $workorder->work_performed) }}</textarea>
            {!! $errors->first('work_performed', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- Parts Used -->
    <div class="form-group {{ $errors->has('parts_used') ? ' has-error' : '' }}">
        <label for="parts_used" class="col-md-3 control-label">
            Parts Used
        </label>
        <div class="col-md-7 col-sm-12">
            <textarea name="parts_used" id="parts_used" class="form-control" rows="3">{{ old('parts_used', $workorder->parts_used) }}</textarea>
            {!! $errors->first('parts_used', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- Notes -->
    <div class="form-group {{ $errors->has('notes') ? ' has-error' : '' }}">
        <label for="notes" class="col-md-3 control-label">
            Notes
        </label>
        <div class="col-md-7 col-sm-12">
            <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes', $workorder->notes) }}</textarea>
            {!! $errors->first('notes', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>
@stop

@section('moar_scripts')
<script>
$(function() {
    $('.select2').select2({
        placeholder: 'Select an option',
        allowClear: true
    });
});
</script>
@stop

