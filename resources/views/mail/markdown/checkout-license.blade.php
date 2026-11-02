@component('mail::message')
# {{ trans('mail.hello').' '.$target->display_name.','}}

{{ $introduction_line }}

@component('mail::table')
|        |          |
| ------------- | ------------- |
@if (isset($checkout_date))
| **{{ trans('mail.checkout_date') }}** | {{ $checkout_date }} |
@endif
| **{{ trans('general.license') }}** | {{ $license->name}} |
@if (isset($license->manufacturer))
| **{{ trans('general.manufacturer') }}** | {{ $license->manufacturer->name }} |
@endif
@if (isset($license->category))
| **{{ trans('general.category') }}** | {{ $license->category->name }} |
@endif
@if (($target instanceof \App\Models\User && Gate::forUser($target)->allows('viewKeys', $license)) || ($target instanceof \App\Models\Asset && $license_seat->user && Gate::forUser($license_seat->user)->allows('viewKeys', $license)))
| **Key** | {{ $license->getRawOriginal('serial') }} |
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
