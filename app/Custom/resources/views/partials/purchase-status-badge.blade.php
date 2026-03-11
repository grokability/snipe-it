@php
    $badgeClass = match($status) {
        'bekliyor'        => 'label-default',
        'onaylandi'       => 'label-info',
        'siparis_verildi' => 'label-primary',
        'teslim_alindi'   => 'label-success',
        'iptal'           => 'label-danger',
        default           => 'label-default',
    };
    $label = match($status) {
        'bekliyor'        => __('custom::fom.pr_status_bekliyor'),
        'onaylandi'       => __('custom::fom.pr_status_onaylandi'),
        'siparis_verildi' => __('custom::fom.pr_status_siparis_verildi'),
        'teslim_alindi'   => __('custom::fom.pr_status_teslim_alindi'),
        'iptal'           => __('custom::fom.pr_status_iptal'),
        default           => $status,
    };
@endphp
<span class="label {{ $badgeClass }}">{{ $label }}</span>
