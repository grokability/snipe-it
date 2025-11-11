@extends('layouts.default')

@section('title')
    Work Order: {{ $workorder->work_order_number }}
@endsection

@section('header_right')
<style>
.field-required {
    border: 2px solid #dd4b39 !important;
    background-color: #ffe6e6 !important;
}
.field-group {
    margin-bottom: 15px;
}
.inline-edit-field {
    width: 100%;
}
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <div class="box-heading">
                    <h2 class="box-title">{{ $workorder->work_order_number }}: {{ $workorder->title }}</h2>
                </div>
                <div class="box-tools pull-right">
                    <a href="{{ route('maintenance.workorders.index') }}" class="btn btn-sm btn-default">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

            <form id="workOrderForm" method="POST" action="{{ route('maintenance.workorders.update', $workorder) }}">
                @csrf
                @method('PUT')
                
                <input type="hidden" name="asset_id" value="{{ $workorder->asset_id }}">
                <input type="hidden" name="maintenance_schedule_id" value="{{ $workorder->maintenance_schedule_id }}">
                <input type="hidden" name="predefined_kit_id" value="{{ $workorder->predefined_kit_id }}">
                <input type="hidden" name="title" value="{{ $workorder->title }}">
                <input type="hidden" name="description" value="{{ $workorder->description }}">
                <input type="hidden" name="type" value="{{ $workorder->type }}">
                <input type="hidden" name="priority" value="{{ $workorder->priority }}">
                <input type="hidden" name="status" value="{{ $workorder->status }}">
                <input type="hidden" name="assigned_to" value="{{ $workorder->assigned_to }}">
                <input type="hidden" name="supplier_id" value="{{ $workorder->supplier_id }}">
                <input type="hidden" name="scheduled_start" value="{{ $workorder->scheduled_start }}">
                <input type="hidden" name="scheduled_end" value="{{ $workorder->scheduled_end }}">
                <input type="hidden" name="estimated_duration" value="{{ $workorder->estimated_duration }}">
                <input type="hidden" name="estimated_cost" value="{{ $workorder->estimated_cost }}">

            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <td><strong>Work Order Number:</strong></td>
                                    <td>{{ $workorder->work_order_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Title:</strong></td>
                                    <td>{{ $workorder->title }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Asset:</strong></td>
                                    <td>
                                        @if($workorder->asset)
                                            <a href="{{ route('hardware.show', $workorder->asset->id) }}">
                                                {{ $workorder->asset->asset_tag }} - {{ $workorder->asset->name }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Type:</strong></td>
                                    <td>
                                        <span class="label label-{{ $workorder->type == 'emergency' ? 'danger' : ($workorder->type == 'corrective' ? 'warning' : 'primary') }}">
                                            {{ ucfirst($workorder->type) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Priority:</strong></td>
                                    <td>
                                        <span class="label label-{{ $workorder->priority == 'critical' ? 'danger' : ($workorder->priority == 'high' ? 'warning' : ($workorder->priority == 'medium' ? 'info' : 'default')) }}">
                                            {{ ucfirst($workorder->priority) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="label label-{{ $workorder->status == 'completed' ? 'success' : ($workorder->status == 'in_progress' ? 'primary' : ($workorder->status == 'cancelled' ? 'danger' : 'warning')) }}">
                                            {{ ucfirst(str_replace('_', ' ', $workorder->status)) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <td><strong>Assigned To:</strong></td>
                                    <td>{{ $workorder->assignedUser ? $workorder->assignedUser->getFullNameAttribute() : 'Unassigned' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Supplier:</strong></td>
                                    <td>{{ $workorder->supplier ? $workorder->supplier->name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Maintenance Schedule:</strong></td>
                                    <td>
                                        @if($workorder->maintenanceSchedule)
                                            {{ $workorder->maintenanceSchedule->title }}
                                        @else
                                            Ad-hoc
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Predefined Kit:</strong></td>
                                    <td>{{ $workorder->kit ? $workorder->kit->name : 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <h4>Description</h4>
                        <p>{{ $workorder->description ?: 'No description provided.' }}</p>
                        <p>
                            <a href="{{ route('maintenance.workorders.activity', $workorder) }}" class="btn btn-xs btn-info">
                                <i class="fas fa-stream"></i> View Activity Log
                            </a>
                        </p>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-12">
                        <h4>Completion Details <small class="text-muted">(Edit fields directly below)</small></h4>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <!-- Work Performed -->
                        <div class="form-group field-group">
                            <label>
                                Work Performed <span class="text-danger">*</span>
                            </label>
                            <textarea 
                                name="work_performed" 
                                class="form-control inline-edit-field" 
                                rows="4" 
                                placeholder="Describe the work that was performed..."
                            >{{ $workorder->work_performed }}</textarea>
                        </div>

                        <!-- Parts Used -->
                        <div class="form-group field-group">
                            <label>Parts Used</label>
                            <textarea 
                                name="parts_used" 
                                class="form-control inline-edit-field" 
                                rows="3" 
                                placeholder="List any parts or materials used..."
                            >{{ $workorder->parts_used }}</textarea>
                        </div>

                        <!-- Notes -->
                        <div class="form-group field-group">
                            <label>Additional Notes</label>
                            <textarea 
                                name="notes" 
                                class="form-control inline-edit-field" 
                                rows="3" 
                                placeholder="Any additional information..."
                            >{{ $workorder->notes }}</textarea>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <!-- Actual End Time -->
                        <div class="form-group field-group">
                            <label>
                                Completion Date & Time <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="datetime-local" 
                                name="actual_end" 
                                class="form-control inline-edit-field" 
                                value="{{ $workorder->actual_end ? $workorder->actual_end->format('Y-m-d\\TH:i') : '' }}"
                            >
                        </div>

                        <!-- Actual Start Time -->
                        <div class="form-group field-group">
                            <label>Actual Start Time</label>
                            <input 
                                type="datetime-local" 
                                name="actual_start" 
                                class="form-control inline-edit-field" 
                                value="{{ $workorder->actual_start ? $workorder->actual_start->format('Y-m-d\TH:i') : '' }}"
                            >
                        </div>

                        <!-- Actual Duration -->
                        <div class="form-group field-group">
                            <label>
                                Duration (minutes) <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="number" 
                                name="actual_duration" 
                                class="form-control inline-edit-field" 
                                value="{{ $workorder->actual_duration }}" 
                                min="1" 
                                step="1" 
                                placeholder="Enter duration in minutes"
                            >
                        </div>

                        <!-- Actual Cost -->
                        <div class="form-group field-group">
                            <label>Actual Cost (£)</label>
                            <input 
                                type="number" 
                                name="actual_cost" 
                                class="form-control inline-edit-field" 
                                value="{{ $workorder->actual_cost }}" 
                                step="0.01" 
                                min="0" 
                                placeholder="0.00"
                            >
                        </div>

                        <!-- Completed By -->
                        <div class="form-group field-group">
                            <label>
                                Completed By <span class="text-danger">*</span>
                            </label>
                            <select 
                                name="completed_by" 
                                class="form-control inline-edit-field"
                            >
                                <option value="">Select User</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ ($workorder->completed_by == $user->id || (!$workorder->completed_by && Auth::id() == $user->id)) ? 'selected' : '' }}>
                                        {{ $user->first_name }} {{ $user->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Note:</strong> Fill in the required fields (marked with <span class="text-danger">*</span>) before marking this work order as completed.
                            Red-bordered fields indicate missing required information.
                        </div>
                    </div>
                </div>

                @if($workorder->maintenanceHistory)
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Maintenance History</h4>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Performed At</th>
                                        <th>Performed By</th>
                                        <th>Duration</th>
                                        <th>Cost</th>
                                        <th>Outcome</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <a href="{{ route('maintenance.history.show', $workorder->maintenanceHistory) }}">
                                                {{ $workorder->maintenanceHistory->workOrder ? $workorder->maintenanceHistory->workOrder->title : 'N/A' }}
                                            </a>
                                        </td>
                                        <td>{{ $workorder->maintenanceHistory->performed_at ? $workorder->maintenanceHistory->performed_at->format('Y-m-d H:i') : 'N/A' }}</td>
                                        <td>{{ $workorder->maintenanceHistory->performed_by_name ?: ($workorder->maintenanceHistory->performedByUser ? $workorder->maintenanceHistory->performedByUser->getFullNameAttribute() : 'N/A') }}</td>
                                        <td>{{ $workorder->maintenanceHistory->duration ? $workorder->maintenanceHistory->duration . ' minutes' : 'N/A' }}</td>
                                        <td>{{ $workorder->maintenanceHistory->cost ? '£' . number_format($workorder->maintenanceHistory->cost, 2) : 'N/A' }}</td>
                                        <td>
                                            <span class="label label-{{ $workorder->maintenanceHistory->outcome == 'success' ? 'success' : 'danger' }}">
                                                {{ ucfirst($workorder->maintenanceHistory->outcome) }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <div class="box-footer">
                @can('update', $workorder)
                    @if($workorder->status !== 'completed' && $workorder->status !== 'cancelled')
                        <!-- Save Changes Button -->
                        <button type="button" class="btn btn-primary" onclick="saveChanges()">
                            <i class="fas fa-save"></i> Save Changes
                        </button>

                        <!-- Complete Work Order Button -->
                        <button type="button" class="btn btn-success" onclick="checkAndComplete()">
                            <i class="fas fa-check"></i> Mark as Completed
                        </button>

                        <!-- Change Status Dropdown -->
                        <div class="btn-group" style="display: inline-block; margin-left: 5px;">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-exchange-alt"></i> Change Status <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                @if($workorder->status !== 'pending')
                                    <li>
                                        <a href="#" onclick="event.preventDefault(); document.getElementById('status-pending-form').submit();">
                                            <i class="fas fa-clock"></i> Mark as Pending
                                        </a>
                                    </li>
                                @endif
                                @if($workorder->status !== 'in_progress')
                                    <li>
                                        <a href="#" onclick="event.preventDefault(); document.getElementById('status-in-progress-form').submit();">
                                            <i class="fas fa-play"></i> Mark as In Progress
                                        </a>
                                    </li>
                                @endif
                                @if($workorder->status !== 'on_hold')
                                    <li>
                                        <a href="#" onclick="event.preventDefault(); document.getElementById('status-on-hold-form').submit();">
                                            <i class="fas fa-pause"></i> Mark as On Hold
                                        </a>
                                    </li>
                                @endif
                                <li class="divider"></li>
                                <li>
                                    <a href="#" onclick="event.preventDefault(); if(confirm('Are you sure you want to cancel this work order?')) document.getElementById('status-cancelled-form').submit();">
                                        <i class="fas fa-ban"></i> Mark as Cancelled
                                    </a>
                                </li>
                            </ul>
                        </div>
                    @endif
                @endcan
            </div>
            </form>

            <!-- Delete Form - Outside main form -->
            @can('delete', $workorder)
                <form method="POST" action="{{ route('maintenance.workorders.destroy', $workorder) }}" style="display: inline-block; margin-left: 10px;" onsubmit="return confirm('Are you sure you want to delete this work order?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            @endcan

            <!-- Hidden forms for status changes -->
            <form id="status-pending-form" method="POST" action="{{ route('maintenance.workorders.updateStatus', $workorder) }}" style="display: none;">
                @csrf
                <input type="hidden" name="status" value="pending">
            </form>
            <form id="status-in-progress-form" method="POST" action="{{ route('maintenance.workorders.updateStatus', $workorder) }}" style="display: none;">
                @csrf
                <input type="hidden" name="status" value="in_progress">
            </form>
            <form id="status-on-hold-form" method="POST" action="{{ route('maintenance.workorders.updateStatus', $workorder) }}" style="display: none;">
                @csrf
                <input type="hidden" name="status" value="on_hold">
            </form>
            <form id="status-cancelled-form" method="POST" action="{{ route('maintenance.workorders.updateStatus', $workorder) }}" style="display: none;">
                @csrf
                <input type="hidden" name="status" value="cancelled">
            </form>
        </div>
    </div>
</div>

<!-- Complete Confirmation Modal -->
<div class="modal fade" id="completeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Missing Required Fields</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Please fill in the following required fields before completing this work order:</strong>
                </div>
                <ul id="missingFieldsList" class="list-unstyled">
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Component Checkout Modal -->
<div class="modal fade" id="componentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fas fa-tools"></i> Component Usage</h4>
            </div>
            <div class="modal-body">
                <p><strong>Did you use any components/spare parts for this work order?</strong></p>
                
                <div class="form-group">
                    <label for="component_id">Select Component:</label>
                    <select id="component_id" class="form-control">
                        <option value="">Select a component (optional)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="component_qty">Quantity:</label>
                    <input type="number" id="component_qty" class="form-control" min="1" value="1">
                </div>
                
                <div id="componentError" class="alert alert-danger" style="display: none;">
                    <i class="fas fa-exclamation-circle"></i> <span id="componentErrorMsg"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="completeWithoutComponent">
                    <i class="fas fa-check"></i> Complete Without Component
                </button>
                <button type="button" class="btn btn-success" id="completeWithComponent">
                    <i class="fas fa-check-double"></i> Checkout & Complete
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Add quick date buttons for datetime-local inputs
document.addEventListener('DOMContentLoaded', function() {
    // Get all datetime-local inputs
    var dateTimeInputs = document.querySelectorAll('input[type="datetime-local"]');
    
    dateTimeInputs.forEach(function(input) {
        // Create a wrapper div for buttons
        var wrapper = document.createElement('div');
        wrapper.style.marginTop = '5px';
        
        // Create "Now" button
        var nowBtn = document.createElement('button');
        nowBtn.type = 'button';
        nowBtn.className = 'btn btn-xs btn-default';
        nowBtn.innerHTML = '<i class="fas fa-clock"></i> Now';
        nowBtn.style.marginRight = '5px';
        nowBtn.onclick = function() {
            var now = new Date();
            var year = now.getFullYear();
            var month = String(now.getMonth() + 1).padStart(2, '0');
            var day = String(now.getDate()).padStart(2, '0');
            var hours = String(now.getHours()).padStart(2, '0');
            var minutes = String(now.getMinutes()).padStart(2, '0');
            input.value = year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
            input.blur(); // Close the calendar
        };
        
        // Create "Clear" button
        var clearBtn = document.createElement('button');
        clearBtn.type = 'button';
        clearBtn.className = 'btn btn-xs btn-default';
        clearBtn.innerHTML = '<i class="fas fa-times"></i> Clear';
        clearBtn.onclick = function() {
            input.value = '';
            input.blur(); // Close the calendar
        };
        
        wrapper.appendChild(nowBtn);
        wrapper.appendChild(clearBtn);
        
        // Insert after the input
        input.parentNode.insertBefore(wrapper, input.nextSibling);
        
        // Auto-close calendar when a value is selected
        input.addEventListener('change', function() {
            setTimeout(function() {
                input.blur();
            }, 100);
        });
    });
});

function saveChanges() {
    document.getElementById('workOrderForm').submit();
}

function checkAndComplete() {
    var missingFields = [];
    
    // Clear previous highlights
    ['textarea[name="work_performed"]', 'input[name="actual_end"]', 'input[name="actual_duration"]', 'select[name="completed_by"]']
        .forEach(function(sel){
            var el = document.querySelector(sel);
            if (el) el.classList.remove('field-required');
        });
    
    // Check work_performed
    var workPerformed = document.querySelector('textarea[name="work_performed"]').value.trim();
    if (!workPerformed) {
        missingFields.push('Work Performed - Describe what work was done');
        var elWP = document.querySelector('textarea[name="work_performed"]');
        if (elWP) elWP.classList.add('field-required');
    }
    
    // Check actual_end
    var actualEnd = document.querySelector('input[name="actual_end"]').value;
    if (!actualEnd) {
        missingFields.push('Completion Date & Time - When was the work completed?');
        var elEnd = document.querySelector('input[name="actual_end"]');
        if (elEnd) elEnd.classList.add('field-required');
    }
    
    // Check actual_duration
    var actualDuration = document.querySelector('input[name="actual_duration"]').value;
    if (!actualDuration || actualDuration <= 0) {
        missingFields.push('Duration - How long did the work take (in minutes)?');
        var elDur = document.querySelector('input[name="actual_duration"]');
        if (elDur) elDur.classList.add('field-required');
    }
    
    // Check completed_by
    var completedBy = document.querySelector('select[name="completed_by"]').value;
    if (!completedBy) {
        missingFields.push('Completed By - Who performed this work?');
        var elCB = document.querySelector('select[name="completed_by"]');
        if (elCB) elCB.classList.add('field-required');
    }
    
    if (missingFields.length > 0) {
        // Show modal with missing fields
        var listHtml = '';
        missingFields.forEach(function(field) {
            listHtml += '<li class="text-danger"><i class="fas fa-times-circle"></i> <strong>' + field + '</strong></li>';
        });
        document.getElementById('missingFieldsList').innerHTML = listHtml;
        $('#completeModal').modal('show');
        return false;
    }
    
    // All required fields are filled, show component modal
    $('#componentModal').modal('show');
}

function completeWorkOrder() {
    var form = document.getElementById('workOrderForm');
    var statusInput = form.querySelector('input[name="status"]');
    statusInput.value = 'completed';
    form.submit();
}
</script>

@endsection

@section('moar_scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 for component dropdown
    $('#component_id').select2({
        placeholder: 'Type to search components...',
        allowClear: true,
        dropdownParent: $('#componentModal'),
        minimumInputLength: 0,
        ajax: {
            url: '{{ route('maintenance.workorders.getComponents') }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    search: params.term,
                    limit: 50
                };
            },
            processResults: function (data) {
                console.log('Component data:', data);
                return data;
            },
            cache: true
        }
    });

    // Handle "Complete Without Component" button
    $('#completeWithoutComponent').click(function() {
        $('#componentModal').modal('hide');
        completeWorkOrder();
    });

    // Handle "Checkout & Complete" button
    $('#completeWithComponent').click(function() {
        var componentId = $('#component_id').val();
        var qty = $('#component_qty').val();
        
        // Hide previous error
        $('#componentError').hide();
        
        // Validation
        if (!componentId) {
            $('#componentErrorMsg').text('Please select a component or click "Complete Without Component"');
            $('#componentError').show();
            return;
        }
        
        if (!qty || qty < 1) {
            $('#componentErrorMsg').text('Please enter a valid quantity (minimum 1)');
            $('#componentError').show();
            return;
        }

        // Get selected component data
        var selectedData = $('#component_id').select2('data')[0];
        if (selectedData && qty > selectedData.qty_available) {
            $('#componentErrorMsg').text('Insufficient quantity available. Only ' + selectedData.qty_available + ' available.');
            $('#componentError').show();
            return;
        }

        var $btn = $(this);
        var originalText = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        $('#completeWithoutComponent').prop('disabled', true);

        // Make AJAX call to checkout component via our custom route
        $.ajax({
            url: '{{ route('maintenance.workorders.checkoutComponent', $workorder) }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            data: JSON.stringify({
                component_id: componentId,
                checkout_qty: parseInt(qty)
            }),
            success: function(response) {
                if (response.status === 'success') {
                    // Checkout successful, now complete the work order
                    $('#componentModal').modal('hide');
                    completeWorkOrder();
                } else {
                    // Checkout failed
                    $btn.prop('disabled', false).html(originalText);
                    $('#completeWithoutComponent').prop('disabled', false);
                    $('#componentErrorMsg').text('Checkout failed: ' + (response.message || 'Unknown error'));
                    $('#componentError').show();
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(originalText);
                $('#completeWithoutComponent').prop('disabled', false);
                
                var errorMsg = 'Checkout failed';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg += ': ' + xhr.responseJSON.message;
                } else {
                    errorMsg += ': ' + xhr.statusText;
                }
                
                console.error('Checkout error:', xhr);
                $('#componentErrorMsg').text(errorMsg);
                $('#componentError').show();
            }
        });
    });

    // Reset modal when closed
    $('#componentModal').on('hidden.bs.modal', function () {
        $('#component_id').val(null).trigger('change');
        $('#component_qty').val(1);
        $('#componentError').hide();
        $('#completeWithComponent').prop('disabled', false).html('<i class="fas fa-check-double"></i> Checkout & Complete');
        $('#completeWithoutComponent').prop('disabled', false);
    });
});
</script>
@endsection

