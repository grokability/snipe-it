@component('mail::message')

### {{ trans_choice('mail.upcoming-audits', $assets->count(), ['count' => $assets->count(), 'threshold' => $threshold]) }}


<table style="width:100%">
<thead>
<tr>
    <th style="vertical-align: top"> </th>
    <th style="vertical-align: top">{{ trans('mail.name') }}</th>
    <th style="vertical-align: top">{{ trans('general.last_audit') }}</th>
    <th style="vertical-align: top">{{ trans('general.next_audit_date') }}</th>
    <th style="vertical-align: top">{{ trans('mail.Days') }}</th>
    <th style="vertical-align: top">{{ trans('mail.supplier') }}</th>
    <th style="vertical-align: top">{{ trans('mail.assigned_to') }}</th>
    <th style="vertical-align: top">{{ trans('general.notes') }}</th>
</tr>


@foreach ($assets as $asset)
@php
$next_audit_date = Helper::getFormattedDateObject($asset->next_audit_date, 'date', false);
$last_audit_date = Helper::getFormattedDateObject($asset->last_audit_date, 'date', false);
$diff = (int) Carbon::parse(Carbon::now())->diffInDays($asset->next_audit_date, true);
$icon = ($diff <= 7) ? '🚨' : (($diff <= 14) ? '⚠️' : ' ');
@endphp

<tr>
    <td style="vertical-align: top">{{ $icon }}</td>
    <td style="vertical-align: top"><a href="{{ route('hardware.show', $asset->id) }}">{{ $asset->display_name }}</a></td>
    <td style="vertical-align: top">{{ $last_audit_date }}</td>
    <td style="vertical-align: top">{{ $next_audit_date }}</td>
    <td style="vertical-align: top">{{ $diff }}</td>
    <td style="vertical-align: top">{{ ($asset->supplier ? e($asset->supplier->name) : '') }}</td>
    <td style="vertical-align: top">{{ ($asset->assignedTo ? $asset->display_name : '') }}</td>
    <td style="vertical-align: top">{!! nl2br(e($asset->notes)) !!}</td>
</tr>

@endforeach
</table>


@endcomponent
