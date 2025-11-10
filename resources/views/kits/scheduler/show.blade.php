@extends('layouts.default')

@section('title')
    Maintenance Schedule: {{ $schedule->title }}
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <div class="box-heading">
                    <h2 class="box-title">{{ $schedule->title }}</h2>
                </div>
                <div class="box-tools pull-right">
                    @can('update', $schedule)
                        <a href="{{ route('maintenance.scheduler.edit', $schedule) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    @endcan
                    <a href="{{ route('maintenance.scheduler.index') }}" class="btn btn-sm btn-default">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <td><strong>Title:</strong></td>
                                    <td>{{ $schedule->title }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Asset:</strong></td>
                                    <td>
                                        @if($schedule->asset)
                                            <a href="{{ route('hardware.show', $schedule->asset->id) }}">
                                                {{ $schedule->asset->asset_tag }} - {{ $schedule->asset->name }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Frequency:</strong></td>
                                    <td>
                                        <span class="label label-primary">
                                            {{ ucfirst($schedule->frequency) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="label label-{{ $schedule->is_active ? 'success' : 'danger' }}">
                                            {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Assigned To:</strong></td>
                                    <td>{{ $schedule->assignedUser ? $schedule->assignedUser->getFullNameAttribute() : 'Unassigned' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Supplier:</strong></td>
                                    <td>{{ $schedule->supplier ? $schedule->supplier->name : 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <td><strong>Predefined Kit:</strong></td>
                                    <td>{{ $schedule->kit ? $schedule->kit->name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Start Date:</strong></td>
                                    <td>{{ $schedule->start_date ? $schedule->start_date->format('Y-m-d') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>End Date:</strong></td>
                                    <td>{{ $schedule->end_date ? $schedule->end_date->format('Y-m-d') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Next Due Date:</strong></td>
                                    <td>
                                        @if($schedule->next_due_date)
                                            <span class="label label-{{ $schedule->next_due_date->isPast() ? 'danger' : 'info' }}">
                                                {{ $schedule->next_due_date->format('Y-m-d') }}
                                            </span>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Last Completed:</strong></td>
                                    <td>{{ $schedule->last_completed_at ? $schedule->last_completed_at->format('Y-m-d H:i') : 'Never' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Estimated Duration:</strong></td>
                                    <td>{{ $schedule->estimated_duration ? $schedule->estimated_duration . ' minutes' : 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <h4>Description</h4>
                        <p>{{ $schedule->description ?: 'No description provided.' }}</p>
                        <p>
                            <a href="{{ route('maintenance.scheduler.activity', $schedule) }}" class="btn btn-xs btn-info">
                                <i class="fas fa-stream"></i> View Activity Log
                            </a>
                        </p>
                    </div>
                </div>

                @if($schedule->notes)
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Notes</h4>
                            <p>{{ $schedule->notes }}</p>
                        </div>
                    </div>
                @endif

                <!-- Work Orders Generated from this Schedule -->
                @if($schedule->workOrders && $schedule->workOrders->count() > 0)
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Work Orders ({{ $schedule->workOrders->count() }})</h4>
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Work Order #</th>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Scheduled Start</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($schedule->workOrders as $workOrder)
                                        <tr>
                                            <td>{{ $workOrder->work_order_number }}</td>
                                            <td>{{ $workOrder->title }}</td>
                                            <td>
                                                <span class="label label-{{ $workOrder->status == 'completed' ? 'success' : ($workOrder->status == 'in_progress' ? 'primary' : 'warning') }}">
                                                    {{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="label label-{{ $workOrder->priority == 'critical' ? 'danger' : ($workOrder->priority == 'high' ? 'warning' : 'default') }}">
                                                    {{ ucfirst($workOrder->priority) }}
                                                </span>
                                            </td>
                                            <td>{{ $workOrder->scheduled_start ? $workOrder->scheduled_start->format('Y-m-d H:i') : 'N/A' }}</td>
                                            <td>
                                                <a href="{{ route('maintenance.workorders.show', $workOrder) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Maintenance History -->
                @if($schedule->maintenanceHistory && $schedule->maintenanceHistory->count() > 0)
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Maintenance History ({{ $schedule->maintenanceHistory->count() }})</h4>
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Completed Date</th>
                                        <th>Performed By</th>
                                        <th>Duration</th>
                                        <th>Cost</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($schedule->maintenanceHistory as $history)
                                        <tr>
                                            <td>{{ $history->work_order ? $history->work_order->title : 'N/A' }}</td>
                                            <td>{{ $history->type ? ucfirst($history->type) : 'N/A' }}</td>
                                            <td>{{ $history->completed_date ? $history->completed_date->format('Y-m-d H:i') : 'N/A' }}</td>
                                            <td>{{ $history->performed_by_name ?: 'N/A' }}</td>
                                            <td>{{ $history->duration ? $history->duration . ' min' : 'N/A' }}</td>
                                            <td>{{ $history->cost ? '£' . number_format($history->cost, 2) : 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <div class="box-footer">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Created At:</strong> {{ $schedule->created_at->format('Y-m-d H:i:s') }}
                    </div>
                    <div class="col-md-6 text-right">
                        <strong>Updated At:</strong> {{ $schedule->updated_at->format('Y-m-d H:i:s') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
