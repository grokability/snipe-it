@component('mail::layout')
{{-- Header --}}
@slot('header')

{{-- Check that the $snipeSettings variable is set, images are set to be shown, and setup is complete --}}
<style>

    th, td {
        vertical-align: top;
    }
    hr {
        display: block;
        height: 1px;
        border: 0;
        border-top: 1px solid #edeff2;
        margin: 1em 0;
        padding: 0;
    }
</style>

@if (isset($snipeSettings) && ($snipeSettings::setupCompleted()))

    @if ($snipeSettings->show_url_in_emails=='1' )
        @component('mail::header', ['url' => config('app.url')])
    @else
        @component('mail::header', ['url' => ''])
    @endif

    {{-- Show images in email!  --}}
    @if (($snipeSettings->show_images_in_email=='1') && ($snipeSettings->email_logo!='') && ($snipeSettings->brand != '1'))

        {{-- $snipeSettings->brand = 1 = Text  --}}
        {{-- $snipeSettings->brand = 2 = Logo  --}}
        {{-- $snipeSettings->brand = 3 = Logo + Text  --}}
        @if ($snipeSettings->brand == '3')

            <img style="max-height: 100px; vertical-align:middle;" src="{{ \Storage::disk('public')->url(e($snipeSettings->email_logo)) }}">
            <br><br>
            {{ $snipeSettings->site_name }}
            <br><br>

        {{-- else if branding type is just logo --}}
        @elseif ($snipeSettings->brand == '2')
           <img style="max-height: 100px; vertical-align:middle;" src="{{ \Storage::disk('public')->url(e($snipeSettings->email_logo)) }}">
        @endif

    @else
        {{ $snipeSettings->site_name ?? config('app.name') }}
    @endif

@endif
@endcomponent
@endslot

{{-- Body --}}
{{ $slot }}

{{-- Subcopy --}}
@isset($subcopy)
@slot('subcopy')
@component('mail::subcopy')
{{ $subcopy }}
@endcomponent
@endslot
@endisset

{{-- Footer --}}
@slot('footer')
@component('mail::footer')
@if($snipeSettings::setupCompleted())
© {{ date('Y') }} {{ $snipeSettings->site_name }}. All rights reserved.
@else
© {{ date('Y') }} Snipe-IT. All rights reserved.
@endif

@if ($snipeSettings->privacy_policy_link!='')
<a href="{{ $snipeSettings->privacy_policy_link }}">{{ trans('admin/settings/general.privacy_policy') }}</a>
@endif

@endcomponent
@endslot
@endcomponent
