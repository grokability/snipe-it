@component('mail::message')

{{ trans_choice('mail.upcoming-audits', $assets->count(), ['count' => $assets->count(), 'threshold' => $threshold]) }}

<x-mail::table>
|        |        |          |
| ------------- | ------------- | ------------- |
@foreach ($assets as $asset)
| {{ ($asset->next_audit_diff_in_days <= 7) ? '🚨' : (($asset->next_audit_diff_in_days <= 14) ? '⚠️' : '⚠️') }} **{{ trans('mail.name') }}** | <a href="{{ route('hardware.show', $asset->id) }}">{{ $asset->display_name }}</a> |
@if ($asset->serial)
| **{{ trans('general.serial_number') }}** | {{ $asset->serial }} |
@endif
@if ($asset->purchase_date)
| **{{ trans('general.purchase_date') }}** | {{ $asset->purchase_date_formatted }} |
@endif
@if ($asset->last_audit_date)
| **{{ trans('general.last_audit') }}** | {{ $asset->last_audit_formatted_date }} ({{ $asset->last_audit_diff_for_humans }}) |
@endif
@if ($asset->next_audit_date)
| **{{ trans('general.next_audit_date') }}** | {{ $asset->next_audit_formatted_date }} ({{ $asset->next_audit_diff_for_humans }}) |
@endif
@if ($asset->supplier)
| **{{ trans('mail.supplier') }}** | {{ ($asset->supplier ? e($asset->supplier->name) : '') }} |
@endif
@if ($asset->assignedTo)
| **{{ trans('mail.assigned_to') }}** | {{ e($asset->assignedTo->present()->display_name) }} |
@endif
@if ($asset->notes)
| **{{ trans('general.notes') }}** | {{ e($asset->notes) }} |
@endif
| <hr> | <hr> |
@endforeach
</x-mail::table>

<x-mail::button :url="route('assets.audit.due')">
    {{ trans_choice('general.audit_due_days', $threshold, ['days' => $threshold]) }}
</x-mail::button>
@endcomponent
