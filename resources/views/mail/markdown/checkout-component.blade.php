@component('mail::message')
# {{ trans('mail.hello') }} {{ $target->display_name }},

{{ trans_choice('mail.new_item_checked', $qty) }}

@component('mail::table')
|        |          |
| ------------- | ------------- |
@if (isset($checkout_date))
| **{{ trans('mail.checkout_date') }}** | {{ $checkout_date }} |
@endif
| **{{ trans('general.component') }}** | {{ $item->name }} |
@if (isset($qty))
| **{{ trans('general.qty') }}** | {{ $qty }} |
@endif
@if (isset($item->manufacturer))
| **{{ trans('general.manufacturer') }}** | {{ $item->manufacturer->name }} |
@endif
@if ($note)
| **{{ trans('mail.additional_notes') }}** | {{ $note }} |
@endif
@if ($admin)
| **{{ trans('general.administrator') }}** | {{ $admin->display_name }} |
@endif
@endcomponent

{{-- Accept-required copy is gated on $accept_url so the "please
     read and click" line and the accept button below only render
     when there's an actual acceptance record for the recipient to
     click through to. Otherwise (e.g. a component checked out to
     an asset whose assigned user could not be resolved) the email
     used to render an empty [text]() markdown link that read as
     broken. See GH #19570. --}}
@if (($req_accept == 1) && ($accept_url) && ($eula!=''))
    {{ trans('mail.read_the_terms_and_click') }}
@elseif (($req_accept == 1) && ($accept_url) && ($eula==''))
    {{ trans('mail.click_on_the_link_asset') }}
@elseif (($req_accept == 0) && ($eula!=''))
    {{ trans('mail.read_the_terms') }}
@endif

@if ($eula)
@component('mail::panel')
    {!! $eula !!}
@endcomponent
@endif

@if (($req_accept == 1) && ($accept_url))
**[✔ {{ trans('mail.i_have_read') }}]({{ $accept_url }})**
@endif


{{ trans('mail.best_regards') }}

{{ $snipeSettings->site_name }}

@endcomponent
