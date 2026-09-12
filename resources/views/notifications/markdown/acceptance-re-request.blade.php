@component('mail::message')
# {{ trans('mail.hello') }} {{ $assigned_to }},

{{ trans_choice('mail.acceptance_re_request_intro', $count, ['count' => $count]) }}

@foreach ($shown_items as $item)
- {{ $item['name'] }} ({{ $item['type'] }}){{ $item['qty'] > 1 ? ' × '.$item['qty'] : '' }}
@endforeach

@if ($remaining > 0)
{{ trans_choice('mail.acceptance_re_request_more_items', $remaining, ['count' => $remaining]) }}
@endif

[{{ trans('general.click_here') }}]({{ $accept_url }})

{{ trans('mail.best_regards') }}

{{ $snipeSettings->site_name }}

@endcomponent
