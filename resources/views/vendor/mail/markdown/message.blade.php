@component('mail::layout')
{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
@if (($snipeSettings->show_images_in_email=='1' ) && ($snipeSettings::setupCompleted()))

@if ($snipeSettings->brand == '3')
@if ($snipeSettings->logo!='')
<img class="navbar-brand-img logo" src="{{ config('app.url') }}/uploads/{{ $snipeSettings->logo }}">
@endif
{{ $snipeSettings->site_name }}

@elseif ($snipeSettings->brand == '2')
@if ($snipeSettings->logo!='')
<img class="navbar-brand-img logo" src="{{ config('app.url') }}/uploads/{{ $snipeSettings->logo }}">
@endif
@else
{{ $snipeSettings->site_name }}
@endif
@else
Snipe-IT
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
© {{ date('Y') }} Snipe-it. All rights reserved.
@endif

@if ($snipeSettings->privacy_policy_link!='')
<a href="{{ $snipeSettings->privacy_policy_link }}">{{ trans('admin/settings/general.privacy_policy') }}</a>
@endif

@endcomponent
@endslot
@endcomponent
