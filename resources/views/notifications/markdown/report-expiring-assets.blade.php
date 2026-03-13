@component('mail::message')
{{ trans_choice('mail.assets_warrantee_alert', $assets->count(), ['count'=>$assets->count(), 'threshold' => $threshold]) }}

<x-mail::table>
|        |        |          |
| ------------- | ------------- | ------------- |
@foreach ($assets as $asset)
| {{ ($asset->eol_diff_in_days <= ($threshold / 2)) ? '🚨' : (($asset->eol_diff_in_days <= $threshold) ? '⚠️' : 'ℹ️ ') }} **{{ trans('mail.name') }}** | <a href="{{ route('hardware.show', $asset->id) }}">{{ $asset->display_name }}</a> |
@if ($asset->serial)
| **{{ trans('general.serial_number') }}** | {{ $asset->serial }} |
@endif
@if ($asset->purchase_date)
| **{{ trans('general.purchase_date') }}** | {{ $asset->purchase_date_formatted }} |
@endif
@if ($asset->warranty_expires)
| **{{ trans('mail.expires') }}** | {{ $asset->warranty_expires_formatted_date }} ({{ $asset->warranty_expires_diff_for_humans }}) |
@endif
@if ($asset->eol_date && $asset->eol_diff_for_humans)
| **{{ trans('mail.eol') }}** | {{ $asset->eol_formatted_date }} ({{ $asset->eol_diff_for_humans }}) |
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

