@component('mail::message')
# {{ trans('mail.hello') }} {{ $target->assignedto->present()->fullName() }},

{{ trans('mail.the_following_item') }}

@component('mail::table')
|        |          |
| ------------- | ------------- |
| **{{ trans('general.component') }}** | {{ $item->name }} |
@if (isset($item->manufacturer))
| **{{ trans('general.manufacturer') }}** | {{ $item->manufacturer->name }} |
@endif
@if ($admin)
| **{{ trans('general.administrator') }}** | {{ $admin->present()->fullName() }} |
@endif
@if ($note)
| **{{ trans('mail.additional_notes') }}** | {{ $note }} |
@endif
@endcomponent

{{ trans('mail.best_regards') }}

{{ $snipeSettings->site_name }}

@endcomponent
