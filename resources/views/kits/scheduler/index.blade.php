@extends('layouts.default')

@section('title')
    Maintenance Scheduler
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h2 class="box-title">Maintenance Schedules</h2>
                <div class="box-tools pull-right">
                    @if(auth()->user()->hasAccess('kits.create'))
                        <a href="{{ route('maintenance.scheduler.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Create Schedule
                        </a>
                    @endif
                </div>
            </div>

            <div class="box-body">
                <!-- Statistics Cards - Clickable Filters -->
                <div class="row">
                    <div class="col-md-3">
                        <a href="{{ route('maintenance.scheduler.index', ['filter' => 'overdue']) }}" 
                           class="info-box-link" 
                           style="display: block; color: inherit; text-decoration: none;">
                            <div class="info-box {{ request('filter') == 'overdue' ? 'info-box-active' : '' }}" 
                                 style="cursor: pointer; {{ request('filter') == 'overdue' ? 'box-shadow: 0 0 10px rgba(221, 75, 57, 0.5);' : '' }}">
                                <span class="info-box-icon bg-red"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Overdue</span>
                                    <span class="info-box-number">{{ $overdueCount }}</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('maintenance.scheduler.index', ['filter' => 'upcoming']) }}" 
                           class="info-box-link" 
                           style="display: block; color: inherit; text-decoration: none;">
                            <div class="info-box {{ request('filter') == 'upcoming' ? 'info-box-active' : '' }}" 
                                 style="cursor: pointer; {{ request('filter') == 'upcoming' ? 'box-shadow: 0 0 10px rgba(243, 156, 18, 0.5);' : '' }}">
                                <span class="info-box-icon bg-yellow"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Upcoming</span>
                                    <span class="info-box-number">{{ $upcomingCount }}</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('maintenance.scheduler.index', ['status' => 'active']) }}" 
                           class="info-box-link" 
                           style="display: block; color: inherit; text-decoration: none;">
                            <div class="info-box {{ request('status') == 'active' ? 'info-box-active' : '' }}" 
                                 style="cursor: pointer; {{ request('status') == 'active' ? 'box-shadow: 0 0 10px rgba(0, 166, 90, 0.5);' : '' }}">
                                <span class="info-box-icon bg-green"><i class="fas fa-play-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Active</span>
                                    <span class="info-box-number">{{ $activeCount }}</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('maintenance.scheduler.index', ['status' => 'paused']) }}" 
                           class="info-box-link" 
                           style="display: block; color: inherit; text-decoration: none;">
                            <div class="info-box {{ request('status') == 'paused' ? 'info-box-active' : '' }}" 
                                 style="cursor: pointer; {{ request('status') == 'paused' ? 'box-shadow: 0 0 10px rgba(96, 92, 168, 0.5);' : '' }}">
                                <span class="info-box-icon bg-purple"><i class="fas fa-pause-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Paused</span>
                                    <span class="info-box-number">{{ $pausedCount }}</span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Filters -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-12">
                        <form method="GET" action="{{ route('maintenance.scheduler.index') }}" class="form-inline">
                            <div class="form-group" style="margin-right: 10px;">
                                <label style="margin-right: 5px;">Status:</label>
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="paused" {{ request('status') == 'paused' ? 'selected' : '' }}>Paused</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin-right: 10px;">
                                <label style="margin-right: 5px;">Priority:</label>
                                <select name="priority" class="form-control">
                                    <option value="">All Priorities</option>
                                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="critical" {{ request('priority') == 'critical' ? 'selected' : '' }}>Critical</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin-right: 10px;">
                                <label style="margin-right: 5px;">Asset:</label>
                                <select name="asset_id" class="form-control select2" style="width: 200px;">
                                    <option value="">All Assets</option>
                                    @foreach($assets as $asset)
                                        <option value="{{ $asset->id }}" {{ request('asset_id') == $asset->id ? 'selected' : '' }}>
                                            {{ $asset->asset_tag }} - {{ $asset->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group" style="margin-right: 10px;">
                                <label style="margin-right: 5px;">Search:</label>
                                <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                            <a href="{{ route('maintenance.scheduler.index') }}" class="btn btn-default"><i class="fas fa-times"></i> Clear</a>
                        </form>
                    </div>
                </div>

                <!-- Schedules Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <a href="{{ route('maintenance.scheduler.index', array_merge(request()->all(), ['sort' => 'created_at', 'order' => request('sort') == 'created_at' && request('order') == 'asc' ? 'desc' : 'asc'])) }}">
                                        Created At
                                        @if(request('sort') == 'created_at')
                                            <i class="fas fa-sort-{{ request('order') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('maintenance.scheduler.index', array_merge(request()->all(), ['sort' => 'title', 'order' => request('sort') == 'title' && request('order') == 'asc' ? 'desc' : 'asc'])) }}">
                                        Title
                                        @if(request('sort') == 'title')
                                            <i class="fas fa-sort-{{ request('order') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('maintenance.scheduler.index', array_merge(request()->all(), ['sort' => 'asset_id', 'order' => request('sort') == 'asset_id' && request('order') == 'asc' ? 'desc' : 'asc'])) }}">
                                        Asset
                                        @if(request('sort') == 'asset_id')
                                            <i class="fas fa-sort-{{ request('order') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('maintenance.scheduler.index', array_merge(request()->all(), ['sort' => 'frequency', 'order' => request('sort') == 'frequency' && request('order') == 'asc' ? 'desc' : 'asc'])) }}">
                                        Frequency
                                        @if(request('sort') == 'frequency')
                                            <i class="fas fa-sort-{{ request('order') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('maintenance.scheduler.index', array_merge(request()->all(), ['sort' => 'next_due_date', 'order' => request('sort') == 'next_due_date' && request('order') == 'asc' ? 'desc' : 'asc'])) }}">
                                        Next Due
                                        @if(request('sort') == 'next_due_date')
                                            <i class="fas fa-sort-{{ request('order') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('maintenance.scheduler.index', array_merge(request()->all(), ['sort' => 'priority', 'order' => request('sort') == 'priority' && request('order') == 'asc' ? 'desc' : 'asc'])) }}">
                                        Priority
                                        @if(request('sort') == 'priority')
                                            <i class="fas fa-sort-{{ request('order') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('maintenance.scheduler.index', array_merge(request()->all(), ['sort' => 'status', 'order' => request('sort') == 'status' && request('order') == 'asc' ? 'desc' : 'asc'])) }}">
                                        Status
                                        @if(request('sort') == 'status')
                                            <i class="fas fa-sort-{{ request('order') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Assigned To</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($schedules as $schedule)
                                <tr class="{{ $schedule->isPastDue() ? 'danger' : '' }}">
                                    <td>
                                        {{ $schedule->created_at ? $schedule->created_at->format('Y-m-d H:i') : '' }}
                                    </td>
                                    <td>
                                        <a href="{{ route('maintenance.scheduler.show', $schedule) }}">
                                            {{ $schedule->title }}
                                        </a>
                                    </td>
                                    <td>{{ $schedule->asset->name ?? 'N/A' }}</td>
                                    <td>{{ ucfirst($schedule->frequency) }} ({{ $schedule->frequency_interval }}x)</td>
                                    <td>
                                        {{ $schedule->next_due_date->format('Y-m-d') }}
                                        @if($schedule->isPastDue())
                                            <span class="label label-danger">Overdue</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="label label-{{ $schedule->priority == 'critical' ? 'danger' : ($schedule->priority == 'high' ? 'warning' : 'info') }}">
                                            {{ ucfirst($schedule->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="label label-{{ $schedule->status == 'active' ? 'success' : 'default' }}">
                                            {{ ucfirst($schedule->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($schedule->assignedUser)
                                            {{ $schedule->assignedUser->first_name }} {{ $schedule->assignedUser->last_name }}
                                        @else
                                            Unassigned
                                        @endif
                                    </td>
                                    <td>
                                        @if(auth()->user()->hasAccess('kits.edit'))
                                            <a href="{{ route('maintenance.scheduler.edit', $schedule) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if(auth()->user()->hasAccess('kits.delete'))
                                            <form method="POST" action="{{ route('maintenance.scheduler.destroy', $schedule) }}" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No maintenance schedules found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="text-center">
                    {{ $schedules->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@section('moar_scripts')
<style>
    .info-box-link .info-box {
        transition: all 0.3s ease;
        border: 1px solid #f4f4f4;
    }
    
    .info-box-link:hover .info-box {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2) !important;
    }
    
    .info-box-active {
        border: 2px solid #3c8dbc !important;
    }
</style>
<script>
    $(function() {
        // Initialize Select2 for asset filter
        $('.select2').select2({
            placeholder: 'All Assets',
            allowClear: true
        });
    });
</script>
@endsection

@endsection
