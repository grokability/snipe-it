@component('mail::message')
# {{ trans('mail.hello') }} {{ $assigned_to}},

{{trans_choice('mail.item_checked_reminder', $count, ['count' => $count])}}
[{{ trans('general.click_here')}}]({{$accept_url}})

{{ trans('mail.best_regards') }}

{{ $snipeSettings->site_name }}

@endcomponent
