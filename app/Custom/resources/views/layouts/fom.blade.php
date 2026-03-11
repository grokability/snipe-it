@extends('layouts/default')

@push('css')
<style>
    #fom-sidebar-menu .treeview-menu > li > a {
        padding: 5px 5px 5px 25px;
    }
    #fom-sidebar-menu > a > .fa,
    #fom-sidebar-menu > a > .fas {
        width: 20px;
    }
</style>
@endpush

@push('js')
<script nonce="{{ csrf_token() }}">
(function() {
    var t = @json([
        'module'            => __('custom::fom.nav_module'),
        'dashboard'         => __('custom::fom.nav_dashboard'),
        'work_orders'       => __('custom::fom.nav_work_orders'),
        'new_report'        => __('custom::fom.nav_new_report'),
        'kanban'            => __('custom::fom.nav_kanban'),
        'shift_handover'    => __('custom::fom.nav_shift_handover'),
        'shift_history'     => __('custom::fom.nav_shift_history'),
        'spare_parts'       => __('custom::fom.nav_spare_parts'),
        'low_stock'         => __('custom::fom.nav_low_stock'),
        'purchase_requests' => __('custom::fom.nav_purchase_requests'),
    ]);

    var sidebar = document.querySelector('.sidebar-menu[data-widget="tree"]');
    if (!sidebar) return;

    var fomMenu = document.createElement('li');
    fomMenu.id = 'fom-sidebar-menu';
    fomMenu.className = 'treeview' + (window.location.pathname.indexOf('/fom') === 0 ? ' active' : '');
    fomMenu.innerHTML =
        '<a href="#">' +
            '<i class="fas fa-industry fa-fw"></i> ' +
            '<span>' + t.module + '</span>' +
            '<span class="pull-right-container"><i class="fas fa-angle-left pull-right"></i></span>' +
        '</a>' +
        '<ul class="treeview-menu"' + (window.location.pathname.indexOf('/fom') === 0 ? ' style="display:block"' : '') + '>' +
            '<li' + (window.location.pathname === '/fom' ? ' class="active"' : '') + '><a href="/fom"><i class="fas fa-tachometer-alt fa-fw"></i> ' + t.dashboard + '</a></li>' +
            '<li' + (window.location.pathname.indexOf('/fom/work-orders') === 0 && window.location.pathname.indexOf('board') === -1 && window.location.pathname.indexOf('create') === -1 ? ' class="active"' : '') + '><a href="/fom/work-orders"><i class="fas fa-wrench fa-fw"></i> ' + t.work_orders + '</a></li>' +
            '<li' + (window.location.pathname === '/fom/work-orders/create' ? ' class="active"' : '') + '><a href="/fom/work-orders/create"><i class="fas fa-plus-circle fa-fw"></i> ' + t.new_report + '</a></li>' +
            '<li' + (window.location.pathname === '/fom/work-orders/board' ? ' class="active"' : '') + '><a href="/fom/work-orders/board"><i class="fas fa-columns fa-fw"></i> ' + t.kanban + '</a></li>' +
            '<li' + (window.location.pathname === '/fom/shift/handover' ? ' class="active"' : '') + '><a href="/fom/shift/handover"><i class="fas fa-exchange-alt fa-fw"></i> ' + t.shift_handover + '</a></li>' +
            '<li' + (window.location.pathname === '/fom/shift/history' ? ' class="active"' : '') + '><a href="/fom/shift/history"><i class="fas fa-history fa-fw"></i> ' + t.shift_history + '</a></li>' +
            '<li' + (window.location.pathname.indexOf('/fom/spare-parts') === 0 && window.location.pathname.indexOf('low-stock') === -1 ? ' class="active"' : '') + '><a href="/fom/spare-parts"><i class="fas fa-cogs fa-fw"></i> ' + t.spare_parts + '</a></li>' +
            '<li' + (window.location.pathname === '/fom/spare-parts/low-stock' ? ' class="active"' : '') + '><a href="/fom/spare-parts/low-stock"><i class="fas fa-exclamation-triangle fa-fw"></i> ' + t.low_stock + '</a></li>' +
            '<li' + (window.location.pathname.indexOf('/fom/purchase-requests') === 0 ? ' class="active"' : '') + '><a href="/fom/purchase-requests"><i class="fas fa-shopping-cart fa-fw"></i> ' + t.purchase_requests + '</a></li>' +
        '</ul>';

    sidebar.appendChild(fomMenu);

    // Re-initialize AdminLTE tree widget for our new menu item
    if (typeof $.fn.tree !== 'undefined') {
        $(sidebar).tree('init');
    }
})();
</script>
@endpush
