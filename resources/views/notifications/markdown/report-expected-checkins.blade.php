@component('mail::message')
# {{ trans('mail.hello') }},

{{ trans('general.due_to_checkin', array('count' => $assets->count())) }}

@component('mail::table')
| {{ trans('general.assets') }} | {{ trans('general.checked_out_to') }} | {{ trans('general.expected_checkin') }} |
| ------------- | ------------- | ------------- |
@foreach ($assets as $asset)
@php
$checkin = Helper::getFormattedDateObject($asset->expected_checkin, 'date');

$assignedToName = $asset->assignedTo ? $asset->assignedTo->present()->fullName : trans('general.unknown_user');
$assignedToRoute = $asset->assignedTo ? route($asset->targetShowRoute().'.show', [$asset->assignedTo->id]) : '';
@endphp
| [{{ $asset->display_name }}]({{ route('hardware.show', $asset) }}) | @if ($asset->assignedTo) [{{ $assignedToName }}]({{ $assignedToRoute }}) @else {{ $assignedToName }} @endif  | {{ $checkin['formatted'] }}
@endforeach
@endcomponent

{{ trans('mail.best_regards') }}

{{ $snipeSettings->site_name }}

@endcomponent
