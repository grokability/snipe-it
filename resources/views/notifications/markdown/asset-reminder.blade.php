@component('mail::message')
# {{ trans('mail.hello') }} {{ $assigned_to}},

{{trans('mail.item_checked_reminder', ['count' => $count])}}
[{{ trans('general.click_here')}}]({{$accept_url}})

{{ trans('mail.best_regards') }}

{{ $snipeSettings->site_name }}

@endcomponent
