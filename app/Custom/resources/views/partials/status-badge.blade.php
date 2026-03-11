@php
    $badgeClass = match($status) {
        'bekliyor'      => 'label-default',
        'atandi'        => 'label-info',
        'devam_ediyor'  => 'label-warning',
        'tamamlandi'    => 'label-success',
        'iptal'         => 'label-danger',
        default         => 'label-default',
    };
    $label = match($status) {
        'bekliyor'      => __('custom::fom.wo_status_bekliyor'),
        'atandi'        => __('custom::fom.wo_status_atandi'),
        'devam_ediyor'  => __('custom::fom.wo_status_devam_ediyor'),
        'tamamlandi'    => __('custom::fom.wo_status_tamamlandi'),
        'iptal'         => __('custom::fom.wo_status_iptal'),
        default         => $status,
    };
@endphp
<span class="label {{ $badgeClass }}">{{ $label }}</span>
