@component('mail::message')
# {{ trans('mail.hello') }} {{ $recipient->present()->fullName() }},

{{ $introduction }}:

@if (($snipeSettings->show_images_in_email =='1') && (method_exists($item, 'getImageUrl') && $item->getImageUrl()))
<center><img src="{{ $item->getImageUrl() }}" alt="Asset" style="max-width: 570px;"></center>
@endif

@component('mail::table')
|        |          |
| ------------- | ------------- |
| **{{ trans('mail.user') }}** | {{ $assignedTo->present()->fullName() }} |
| **{{ trans('mail.name') }}** | {{ $item->present()->name() }} |
@if (isset($item->asset_tag))
| **{{ trans('mail.asset_tag') }}** | {{ $item->asset_tag }} |
@endif
@if ($note != '')
| **{{ trans('mail.notes') }}** | {{ $note }} |
@endif
@endcomponent

{{ trans('mail.best_regards') }}

{{ $snipeSettings->site_name }}

@endcomponent
