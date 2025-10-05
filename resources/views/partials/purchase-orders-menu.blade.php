{{-- Purchase Orders Menu Item --}}
{{-- This should be included in the main navigation menu --}}

<li class="{{ Request::is('purchase-orders*') ? 'active' : '' }}">
    <a href="{{ route('purchase-orders.index') }}">
        <i class="fa fa-shopping-cart"></i>
        <span>Purchase Orders</span>
        @if(isset($pendingPurchaseOrders) && $pendingPurchaseOrders > 0)
            <span class="label label-warning pull-right">{{ $pendingPurchaseOrders }}</span>
        @endif
    </a>
    <ul class="treeview-menu">
        <li class="{{ Request::is('purchase-orders') && !Request::is('purchase-orders/create') ? 'active' : '' }}">
            <a href="{{ route('purchase-orders.index') }}">
                <i class="fa fa-list"></i> View All
            </a>
        </li>
        <li class="{{ Request::is('purchase-orders/create') ? 'active' : '' }}">
            <a href="{{ route('purchase-orders.create') }}">
                <i class="fa fa-plus"></i> Create New
            </a>
        </li>
        <li>
            <a href="{{ route('purchase-orders.index', ['status' => 'draft']) }}">
                <i class="fa fa-edit"></i> Drafts
            </a>
        </li>
        <li>
            <a href="{{ route('purchase-orders.index', ['status' => 'pending']) }}">
                <i class="fa fa-clock-o"></i> Pending Approval
            </a>
        </li>
        <li>
            <a href="{{ route('purchase-orders.index', ['status' => 'ordered']) }}">
                <i class="fa fa-truck"></i> Ordered
            </a>
        </li>
    </ul>
</li>
