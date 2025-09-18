@component('mail::message')
{{ trans_choice('mail.assets_warrantee_alert', $assets->count(), ['count'=>$assets->count(), 'threshold' => $threshold]) }}

<style>

    th, td {
        vertical-align: top;
    }
    hr {
        display: block;
        height: 1px;
        border: 0;
        border-top: 1px solid #ccc;
        margin: 1em 0;
        padding: 0;
    }
</style>
<x-mail::table>

|        |        |          |
| ------------- | ------------- | ------------- |
@foreach ($assets as $asset)
@php
    $warranty_expires = \App\Helpers\Helper::getFormattedDateObject($asset->present()->warranty_expires, 'date');
    $eol_date = \App\Helpers\Helper::getFormattedDateObject($asset->asset_eol_date, 'date');
    $warranty_diff = ($asset->present()->warranty_expires) ? round(\Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($warranty_expires['date']), false), 1) : '';
    $eol_diff = round(\Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($asset->asset_eol_date), false),  1);
    $icon = ($warranty_diff <= $threshold && $warranty_diff >= 0) ? '⚠️' : (($eol_diff <= $threshold && $eol_diff >= 0) ? '🚨' : 'ℹ️');
@endphp
| {{ $icon }} **{{ trans('mail.name') }}** | <a href="{{ route('hardware.show', $asset->id) }}">{{ $asset->display_name }}</a> <br><small>{{trans('mail.serial').': '.$asset->serial}}</small> |
@if ($warranty_expires)
| **{{ trans('mail.expires') }}** | {{ !is_null($warranty_expires) ? $warranty_expires['formatted'] : '' }} (<strong>{{ $warranty_diff }} {{ trans('mail.Days') }}</strong>) |
@endif
@if ($eol_date)
| **{{ trans('mail.eol') }}** | {{ !is_null($eol_date) ? $eol_date['formatted'] : '' }} (<strong>{{ $eol_diff }} {{ trans('mail.Days') }}</strong>) |
@endif
@if ($asset->supplier)
| **{{ trans('mail.supplier') }}** | {{ ($asset->supplier ? e($asset->supplier->name) : '') }} |
@endif
@if ($asset->assignedTo)
| **{{ trans('mail.assigned_to') }}** | {{ e($asset->assignedTo->present()->display_name) }} |
@endif
| <hr> | <hr> |
@endforeach
</x-mail::table>

@endcomponent

