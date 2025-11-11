@extends('layouts.default')

@section('title')
    Overdue Maintenance Schedules
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h2 class="box-title">
                    <i class="fas fa-exclamation-triangle"></i> Overdue Maintenance Schedules
                </h2>
                <div class="box-tools pull-right">
                    <a href="{{ route('maintenance.scheduler.index') }}" class="btn btn-default btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to All Schedules
                    </a>
                </div>
            </div>

            <div class="box-body">
                @if($schedules->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Schedule Name</th>
                                    <th>Asset</th>
                                    <th>Frequency</th>
                                    <th>Due Date</th>
                                    <th>Priority</th>
                                    <th>Assigned To</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($schedules as $schedule)
                                    <tr class="danger">
                                        <td>
                                            <strong>{{ $schedule->title }}</strong>
                                            @if($schedule->description)
                                                <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($schedule->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($schedule->asset)
                                                <a href="{{ route('hardware.show', $schedule->asset->id) }}">
                                                    {{ $schedule->asset->name ?? 'N/A' }}
                                                    <br><small class="text-muted">{{ $schedule->asset->asset_tag ?? '' }}</small>
                                                </a>
                                            @else
                                                <span class="text-muted">No Asset</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="label label-default">
                                                {{ ucfirst($schedule->frequency) }}
                                                @if($schedule->frequency_interval > 1)
                                                    ({{ $schedule->frequency_interval }})
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-danger">
                                                <i class="fas fa-exclamation-circle"></i>
                                                {{ $schedule->next_due_date ? $schedule->next_due_date->format('Y-m-d') : 'N/A' }}
                                                @if($schedule->next_due_date)
                                                    <br><small>{{ $schedule->next_due_date->diffForHumans() }}</small>
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $priorityClass = match($schedule->priority) {
                                                    'critical' => 'danger',
                                                    'high' => 'warning',
                                                    'medium' => 'info',
                                                    'low' => 'default',
                                                    default => 'default'
                                                };
                                            @endphp
                                            <span class="label label-{{ $priorityClass }}">
                                                {{ ucfirst($schedule->priority) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($schedule->assignedUser)
                                                {{ $schedule->assignedUser->first_name }} {{ $schedule->assignedUser->last_name }}
                                            @else
                                                <span class="text-muted">Unassigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $statusClass = match($schedule->status) {
                                                    'active' => 'success',
                                                    'paused' => 'warning',
                                                    'completed' => 'primary',
                                                    'cancelled' => 'default',
                                                    default => 'default'
                                                };
                                            @endphp
                                            <span class="label label-{{ $statusClass }}">
                                                {{ ucfirst($schedule->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('maintenance.scheduler.show', $schedule->id) }}" 
                                               class="btn btn-sm btn-primary" 
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if(auth()->user()->hasAccess('kits.edit'))
                                                <a href="{{ route('maintenance.scheduler.edit', $schedule->id) }}" 
                                                   class="btn btn-sm btn-warning" 
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-md-12 text-center">
                            {{ $schedules->links() }}
                        </div>
                    </div>
                @else
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        No overdue maintenance schedules. Great job!
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
