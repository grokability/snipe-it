@php
    $badgeClass = match($priority) {
        'kritik' => 'label-danger',
        'yuksek' => 'label-warning',
        default  => 'label-success',
    };
    $label = match($priority) {
        'kritik' => __('custom::fom.priority_kritik'),
        'yuksek' => __('custom::fom.priority_yuksek'),
        default  => __('custom::fom.priority_normal'),
    };
@endphp
<span class="label {{ $badgeClass }}">{{ $label }}</span>
