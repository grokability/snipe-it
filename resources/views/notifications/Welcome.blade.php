@component('mail::message')
{{ trans('mail.hello') }} {{ $first_name }} {{$last_name}},

{{ trans('mail.admin_has_created', ['web' => $snipeSettings->site_name]) }}

<strong>{{ trans('mail.username') }}: </strong> {{ $username }}<br>

@component('mail::button',
    ['url' => url(route('password.reset', ['token' => $token, 'email' => $email]))])
    {{ trans('general.set_password') }}
@endcomponent

<p>{{ trans('auth/general.invite_password_expires', ['expire_date' => $expire_date]) }}: <a href="{{ url(route('password.request')) }}">{{ url(route('password.request')) }}</a>
</p>

{{ trans('mail.best_regards') }} <br>
@if ($snipeSettings->show_url_in_emails=='1')
    <p><a href="{{ config('app.url') }}">{{ $snipeSettings->site_name }}</a></p>
@else
    <p>{{ $snipeSettings->site_name }}</p>
@endif
@endcomponent
