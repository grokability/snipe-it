@extends('layouts.default')

@section('title')
    Create Work Order
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="box box-default">
            <div class="box-header with-border">
                <h2 class="box-title">Create New Work Order</h2>
            </div>

            <form method="POST" action="{{ route('maintenance.workorders.store') }}" class="form-horizontal">
                @csrf
                
                <div class="box-body">
                    <!-- Title -->
                    <div class="form-group ">
                        <label for="title" class="col-md-3 control-label">
                            Title <span class="text-danger">*</span>
                        </label>
                        <div class="col-md-9">
                            <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" required>
                            
                                
                            
                        </div>
                    </div>

                    <!-- Asset -->
                    <div class="form-group ">
                        <label for="asset_id" class="col-md-3 control-label">
                            Asset <span class="text-danger">*</span>
                        </label>
                        <div class="col-md-9">
                            <select name="asset_id" id="asset_id" class="form-control select2" required>
                                <option value="">Select Asset</option>
                                @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
                                        {{ $asset->asset_tag }} - {{ $asset->name }}
                                    </option>
                                @endforeach
                            </select>
                            
                                
                            
                        </div>
                    </div>

                    <!-- Maintenance Schedule (Optional) -->
                    <div class="form-group ">
                        <label for="maintenance_schedule_id" class="col-md-3 control-label">
                            From Schedule
                        </label>
                        <div class="col-md-9">
                            <select name="maintenance_schedule_id" id="maintenance_schedule_id" class="form-control select2">
                                <option value="">None (Ad-hoc)</option>
                                @foreach($schedules as $schedule)
                                    <option value="{{ $schedule->id }}" {{ old('maintenance_schedule_id') == $schedule->id ? 'selected' : '' }}>
                                        {{ $schedule->title }}
                                    </option>
                                @endforeach
                            </select>
                            
                                
                            
                        </div>
                    </div>

                    <!-- Predefined Kit (Optional) -->
                    <div class="form-group ">
                        <label for="predefined_kit_id" class="col-md-3 control-label">
                            Predefined Kit
                        </label>
                        <div class="col-md-9">
                            <select name="predefined_kit_id" id="predefined_kit_id" class="form-control select2">
                                <option value="">None</option>
                                @foreach($kits as $kit)
                                    <option value="{{ $kit->id }}" {{ old('predefined_kit_id') == $kit->id ? 'selected' : '' }}>
                                        {{ $kit->name }}
                                    </option>
                                @endforeach
                            </select>
                            
                                
                            
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="form-group ">
                        <label for="description" class="col-md-3 control-label">
                            Description
                        </label>
                        <div class="col-md-9">
                            <textarea name="description" id="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                            
                                
                            
                        </div>
                    </div>

                    <!-- Type -->
                    <div class="form-group ">
                        <label for="type" class="col-md-3 control-label">
                            Type <span class="text-danger">*</span>
                        </label>
                        <div class="col-md-9">
                            <select name="type" id="type" class="form-control" required>
                                <option value="">Select Type</option>
                                <option value="preventive" {{ old('type', 'preventive') == 'preventive' ? 'selected' : '' }}>Preventive</option>
                                <option value="corrective" {{ old('type') == 'corrective' ? 'selected' : '' }}>Corrective</option>
                                <option value="inspection" {{ old('type') == 'inspection' ? 'selected' : '' }}>Inspection</option>
                                <option value="emergency" {{ old('type') == 'emergency' ? 'selected' : '' }}>Emergency</option>
                            </select>
                            
                                
                            
                        </div>
                    </div>

                    <!-- Priority -->
                    <div class="form-group ">
                        <label for="priority" class="col-md-3 control-label">
                            Priority <span class="text-danger">*</span>
                        </label>
                        <div class="col-md-9">
                            <select name="priority" id="priority" class="form-control" required>
                                <option value="">Select Priority</option>
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                            
                                
                            
                        </div>
                    </div>

                    <!-- Assigned To -->
                    <div class="form-group ">
                        <label for="assigned_to" class="col-md-3 control-label">
                            Assigned To
                        </label>
                        <div class="col-md-9">
                            <select name="assigned_to" id="assigned_to" class="form-control select2">
                                <option value="">Unassigned</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                        {{ $user->getFullNameAttribute() }}
                                    </option>
                                @endforeach
                            </select>
                            
                                
                            
                        </div>
                    </div>

                    <!-- Scheduled Start -->
                    <div class="form-group ">
                        <label for="scheduled_start" class="col-md-3 control-label">
                            Scheduled Start
                        </label>
                        <div class="col-md-9">
                            <input type="datetime-local" name="scheduled_start" id="scheduled_start" class="form-control" 
                                   value="{{ old('scheduled_start') }}">
                            
                                
                            
                        </div>
                    </div>

                    <!-- Scheduled End -->
                    <div class="form-group ">
                        <label for="scheduled_end" class="col-md-3 control-label">
                            Scheduled End
                        </label>
                        <div class="col-md-9">
                            <input type="datetime-local" name="scheduled_end" id="scheduled_end" class="form-control" 
                                   value="{{ old('scheduled_end') }}">
                            
                                
                            
                        </div>
                    </div>

                    <!-- Estimated Duration (minutes) -->
                    <div class="form-group ">
                        <label for="estimated_duration" class="col-md-3 control-label">
                            Estimated Duration (minutes)
                        </label>
                        <div class="col-md-9">
                            <input type="number" name="estimated_duration" id="estimated_duration" class="form-control" 
                                   value="{{ old('estimated_duration') }}" min="1">
                            
                                
                            
                        </div>
                    </div>

                    <!-- Estimated Cost -->
                    <div class="form-group ">
                        <label for="estimated_cost" class="col-md-3 control-label">
                            Estimated Cost
                        </label>
                        <div class="col-md-9">
                            <input type="number" name="estimated_cost" id="estimated_cost" class="form-control" 
                                   value="{{ old('estimated_cost') }}" min="0" step="0.01">
                            
                                
                            
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="form-group ">
                        <label for="notes" class="col-md-3 control-label">
                            Notes
                        </label>
                        <div class="col-md-9">
                            <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                            
                                
                            
                        </div>
                    </div>
                </div>

                <div class="box-footer">
                    <a href="{{ route('maintenance.workorders.index') }}" class="btn btn-default">Cancel</a>
                    <button type="submit" class="btn btn-primary pull-right">
                        <i class="fas fa-check"></i> Create Work Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('moar_scripts')
<script>
$(function() {
    $('.select2').select2({
        placeholder: 'Select an option',
        allowClear: true
    });
});
</script>
@endsection
