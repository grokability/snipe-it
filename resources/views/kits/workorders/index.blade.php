@extends('layouts.default')

@section('title')
    Work Orders
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h2 class="box-title">Work Orders</h2>
                <div class="box-tools pull-right">
                    @if(auth()->user()->hasAccess('kits.create'))
                        <a href="{{ route('maintenance.workorders.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Create Work Order
                        </a>
                    @endif
                    <a href="{{ route('maintenance.workorders.deleted') }}" class="btn btn-sm btn-danger" title="View deleted work orders">
                        <i class="fas fa-trash"></i> Deleted
                    </a>
                </div>
            </div>

            <div class="box-body">
                <!-- Statistics Cards - Clickable Filters -->
                <div class="row">
                    <div class="col-md-3">
                        <a href="{{ route('maintenance.workorders.index', ['status' => 'pending']) }}" 
                           class="info-box-link" 
                           style="display: block; color: inherit; text-decoration: none;">
                            <div class="info-box {{ request('status') == 'pending' ? 'info-box-active' : '' }}" 
                                 style="cursor: pointer; {{ request('status') == 'pending' ? 'box-shadow: 0 0 10px rgba(243, 156, 18, 0.5);' : '' }}">
                                <span class="info-box-icon bg-yellow"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Pending</span>
                                    <span class="info-box-number">{{ $pendingCount }}</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('maintenance.workorders.index', ['status' => 'in_progress']) }}" 
                           class="info-box-link" 
                           style="display: block; color: inherit; text-decoration: none;">
                            <div class="info-box {{ request('status') == 'in_progress' ? 'info-box-active' : '' }}" 
                                 style="cursor: pointer; {{ request('status') == 'in_progress' ? 'box-shadow: 0 0 10px rgba(0, 115, 183, 0.5);' : '' }}">
                                <span class="info-box-icon bg-blue"><i class="fas fa-spinner"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">In Progress</span>
                                    <span class="info-box-number">{{ $inProgressCount }}</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('maintenance.workorders.index', ['status' => 'overdue']) }}" 
                           class="info-box-link" 
                           style="display: block; color: inherit; text-decoration: none;">
                            <div class="info-box {{ request('status') == 'overdue' ? 'info-box-active' : '' }}" 
                                 style="cursor: pointer; {{ request('status') == 'overdue' ? 'box-shadow: 0 0 10px rgba(221, 75, 57, 0.5);' : '' }}">
                                <span class="info-box-icon bg-red"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Overdue</span>
                                    <span class="info-box-number">{{ $overdueCount }}</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('maintenance.workorders.index', ['status' => 'completed']) }}" 
                           class="info-box-link" 
                           style="display: block; color: inherit; text-decoration: none;">
                            <div class="info-box {{ request('status') == 'completed' ? 'info-box-active' : '' }}" 
                                 style="cursor: pointer; {{ request('status') == 'completed' ? 'box-shadow: 0 0 10px rgba(0, 166, 90, 0.5);' : '' }}">
                                <span class="info-box-icon bg-green"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Completed</span>
                                    <span class="info-box-number">{{ $completedCount }}</span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Filters -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-12">
                        <form method="GET" action="{{ route('maintenance.workorders.index') }}" class="form-inline">
                            <div class="form-group">
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="on_hold" {{ request('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <select name="type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="preventive" {{ request('type') == 'preventive' ? 'selected' : '' }}>Preventive</option>
                                    <option value="corrective" {{ request('type') == 'corrective' ? 'selected' : '' }}>Corrective</option>
                                    <option value="inspection" {{ request('type') == 'inspection' ? 'selected' : '' }}>Inspection</option>
                                    <option value="emergency" {{ request('type') == 'emergency' ? 'selected' : '' }}>Emergency</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                            </div>
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('maintenance.workorders.index') }}" class="btn btn-default">Clear</a>
                        </form>
                    </div>
                </div>

                <!-- Work Orders Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>WO Number</th>
                                <th>Title</th>
                                <th>Asset</th>
                                <th>Type</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Assigned To</th>
                                <th>Scheduled Start</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($workOrders as $workOrder)
                                <tr class="{{ $workOrder->isOverdue() ? 'danger' : '' }}">
                                    <td>
                                        <a href="{{ route('maintenance.workorders.show', $workOrder) }}">
                                            {{ $workOrder->work_order_number }}
                                        </a>
                                    </td>
                                    <td>{{ $workOrder->title }}</td>
                                    <td>{{ $workOrder->asset->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="label label-info">
                                            {{ ucfirst($workOrder->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="label label-{{ $workOrder->priority == 'critical' ? 'danger' : ($workOrder->priority == 'high' ? 'warning' : 'info') }}">
                                            {{ ucfirst($workOrder->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="label label-{{ $workOrder->status == 'completed' ? 'success' : ($workOrder->status == 'in_progress' ? 'primary' : 'warning') }}">
                                            {{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($workOrder->assignedUser)
                                            {{ $workOrder->assignedUser->first_name }} {{ $workOrder->assignedUser->last_name }}
                                        @else
                                            Unassigned
                                        @endif
                                    </td>
                                    <td>{{ $workOrder->scheduled_start ? $workOrder->scheduled_start->format('Y-m-d H:i') : 'N/A' }}</td>
                                    <td>
                                        @if(auth()->user()->hasAccess('kits.edit'))
                                            <a href="{{ route('maintenance.workorders.edit', $workOrder) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if(auth()->user()->hasAccess('kits.delete'))
                                            <form method="POST" action="{{ route('maintenance.workorders.destroy', $workOrder) }}" style="display: inline;">
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
                                    <td colspan="9" class="text-center">No work orders found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="text-center">
                    {{ $workOrders->links() }}
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
    }
</style>
@endsection

@endsection
