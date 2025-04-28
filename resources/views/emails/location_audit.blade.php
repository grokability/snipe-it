@component('mail::message')
# {{ __('Audit for') }} {{ $audit->location->name }} – {{ $audit->created_at->format('d.m.Y H:i') }}

@component('mail::table')
| {{ __('Asset Tag') }} | {{ __('Name') }} | {{ __('Present?') }} |
|:--|:--|:--:|
@foreach($audit->assets as $asset)
| {{ $asset->asset_tag }} | {{ $asset->name }} | {{ $asset->pivot->present ? '✔' : '✖' }} |
@endforeach
@endcomponent

@if($audit->notes)
> {{ $audit->notes }}
@endif

{{ __('Audited by') }} **{{ $audit->user->name }}**  
{{ config('app.name') }}
@endcomponent
