@component('mail::message')
# {{ trans('mail.hello') }},

@if ($expected_checkin_date > now())
{{ trans('mail.Expected_Checkin_Date', ['date' => $date]) }}
@else
{{ trans('mail.Expected_Checkin_Date_Past', ['date' => $date]) }}
@endif

@if ((isset($asset)) && ($asset!=''))
<strong>{{ trans('mail.asset_name') }}:</strong> {{ $asset }}

@endif
<strong>{{ trans('mail.asset_tag') }}:</strong> {{ $asset_tag }}

@if (isset($serial))
<strong>{{ trans('mail.serial') }}:</strong> {{ $serial }}

@endif

**[{{ trans('mail.your_assets') }}]({{ route('view-assets') }})**

{{ trans('mail.best_regards') }}

{{ $snipeSettings->site_name }}

@endcomponent
