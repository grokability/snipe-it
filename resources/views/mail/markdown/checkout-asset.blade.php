@component('mail::message')
# {{ trans('mail.hello').' '.$target.','}}

{{ $introduction_line }}

@if (($snipeSettings->show_images_in_email =='1') && $item->getImageUrl())
<center><img src="{{ $item->getImageUrl() }}" alt="Asset" style="max-width: 570px;"></center>
@endif

@component('mail::table')
|        |          |
| ------------- | ------------- |
@if ((isset($item->name)) && ($item->name!=''))
| **{{ trans('mail.asset_name') }}** | {{ $item->name }} |
@endif
@if (($item->name!=$item->asset_tag))
| **{{ trans('mail.asset_tag') }}** | {{ $item->asset_tag }} |
@endif
@if (isset($item->model->category))
| **{{ trans('general.category') }}** | {{ $item->model->category->name }} |
@endif
@if (isset($item->manufacturer))
| **{{ trans('general.manufacturer') }}** | {{ $item->manufacturer->name }} |
@endif
@if (isset($item->model))
| **{{ trans('general.asset_model') }}** | {{ $item->model->name }} |
@endif
@if ((isset($item->model->model_number)) && ($item->model->name!=$item->model->model_number))
| **{{ trans('general.model_no') }}** | {{ $item->model->model_number }} |
@endif
@if (isset($item->serial))
| **{{ trans('mail.serial') }}** | {{ $item->serial }} |
@endif
@if (isset($last_checkout))
| **{{ trans('mail.checkout_date') }}** | {{ $last_checkout }} |
@endif
@if (isset($status))
| **{{ trans('general.status') }}** | {{ $status }} |
@endif
@if ((isset($expected_checkin)) && ($expected_checkin!=''))
| **{{ trans('mail.expecting_checkin_date') }}** | {{ $expected_checkin }} |
@endif
@foreach($fields as $field)
@if (($item->{ $field->db_column_name() }!='') && ($field->show_in_email) && ($field->field_encrypted=='0'))
| **{{ $field->name }}** | {{ $item->{ $field->db_column_name() } }} |
@endif
@endforeach
@if ($admin)
| **{{ trans('general.administrator') }}** | {{ $admin->display_name }} |
@endif
@if ($note)
| **{{ trans('mail.additional_notes') }}** | {{ $note }} |
@endif
@endcomponent

@if (($req_accept == 1) && ($eula!=''))
{{ trans('mail.read_the_terms_and_click') }}
@elseif (($req_accept == 1) && ($eula==''))
{{ trans('mail.click_on_the_link_asset') }}
@elseif (($req_accept == 0) && ($eula!=''))
{{ trans('mail.read_the_terms') }}
@endif

@if ($eula)
@component('mail::panel')
{!! $eula !!}
@endcomponent
@endif

@if ($req_accept == 1)
**[✔ {{ trans('mail.i_have_read') }}]({{ $accept_url }})**
@endif


{{ trans('mail.best_regards') }}

{{ $snipeSettings->site_name }}

@endcomponent
