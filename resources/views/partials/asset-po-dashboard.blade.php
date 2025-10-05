{{-- Asset-PO Dashboard Widget --}}
{{-- Shows recent assets linked to Purchase Orders --}}

@php
    // Get recent assets with PO links
    $recentAssetPOs = DB::select("
        SELECT 
            a.id as asset_id,
            a.name as asset_name,
            a.asset_tag,
            po.id as po_id,
            po.po_number,
            po.status as po_status,
            po.order_date,
            s.name as supplier_name,
            am.name as model_name
        FROM assets a
        JOIN purchase_orders po ON a.purchase_order_id = po.id
        LEFT JOIN suppliers s ON po.supplier_id = s.id
        LEFT JOIN models am ON a.model_id = am.id
        ORDER BY po.created_at DESC
        LIMIT 10
    ");
@endphp

@if(count($recentAssetPOs) > 0)
<div class="row">
    <div class="col-md-12">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fas fa-link"></i> Recent Asset-Purchase Order Links
                </h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-striped table-condensed">
                        <thead>
                            <tr>
                                <th>Asset</th>
                                <th>Asset Tag</th>
                                <th>Model</th>
                                <th>Purchase Order</th>
                                <th>Status</th>
                                <th>Supplier</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentAssetPOs as $item)
                            <tr>
                                <td>
                                    <a href="/hardware/{{ $item->asset_id }}">
                                        {{ $item->asset_name ?: 'Asset #' . $item->asset_tag }}
                                    </a>
                                </td>
                                <td>
                                    <code>{{ $item->asset_tag }}</code>
                                </td>
                                <td>
                                    {{ $item->model_name ?: 'N/A' }}
                                </td>
                                <td>
                                    <a href="/purchase-orders/{{ $item->po_id }}" class="btn btn-xs btn-primary">
                                        {{ $item->po_number }}
                                    </a>
                                </td>
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
                                        $statusColor = $statusColors[$item->po_status] ?? 'secondary';
                                    @endphp
                                    <span class="label label-{{ $statusColor }}">
                                        {{ ucfirst($item->po_status) }}
                                    </span>
                                </td>
                                <td>
                                    {{ $item->supplier_name ?: 'N/A' }}
                                </td>
                                <td>
                                    <div class="btn-group btn-group-xs">
                                        <a href="/hardware/{{ $item->asset_id }}" class="btn btn-default" title="View Asset">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="/purchase-orders/{{ $item->po_id }}" class="btn btn-primary" title="View PO">
                                            <i class="fas fa-shopping-cart"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="pull-right">
                            <a href="/purchase-orders" class="btn btn-primary">
                                <i class="fas fa-list"></i> View All Purchase Orders
                            </a>
                            <a href="/hardware" class="btn btn-info">
                                <i class="fas fa-laptop"></i> View All Assets
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@else
<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fas fa-info-circle"></i> Asset-Purchase Order Links
                </h3>
            </div>
            <div class="box-body">
                <p>No assets are currently linked to Purchase Orders.</p>
                <a href="/purchase-orders/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Purchase Order
                </a>
            </div>
        </div>
    </div>
</div>
@endif
