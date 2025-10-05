{{-- Asset Purchase Order Information Partial --}}
{{-- This partial shows PO information for an asset --}}

@php
    // Get purchase order information for this asset
    $purchaseOrder = null;
    if (isset($asset) && $asset->id) {
        $purchaseOrder = DB::table('purchase_orders')
            ->where('id', function($query) use ($asset) {
                $query->select('purchase_order_id')
                      ->from('assets')
                      ->where('id', $asset->id)
                      ->whereNotNull('purchase_order_id');
            })
            ->first();
    }
@endphp

@if($purchaseOrder)
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fas fa-shopping-cart"></i> Purchase Order Information
                </h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-striped table-condensed">
                            <tr>
                                <td><strong>PO Number:</strong></td>
                                <td>
                                    <a href="{{ url('purchase-orders/' . $purchaseOrder->id) }}" class="btn btn-sm btn-primary">
                                        {{ $purchaseOrder->po_number }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'draft' => 'secondary',
                                            'pending' => 'warning', 
                                            'approved' => 'info',
                                            'ordered' => 'primary',
                                            'received' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                        $statusColor = $statusColors[$purchaseOrder->status] ?? 'secondary';
                                    @endphp
                                    <span class="label label-{{ $statusColor }}">
                                        {{ ucfirst($purchaseOrder->status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Order Date:</strong></td>
                                <td>{{ $purchaseOrder->order_date ? date('F j, Y', strtotime($purchaseOrder->order_date)) : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-striped table-condensed">
                            <tr>
                                <td><strong>Expected Delivery:</strong></td>
                                <td>{{ $purchaseOrder->expected_delivery_date ? date('F j, Y', strtotime($purchaseOrder->expected_delivery_date)) : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Amount:</strong></td>
                                <td><strong>${{ number_format($purchaseOrder->total_amount, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td><strong>Supplier:</strong></td>
                                <td>
                                    @php
                                        $supplier = DB::table('suppliers')->where('id', $purchaseOrder->supplier_id)->first();
                                    @endphp
                                    {{ $supplier ? $supplier->name : 'N/A' }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                @if($purchaseOrder->notes)
                <div class="row">
                    <div class="col-md-12">
                        <strong>Notes:</strong>
                        <p class="well well-sm">{{ $purchaseOrder->notes }}</p>
                    </div>
                </div>
                @endif
                
                <div class="row">
                    <div class="col-md-12">
                        <a href="{{ url('purchase-orders/' . $purchaseOrder->id) }}" class="btn btn-primary">
                            <i class="fas fa-eye"></i> View Full Purchase Order
                        </a>
                        <a href="{{ url('purchase-orders/' . $purchaseOrder->id . '/pdf') }}" class="btn btn-info" target="_blank">
                            <i class="fas fa-download"></i> Download PO PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
