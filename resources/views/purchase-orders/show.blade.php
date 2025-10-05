@extends('layouts/default')

@section('title')
    Purchase Order {{ $purchaseOrder->po_number }}
@parent
@stop

@section('header_right')
    <div class="btn-group pull-right">
        @if(!in_array($purchaseOrder->status, ['received', 'cancelled']))
            <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}" class="btn btn-warning">
                <i class="fa fa-pencil"></i> Edit
            </a>
        @endif
        
        <a href="{{ route('purchase-orders.pdf', $purchaseOrder) }}" class="btn btn-info" target="_blank">
            <i class="fa fa-file-pdf-o"></i> View PDF
        </a>
        
        <form method="POST" action="{{ route('purchase-orders.duplicate', $purchaseOrder) }}" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-default">
                <i class="fa fa-copy"></i> Duplicate
            </button>
        </form>
        
        <a href="{{ route('purchase-orders.index') }}" class="btn btn-default">
            <i class="fa fa-arrow-left"></i> Back to List
        </a>
    </div>
@stop

@section('content')

<div class="row">
    <div class="col-md-8">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Purchase Order Details</h3>
            </div>
            
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-striped">
                            <tr>
                                <td><strong>PO Number:</strong></td>
                                <td>{{ $purchaseOrder->po_number }}</td>
                            </tr>
                            <tr>
                                <td><strong>Supplier:</strong></td>
                                <td>{{ $purchaseOrder->supplier->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <span class="label label-{{ $purchaseOrder->status_badge }}">
                                        {{ ucfirst($purchaseOrder->status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Order Date:</strong></td>
                                <td>{{ $purchaseOrder->order_date ? $purchaseOrder->order_date->format('F j, Y') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <table class="table table-striped">
                            <tr>
                                <td><strong>Expected Delivery:</strong></td>
                                <td>{{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('F j, Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Amount:</strong></td>
                                <td><strong>{{ $snipeSettings->default_currency }} {{ number_format($purchaseOrder->total_amount, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td><strong>Created By:</strong></td>
                                <td>{{ $purchaseOrder->creator->first_name ?? 'N/A' }} {{ $purchaseOrder->creator->last_name ?? '' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Created At:</strong></td>
                                <td>{{ $purchaseOrder->created_at->format('F j, Y g:i A') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($purchaseOrder->notes)
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Notes</h4>
                            <div class="well">
                                {{ $purchaseOrder->notes }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Items -->
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Items ({{ $purchaseOrder->items->count() }})</h3>
            </div>
            
            <div class="box-body">
                @if($purchaseOrder->items->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Asset</th>
                                    <th>Model</th>
                                    <th>Asset Tag</th>
                                    <th class="text-right">Unit Price</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseOrder->items as $item)
                                    <tr>
                                        <td>
                                            @if($item->asset)
                                                <a href="{{ route('hardware.show', $item->asset) }}">
                                                    <strong>{{ $item->asset->name ?: 'Asset #' . $item->asset->asset_tag }}</strong>
                                                </a>
                                                <br><small class="text-muted">Linked to specific asset</small>
                                            @else
                                                <strong>{{ $item->model_name }}</strong>
                                                <br><small class="text-muted">Model-based item</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->asset && $item->asset->model)
                                                <a href="{{ route('models.show', $item->asset->model) }}">
                                                    {{ $item->asset->model->name }}
                                                    @if($item->asset->model->model_number)
                                                        <br><small class="text-muted">{{ $item->asset->model->model_number }}</small>
                                                    @endif
                                                </a>
                                            @elseif($item->assetModel)
                                                <a href="{{ route('models.show', $item->assetModel) }}">
                                                    {{ $item->assetModel->name }}
                                                    @if($item->assetModel->model_number)
                                                        <br><small class="text-muted">{{ $item->assetModel->model_number }}</small>
                                                    @endif
                                                </a>
                                            @else
                                                <span class="text-muted">{{ $item->model_name ?: 'Unknown' }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->asset)
                                                <code>{{ $item->asset->asset_tag }}</code>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            {{ $snipeSettings->default_currency }} {{ number_format($item->unit_price, 2) }}
                                        </td>
                                        <td>
                                            {{ $item->notes ?: '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="info">
                                    <th colspan="4" class="text-right">Total:</th>
                                    <th class="text-right">{{ $snipeSettings->default_currency }} {{ number_format($purchaseOrder->total_amount, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <p class="text-center text-muted">No items found.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Status Actions -->
        @if(!in_array($purchaseOrder->status, ['received', 'cancelled']))
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">Status Actions</h3>
                </div>
                <div class="box-body">
                    <form method="POST" action="{{ route('purchase-orders.update-status', $purchaseOrder) }}">
                        @csrf
                        @method('PATCH')
                        
                        <div class="form-group">
                            <label>Change Status:</label>
                            <select name="status" class="form-control">
                                <option value="draft" {{ $purchaseOrder->status == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="pending" {{ $purchaseOrder->status == 'pending' ? 'selected' : '' }}>Pending Approval</option>
                                <option value="approved" {{ $purchaseOrder->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="ordered" {{ $purchaseOrder->status == 'ordered' ? 'selected' : '' }}>Ordered</option>
                                <option value="received" {{ $purchaseOrder->status == 'received' ? 'selected' : '' }}>Received</option>
                                <option value="cancelled" {{ $purchaseOrder->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">
                            Update Status
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <!-- Supplier Information -->
        @if($purchaseOrder->supplier)
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">Supplier Information</h3>
                </div>
                <div class="box-body">
                    <table class="table table-striped table-condensed">
                        <tr>
                            <td><strong>Name:</strong></td>
                            <td>{{ $purchaseOrder->supplier->name }}</td>
                        </tr>
                        @if($purchaseOrder->supplier->contact)
                            <tr>
                                <td><strong>Contact:</strong></td>
                                <td>{{ $purchaseOrder->supplier->contact }}</td>
                            </tr>
                        @endif
                        @if($purchaseOrder->supplier->phone)
                            <tr>
                                <td><strong>Phone:</strong></td>
                                <td>{{ $purchaseOrder->supplier->phone }}</td>
                            </tr>
                        @endif
                        @if($purchaseOrder->supplier->email)
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>
                                    <a href="mailto:{{ $purchaseOrder->supplier->email }}">
                                        {{ $purchaseOrder->supplier->email }}
                                    </a>
                                </td>
                            </tr>
                        @endif
                    </table>
                    
                    <a href="{{ route('suppliers.show', $purchaseOrder->supplier) }}" class="btn btn-default btn-block">
                        View Supplier Details
                    </a>
                </div>
            </div>
        @endif

        <!-- Quick Stats -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Quick Stats</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-6">
                        <div class="description-block border-right">
                            <span class="description-percentage text-green">
                                <i class="fa fa-shopping-cart"></i>
                            </span>
                            <h5 class="description-header">{{ $purchaseOrder->items->count() }}</h5>
                            <span class="description-text">ITEMS</span>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="description-block">
                            <span class="description-percentage text-blue">
                                <i class="fa fa-dollar"></i>
                            </span>
                            <h5 class="description-header">{{ $snipeSettings->default_currency }} {{ number_format($purchaseOrder->total_amount, 0) }}</h5>
                            <span class="description-text">TOTAL</span>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <div class="description-block">
                            <span class="description-percentage text-yellow">
                                <i class="fa fa-cubes"></i>
                            </span>
                            <h5 class="description-header">{{ $purchaseOrder->items->sum('quantity') }}</h5>
                            <span class="description-text">TOTAL QUANTITY</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Danger Zone -->
        @if(!in_array($purchaseOrder->status, ['ordered', 'received']))
            <div class="box box-solid box-danger">
                <div class="box-header with-border">
                    <h3 class="box-title">Danger Zone</h3>
                </div>
                <div class="box-body">
                    <p class="text-muted">
                        Once you delete this purchase order, there is no going back. Please be certain.
                    </p>
                    
                    <form method="POST" action="{{ route('purchase-orders.destroy', $purchaseOrder) }}" 
                          onsubmit="return confirm('Are you sure you want to delete this purchase order? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fa fa-trash"></i> Delete Purchase Order
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>

@stop
