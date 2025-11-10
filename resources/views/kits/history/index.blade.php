@extends('layouts.default')

@section('title')
    Maintenance History
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h2 class="box-title">Maintenance History</h2>
                <div class="box-tools pull-right">
                    <a href="{{ route('maintenance.history.export') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-download"></i> Export Report
                    </a>
                    <a href="{{ route('maintenance.history.statistics') }}" class="btn btn-info btn-sm">
                        <i class="fas fa-chart-bar"></i> Statistics
                    </a>
                </div>
            </div>

            <div class="box-body">
                <!-- Summary Statistics -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-blue"><i class="fas fa-wrench"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Maintenance</span>
                                <span class="info-box-number">{{ $totalMaintenance }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-green"><i class="fas fa-pound-sign"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Cost</span>
                                <span class="info-box-number">£{{ number_format($totalCost, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-12">
                        <form method="GET" action="{{ route('maintenance.history.index') }}" class="form-inline">
                            <div class="form-group">
                                <select name="asset_id" class="form-control">
                                    <option value="">All Assets</option>
                                    @foreach($assets as $asset)
                                        <option value="{{ $asset->id }}" {{ request('asset_id') == $asset->id ? 'selected' : '' }}>
                                            {{ $asset->name }}
                                        </option>
                                    @endforeach
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
                                <input type="date" name="date_from" class="form-control" placeholder="From Date" value="{{ request('date_from') }}">
                            </div>
                            <div class="form-group">
                                <input type="date" name="date_to" class="form-control" placeholder="To Date" value="{{ request('date_to') }}">
                            </div>
                            <div class="form-group">
                                <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                            </div>
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('maintenance.history.index') }}" class="btn btn-default">Clear</a>
                        </form>
                    </div>
                </div>

                <!-- History Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Title</th>
                                <th>Asset</th>
                                <th>Type</th>
                                <th>Performed By</th>
                                <th>Duration</th>
                                <th>Cost</th>
                                <th>Outcome</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($history as $record)
                                <tr>
                                    <td>{{ $record->performed_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('maintenance.history.show', $record) }}">
                                            {{ $record->title }}
                                        </a>
                                    </td>
                                    <td>{{ $record->asset->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="label label-info">
                                            {{ ucfirst($record->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($record->performedByUser)
                                            {{ $record->performedByUser->first_name }} {{ $record->performedByUser->last_name }}
                                        @else
                                            Unknown
                                        @endif
                                    </td>
                                    <td>{{ $record->formatted_duration }}</td>
                                    <td>£{{ number_format($record->cost ?? 0, 2) }}</td>
                                    <td>
                                        <span class="label label-{{ $record->outcome == 'success' ? 'success' : ($record->outcome == 'failed' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($record->outcome) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('maintenance.history.show', $record) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No maintenance history found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="text-center">
                    {{ $history->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
