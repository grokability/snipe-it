@component('mail::message')
# {{ trans('mail.hello').' '.$target.','}}

{{ $introduction_line }}

@if (($snipeSettings->show_images_in_email =='1') && $item->getImageUrl())
<center><img src="{{ $item->getImageUrl() }}" alt="Accessory" style="max-width: 570px;"></center>
@endif

@component('mail::table')
|        |          |
| ------------- | ------------- |
@if (isset($checkout_date))
| **{{ trans('mail.checkout_date') }}** | {{ $checkout_date }} |
@endif
| **{{ trans('general.accessory') }}** | {{ $item->name }} |
@if (isset($item->category))
    | **{{ trans('general.category') }}** | {{ $item->category->name }} |
@endif
@if (isset($item->model_number))
    | **{{ trans('general.model_no') }}** | {{ $item->model_number }} |
@endif
@if (isset($item->manufacturer))
| **{{ trans('general.manufacturer') }}** | {{ $item->manufacturer->name }} |
@endif
@if (isset($item->model_no))
| **{{ trans('general.model_no') }}** | {{ $item->model_no }} |
@endif
@if (isset($checkout_qty))
| **{{ trans('general.qty') }}** | {{ $checkout_qty }} |
@endif
@if ($note)
| **{{ trans('mail.additional_notes') }}** | {{ $note }} |
@endif
@if ($admin)
| **{{ trans('general.administrator') }}** | {{ $admin->display_name }} |
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
