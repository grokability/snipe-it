@extends('layouts.default')

@section('title')
    Maintenance History Details
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <div class="box-heading">
                    <h2 class="box-title">Maintenance History: {{ $history->workOrder ? $history->workOrder->title : 'N/A' }}</h2>
                </div>
                <div class="box-tools pull-right">
                    <a href="{{ route('maintenance.history.index') }}" class="btn btn-sm btn-default">
                        <i class="fas fa-arrow-left"></i> Back to History
                    </a>
                </div>
            </div>

            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <td><strong>Work Order:</strong></td>
                                    <td>
                                        @if($history->workOrder)
                                            <a href="{{ route('maintenance.workorders.show', $history->workOrder) }}">
                                                {{ $history->workOrder->work_order_number }} - {{ $history->workOrder->title }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Asset:</strong></td>
                                    <td>
                                        @if($history->asset)
                                            <a href="{{ route('hardware.show', $history->asset->id) }}">
                                                {{ $history->asset->asset_tag }} - {{ $history->asset->name }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Maintenance Schedule:</strong></td>
                                    <td>
                                        @if($history->maintenanceSchedule)
                                            <a href="{{ route('maintenance.scheduler.show', $history->maintenanceSchedule) }}">
                                                {{ $history->maintenanceSchedule->title }}
                                            </a>
                                        @else
                                            Ad-hoc
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Type:</strong></td>
                                    <td>
                                        <span class="label label-primary">
                                            {{ ucfirst($history->type ?? 'N/A') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Outcome:</strong></td>
                                    <td>
                                        <span class="label label-{{ $history->outcome == 'success' ? 'success' : ($history->outcome == 'failed' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($history->outcome ?? 'N/A') }}
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
                                    <td><strong>Performed At:</strong></td>
                                    <td>{{ $history->performed_at ? $history->performed_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Completed Date:</strong></td>
                                    <td>{{ $history->completed_date ? $history->completed_date->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Performed By:</strong></td>
                                    <td>{{ $history->performed_by_name ?: ($history->performedByUser ? $history->performedByUser->getFullNameAttribute() : 'N/A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Duration:</strong></td>
                                    <td>{{ $history->duration ? $history->duration . ' minutes' : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Cost:</strong></td>
                                    <td>{{ $history->cost ? '£' . number_format($history->cost, 2) : 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($history->description)
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Description</h4>
                            <p>{{ $history->description }}</p>
                        </div>
                    </div>
                @endif

                @if($history->work_performed)
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Work Performed</h4>
                            <p>{{ $history->work_performed }}</p>
                        </div>
                    </div>
                @endif

                @if($history->parts_used)
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Parts Used</h4>
                            <p>{{ $history->parts_used }}</p>
                        </div>
                    </div>
                @endif

                @if($history->issues_found)
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Issues Found</h4>
                            <p>{{ $history->issues_found }}</p>
                        </div>
                    </div>
                @endif

                @if($history->notes)
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Notes</h4>
                            <p>{{ $history->notes }}</p>
                        </div>
                    </div>
                @endif

                @if($history->kit)
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Predefined Kit Used</h4>
                            <p>{{ $history->kit->name }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="box-footer">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Created At:</strong> {{ $history->created_at->format('Y-m-d H:i:s') }}
                    </div>
                    <div class="col-md-6 text-right">
                        <strong>Updated At:</strong> {{ $history->updated_at->format('Y-m-d H:i:s') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
