@extends('layouts/default')

@section('title')
    Purchase Orders
@parent
@stop

@section('header_right')
    <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary pull-right">
        Create New Purchase Order
    </a>
@stop

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Purchase Orders</h3>
                
                <!-- Filters -->
                <div class="box-tools pull-right">
                    <form method="GET" class="form-inline">
                        <div class="form-group">
                            <select name="status" class="form-control input-sm">
                                <option value="">All Statuses</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <select name="supplier_id" class="form-control input-sm">
                                <option value="">All Suppliers</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <input type="text" name="search" class="form-control input-sm" placeholder="Search..." value="{{ request('search') }}">
                        </div>
                        
                        <button type="submit" class="btn btn-default btn-sm">Filter</button>
                        <a href="{{ route('purchase-orders.index') }}" class="btn btn-default btn-sm">Clear</a>
                    </form>
                </div>
            </div>
            
            <div class="box-body">
                @if($purchaseOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped snipe-table">
                            <thead>
                                <tr>
                                    <th>PO Number</th>
                                    <th>Supplier</th>
                                    <th>Status</th>
                                    <th>Order Date</th>
                                    <th>Expected Delivery</th>
                                    <th>Total Amount</th>
                                    <th>Items</th>
                                    <th>Created By</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseOrders as $po)
                                    <tr>
                                        <td>
                                            <a href="{{ route('purchase-orders.show', $po) }}">
                                                <strong>{{ $po->po_number }}</strong>
                                            </a>
                                        </td>
                                        <td>{{ $po->supplier->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="label label-{{ $po->status_badge }}">
                                                {{ ucfirst($po->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $po->order_date ? $po->order_date->format('Y-m-d') : 'N/A' }}</td>
                                        <td>{{ $po->expected_delivery_date ? $po->expected_delivery_date->format('Y-m-d') : 'N/A' }}</td>
                                        <td>${{ number_format($po->total_amount, 2) }}</td>
                                        <td>
                                            <span class="badge">{{ $po->items->count() }}</span>
                                        </td>
                                        <td>{{ $po->creator->first_name ?? 'N/A' }} {{ $po->creator->last_name ?? '' }}</td>
                                        <td class="text-right">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                                                    Actions <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-right">
                                                    <li><a href="{{ route('purchase-orders.show', $po) }}">View</a></li>
                                                    @if(!in_array($po->status, ['received', 'cancelled']))
                                                        <li><a href="{{ route('purchase-orders.edit', $po) }}">Edit</a></li>
                                                    @endif
                                                    <li><a href="{{ route('purchase-orders.pdf', $po) }}" target="_blank">View PDF</a></li>
                                                    <li>
                                                        <form method="POST" action="{{ route('purchase-orders.duplicate', $po) }}" style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-link" style="padding: 3px 20px; text-align: left; width: 100%;">
                                                                Duplicate
                                                            </button>
                                                        </form>
                                                    </li>
                                                    @if(!in_array($po->status, ['ordered', 'received']))
                                                        <li class="divider"></li>
                                                        <li>
                                                            <form method="POST" action="{{ route('purchase-orders.destroy', $po) }}" 
                                                                  onsubmit="return confirm('Are you sure you want to delete this purchase order?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-link text-danger" 
                                                                        style="padding: 3px 20px; text-align: left; width: 100%;">
                                                                    Delete
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-center">
                        {{ $purchaseOrders->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center">
                        <p>No purchase orders found.</p>
                        <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary">
                            Create your first purchase order
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@stop

@section('moar_scripts')
<script>
$(document).ready(function() {
    // Auto-submit form when filters change
    $('select[name="status"], select[name="supplier_id"]').change(function() {
        $(this).closest('form').submit();
    });
});
</script>
@stop
