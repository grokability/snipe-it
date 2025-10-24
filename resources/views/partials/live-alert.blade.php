@php
    $id = $alert['id'];
    $type = $alert['type']; // already normalized (success|danger|warning|info)
    $icon = $alert['icon'] ?? null;
    $title = $alert['title'] ?? null;
    $message = $alert['message'] ?? '';
    $description = $alert['description'] ?? null;
    $html = $alert['html'] ?? false;
@endphp

<div class="col-md-12" id="live-alert-{{ $id }}" wire:key="live-alert-{{ $id }}">
    <div class="alert alert-{{ $type }}">
        <button type="button" class="close" wire:click="dismiss('{{ $id }}')" aria-label="Close">&times;</button>
        @if($icon)
            <i class="{{ $icon }}"></i>
        @endif
        @if($title)
            <strong>{{ $title }}@if(!$html && $message){{ ': ' }}@endif</strong>
        @endif

        @if($html)
            {!! $message !!}
        @else
            {{ $message }}
        @endif

        @if($description)
            <div class="small" style="margin-top:4px;opacity:.85;">
                @if($html)
                    {!! $description !!}
                @else
                    {{ $description }}
                @endif
            </div>
        @endif
    </div>
</div>