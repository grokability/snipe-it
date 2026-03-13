@component('mail::message')
{{ trans('mail.hello') }} {{ $first_name }} {{$last_name}},

{{ trans('mail.login') }}: {{ $username }} <br>
{{ trans('mail.password') }}: {{ $password }}

@component('mail::button', ['url' => $url])
Go To {{$snipeSettings->site_name}}
@endcomponent

{{ trans('mail.best_regards') }} <br>
@if ($snipeSettings->show_url_in_emails=='1')
    <p><a href="{{ config('app.url') }}">{{ $snipeSettings->site_name }}</a></p>
@else
    <p>{{ $snipeSettings->site_name }}</p>
@endif
@endcomponent
