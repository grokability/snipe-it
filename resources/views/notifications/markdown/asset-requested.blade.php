@component('mail::message')
# {{ trans('mail.hello') }},

{{ $intro_text }}.

@if (($snipeSettings->show_images_in_email =='1') && $item->getImageUrl())
<center><img src="{{ $item->getImageUrl() }}" alt="Asset" style="max-width: 570px;"></center>
@endif

@component('mail::table')
|        |          |
| ------------- | ------------- |
@if (isset($qty))
| **{{ trans('general.qty') }}** | {{ $qty }}
@endif
| **{{ trans('mail.user') }}** | [{{ $requested_by->display_name }}]({{ route('users.show', $requested_by->id) }}) |
| **{{ trans('general.requested') }}** | {{ $requested_date }} |
@if ((isset($item->asset_tag)) && ($item->asset_tag!=''))
| **{{ trans('mail.asset_tag') }}** | {{ $item->asset_tag }} |
@endif
@if (isset($item->model->category))
| **{{ trans('general.category') }}** | {{ $item->model->category->name }} |
@endif
@if ((isset($item->name)) && ($item->name!=''))
| **{{ trans('mail.asset_name') }}** | {{ $item->name }} |
@endif
@if (isset($item->assetstatus))
| **{{ trans('general.status') }}** | {{ $item->assetstatus->name }}
@endif
@if ($item->assignedTo)
| **{{ trans('general.checked_out_to') }}** | {!! $item->assignedTo->present()->nameUrl() !!} ({{ $item->present()->statusMeta }})
@endif
@if (isset($item->manufacturer))
| **{{ trans('general.manufacturer') }}** | {{ $item->manufacturer->name }} |
@endif
@if (isset($item->model))
| **{{ trans('general.asset_model') }}** | {{ $item->model->name }} |
@endif
@if (isset($item->model_no))
| **{{ trans('general.model_no') }}** | {{ $item->model_no }} |
@endif
@if (isset($item->serial))
| **{{ trans('mail.serial') }}** | {{ $item->serial }} |
@endif
@if ((isset($last_checkout)) && ($last_checkout!=''))
| **{{ trans('general.last_checkout') }}** | {{ $last_checkout }} |
@endif
@if ((isset($expected_checkin)) && ($expected_checkin!=''))
| **{{ trans('mail.expecting_checkin_date') }}** | {{ $expected_checkin }} |
@endif
@foreach($fields as $field)
@if (($item->{ $field->db_column_name() }!='') && ($field->show_in_email) && ($field->field_encrypted=='0'))
| **{{ $field->name }}** | {{ $item->{ $field->db_column_name() } }} |
@endif
@endforeach
@if ($note)
| **{{ trans('mail.additional_notes') }}** | {{ $note }} |
@endif
@endcomponent

{{ trans('mail.best_regards') }}

{{ $snipeSettings->site_name }}

@endcomponent
