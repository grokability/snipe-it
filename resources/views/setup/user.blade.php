@extends('layouts/setup')

@section('title')
{{ trans('admin/users/general.create_user') }} ::
@parent
@stop

{{-- Page content --}}
@section('content')

<p>{{ trans('admin/users/general.create_user_page_explanation') }}</p>

<form action="{{ route('setup.user.save') }}" method="POST">
  {{ csrf_field() }}

  <div class="col-lg-12" style="padding-top: 20px;">

    <!-- Site Name -->
    <div class="row">
      <div class="form-group col-lg-12 required {{ $errors->has('site_name') ? 'error' : '' }}">
        <label for="site_name">
          {{ trans('general.site_name') }}
        </label>
        <input class="form-control" placeholder="Snipe-IT Asset Management" required="" name="site_name" type="text" value="{{ old('site_name') }}">

        {!! $errors->first('site_name', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
      </div>
    </div>

  <div class="row">

    <!-- Language -->
    <div class="form-group col-lg-6{{$errors->has('default_language') ? ' error' : ''}}">
      <label for="locale">
        {{ trans('admin/settings/general.default_language') }}
      </label>
      <x-input.locale-select name="locale" :selected="old('locale', 'en-US')" />
      {!! $errors->first('locale', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
    </div>

    <!-- Currency -->
    <div class="form-group col-lg-6{{$errors->has('default_currency') ? ' error' : ''}}">
      <label for="default_currency">{{ trans('admin/settings/general.default_currency') }}</label>
      <input class="form-control" placeholder="USD" maxlength="3" style="width: 60px;" name="default_currency" type="text" id="default_currency" value="{{ old('default_currency') }}">

      {!! $errors->first('default_currency', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
    </div>

  </div>

  <div class="row">

    <div class="form-group col-lg-6">

      <label class="form-control form-control">
        <input type="checkbox" value="1" name="auto_increment_assets">{{trans('admin/settings/general.auto_increment_assets')}}
      </label>

    </div>

    <!-- Multi Company Support -->
    <div class="form-group col-lg-6">
      <label class="form-control form-control">
        <input type="checkbox" value="1" name="full_multiple_companies_support">  {{ trans('admin/settings/general.full_multiple_companies_support_text') }}
      </label>
    </div>


  </div>

  <div class="row">

    <div class="form-group col-lg-6{{ $errors->has('auto_increment_prefix') ? ' error' : '' }}">
      <label for="auto_increment_prefix">{{ trans('admin/settings/general.auto_increment_prefix') }}</label>
      <input class="form-control" name="auto_increment_prefix" type="text" id="auto_increment_prefix" value="{{ old('auto_increment_prefix') }}">

      {!! $errors->first('auto_increment_prefix', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
    </div>

    <div class="form-group col-lg-6{{ $errors->has('zerofill_count') ? ' error' : '' }}">
      <label for="zerofill_count">{{ trans('admin/settings/general.zerofill_count') }}</label>
      <input class="form-control" name="zerofill_count" type="text" value="{{ old('zerofill_count', 5) }}" id="zerofill_count">

      {!! $errors->first('zerofill_count', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
    </div>
  </div>


    <!-- email domain -->
    <div class="row">
      <div class="form-group col-lg-6 required {{ $errors->has('email_domain') ? 'error' : '' }}">
        <label for="email_domain">{{ trans('general.email_domain') }}</label>
        <input class="form-control" placeholder="example.com" required="" name="email_domain" type="text" id="email_domain" value="{{ old('email_domain') }}">
        <span class="help-block">{{ trans('general.email_domain_help')  }}</span>

        {!! $errors->first('email_domain', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
      </div>

      <!-- email format  -->
      <div class="form-group col-lg-6 {{ $errors->has('email_format') ? 'error' : '' }}">
        <label for="email_format">{{ trans('admin/settings/general.email_formats.email_format') }}</label>
        {!! Form::username_format('email_format', old('email_format', 'filastname'), 'select2') !!}
        {!! $errors->first('email_format', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
      </div>
    </div>

    <!-- Name -->
    <div class="row">
      <!-- first name -->
      <div class="form-group col-lg-6">
        <label for="first_name">{{ trans('general.first_name') }}</label>
        <input class="form-control" placeholder="Jane" required="" name="first_name" type="text" id="first_name" value="{{ old('first_name') }}">
        {!! $errors->first('first_name', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
      </div>

      <!-- last name -->
      <div class="form-group col-lg-6 required {{ $errors->has('last_name') ? 'error' : '' }}">
        <label for="last_name">{{ trans('general.last_name') }}</label>
        <input class="form-control" placeholder="Smith" required="" name="last_name" type="text" id="last_name" value="{{ old('last_name') }}">
        {!! $errors->first('last_name', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
      </div>
    </div>

    <div class="row">
      <!-- email-->
      <div class="form-group col-lg-6{{ $errors->has('email') ? 'error' : '' }}">
        <label for="email">{{ trans('admin/users/table.email') }}</label>
        <input class="form-control" type="email" name="email" id="email" value="{{ old('email', config('mail.from.address')) }}" placeholder="you@example.com" required>
        {!! $errors->first('email', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
      </div>

      <!-- username -->
      <div class="form-group col-lg-6{{  (Helper::checkIfRequired(\App\Models\User::class, 'username')) ? ' required' : '' }} {{ $errors->has('username') ? 'error' : '' }}">
        <label for="username">{{ trans('admin/users/table.username') }}</label>
        <input class="form-control" placeholder="jsmith" required="" name="username" type="text" id="username" value="{{ old('username') }}">
        {!! $errors->first('username', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
      </div>
    </div>

    <div class="row">
      <!-- password -->
      <div class="form-group col-lg-6{{  (Helper::checkIfRequired(\App\Models\User::class, 'password')) ? ' required' : '' }} {{ $errors->has('password') ? 'error' : '' }}">
        <label for="password">{{ trans('admin/users/table.password') }}</label>
        <input class="form-control" type="password" name="password" id="password" value="" required>
        {!! $errors->first('password', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
      </div>

      <!-- password confirm -->
      <div class="form-group col-lg-6{{  (Helper::checkIfRequired(\App\Models\User::class, 'password')) ? ' required' : '' }} {{ $errors->has('password_confirm') ? 'error' : '' }}">
        <label for="password_confirmation">{{ trans('admin/users/table.password_confirm') }}</label>
        <input class="form-control" type="password" name="password_confirmation" id="password_confirmation" value="" required>
        {!! $errors->first('password_confirmation', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
      </div>
    </div>

    <!-- Email credentials -->
    <div class="form-group col-lg-12">
      <label class="form-control form-control">
        <input type="checkbox" value="1" name="email_creds">{{ trans('admin/users/general.email_credentials_text') }}
      </label>
    </div>
  </div> <!--/.COL-LG-12-->
@stop

@section('button')
  <button class="btn btn-primary">{{ trans('admin/users/general.next_save_user') }}</button>
</form>
@parent
@stop
