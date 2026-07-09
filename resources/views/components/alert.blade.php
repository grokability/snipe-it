@props([
    'type'     => 'info',    // success | danger | warning | info | error
    'icon'     => null,
    'heading'  => null,
    'html'     => false,
    'confetti' => false,
    'id'       => null,
])

@php
    // Normalize "error" to Bootstrap's "danger"
    $normalized = $type === 'error' ? 'danger' : $type;

    // Default icon set (Font Awesome 5 in your project already)
    $iconClass = $icon ?? match($normalized) {
        'success' => 'fas fa-check faa-pulse animated',
        'danger'  => 'fas fa-exclamation-triangle faa-pulse animated',
        'warning' => 'fas fa-exclamation-triangle faa-pulse animated',
        default   => 'fas fa-info-circle faa-pulse animated',
    };

    $wrapperId = $id ? 'id="'.$id.'"' : '';
@endphp

<div class="col-md-12" {!! $wrapperId !!}>
    <div class="alert alert-{{ $normalized }}">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">&times;</button>
        @if($iconClass)
            <i class="{{ $iconClass }}"></i>
        @endif
        @if($heading)
            <strong>{{ $heading }}:</strong>
        @endif

        @if($html)
            {!! $slot !!}
        @else
            {{ $slot }}
        @endif
    </div>
</div>

@if($confetti)
    @include('partials.confetti-js')
@endif