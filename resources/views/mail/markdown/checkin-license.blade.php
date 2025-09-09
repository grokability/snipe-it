@component('mail::message')
# {{ trans('mail.hello') }} {{ $target->display_name }},

{{ trans('mail.the_following_item') }}

@component('mail::table')
|        |          |
| ------------- | ------------- |
| **{{ trans('mail.asset_name') }}** | {{ $license->name }} |
@if (isset($license->manufacturer))
| **{{ trans('general.manufacturer') }}** | {{ $license->manufacturer->name }} |
@endif
@if (($target instanceof \App\Models\User && $target->can('view', $license)) ||($target instanceof \App\Models\Asset && $license_seat->user?->can('view', $license)))
| **Key** | {{ $license->serial }} |
@endif
@if (isset($item->category))
| **{{ trans('general.category') }}** | {{ $license->category->name }} |
@endif
@if ($admin)
| **{{ trans('general.administrator') }}** | {{ $admin->display_name }} |
@endif
@if ($note)
| **{{ trans('mail.additional_notes') }}** | {{ $note }} |
@endif
@endcomponent

{{ trans('mail.best_regards') }}

{{ $snipeSettings->site_name }}

@endcomponent
