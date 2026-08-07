@component('mail::message')
# {{ trans('mail.hello') }} {{ $assigned_to }},

{{ trans_choice('mail.reacceptance_body', $count) }}
[{{ trans('general.click_here') }}]({{ $link }})

{{ trans('mail.best_regards') }}

{{ $snipeSettings->site_name }}

@endcomponent
