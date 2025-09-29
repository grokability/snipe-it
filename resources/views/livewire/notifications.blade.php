<div id="livewire-notifications-root">
    {{-- Existing redirect/session flashes --}}
    @include('notifications')

    {{-- Live (dynamic) alerts --}}
    @foreach($liveAlerts as $alert)
        @include('partials.live-alert', ['alert' => $alert])
    @endforeach
</div>