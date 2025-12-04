<div id="livewire-notifications-root">
    {{-- Existing redirect/session flashes --}}
    @include('notifications')

    {{-- Live (dynamic) alerts --}}
    @foreach($liveAlerts as $alert)
        @include('partials.live-alert', ['alert' => $alert])
    @endforeach

    {{-- Javascript --}}
    @script
    <script>
        // Livewire event bridging. This isn't the best way but it works for now...
        Livewire.on('showNotificationInFrontend', (params) => {
            //console.log(params);
            Livewire.dispatch('showNotification', params[0]);
        });
    </script>
    @endscript

</div>