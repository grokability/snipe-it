<div id="livewire-notifications-root" wire:key="livewire-notifications-root">
    {{-- Existing redirect/session flashes --}}
    @include('notifications')

    {{-- Live dynamic alerts --}}
    @foreach($liveAlerts as $alert)
        <div class="col-md-12" wire:key="dyn-{{ $alert['id'] }}">
            <div class="alert alert-{{ $alert['type'] }}">
                <button type="button" class="close" wire:click="dismiss('{{ $alert['id'] }}')">&times;</button>
                {{ $alert['message'] }}
            </div>
        </div>
    @endforeach
</div>