@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.editprofile') }}
@stop

{{-- Account page content --}}
@section('content')

<div class="row">
  <div class="col-md-9">
  <form method="POST" action="{{ route('profile.update') }}" accept-charset="UTF-8" class="form-horizontal" autocomplete="off" enctype="multipart/form-data">
  <!-- CSRF Token -->
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
    <div class="box box-default">
      <div class="box-body">
        <!-- First Name -->
        <div class="form-group {{ $errors->has('first_name') ? ' has-error' : '' }}">
          <label for="first_name" class="col-md-3 control-label">{{ trans('general.first_name') }}
          </label>
          <div class="col-md-8 required">
            <input class="form-control" type="text" name="first_name" id="first_name" value="{{ old('first_name', $user->first_name) }}" required />
            {!! $errors->first('first_name', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
          </div>
        </div>

        <!-- Last Name -->
        <div class="form-group {{ $errors->has('last_name') ? ' has-error' : '' }}">
          <label for="last_name" class="col-md-3 control-label">
            {{ trans('general.last_name') }}
          </label>
          <div class="col-md-8 required">
            <input class="form-control" type="text" name="last_name" id="last_name" value="{{ old('last_name', $user->last_name) }}" />
            {!! $errors->first('last_name', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
          </div>
        </div>


        @can('self.edit_location')
        <!-- Location -->
        @include ('partials.forms.edit.location-profile-select', ['translated_name' => trans('general.location')])
        @endcan


        <!-- Language -->
        <div class="form-group {{ $errors->has('locale') ? 'has-error' : '' }}">
          <label class="col-md-3 control-label" for="locale">{{ trans('general.language') }}</label>
          <div class="col-md-7">

            @if (!config('app.lock_passwords'))
              <x-input.locale-select name="locale" :selected="old('locale', $user->locale)"/>
              {!! $errors->first('locale', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
            @else
              <p class="help-block">{{ trans('general.feature_disabled') }}</p>
            @endif

          </div>
        </div>

        @if ($snipeSettings->allow_user_skin=='1')
        <!-- Skin -->
        <div class="form-group {{ $errors->has('skin') ? 'error' : '' }}">
          <label for="skin" class="col-md-3 control-label">
            {{ trans('general.skin') }}
          </label>
          <div class="col-md-8">
            <x-input.skin name="skin" :selected="old('skin', $user->skin)" :include-blank-option="true"/>
            {!! $errors->first('skin', '<span class="alert-msg">:message</span>') !!}
          </div>
        </div>
        @endif

        <!-- Phone -->
        <div class="form-group {{ $errors->has('phone') ? 'has-error' : '' }}">
          <label class="col-md-3 control-label" for="phone">{{ trans('admin/users/table.phone') }}</label>
          <div class="col-md-4">
            <input class="form-control" type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" />
            {!! $errors->first('phone', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
          </div>
        </div>

        <!-- Website URL -->
        <div class="form-group {{ $errors->has('website') ? ' has-error' : '' }}">
          <label for="website" class="col-md-3 control-label">{{ trans('general.website') }}</label>
          <div class="col-md-8">
            <input class="form-control" type="text" name="website" id="website" value="{{ old('website', $user->website) }}" />
            {!! $errors->first('website', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
          </div>
        </div>

        <div class="form-group">
          <div class="col-md-9 col-md-offset-3">
            <label class="form-control">
              <input type="checkbox" name="enable_sounds" value="1" {{ old('enable_sounds', $user->enable_sounds) ? 'checked' : '' }}>
              {{ trans('account/general.enable_sounds') }}
            </label>
          </div>
        </div>

        <div class="form-group">
          <div class="col-md-9 col-md-offset-3">
            <label class="form-control">
              <input type="checkbox" name="enable_confetti" value="1" {{ old('enable_confetti', $user->enable_confetti) ? 'checked' : '' }}>
              {{ trans('account/general.enable_confetti') }}
            </label>
          </div>
        </div>



        <!-- Avatar -->
        @if (($user->avatar) && ($user->avatar!=''))
          <div class="form-group{{ $errors->has('image_delete') ? ' has-error' : '' }}">
            <div class="col-md-9 col-md-offset-3">
              @if (!$user->isAvatarExternal())
              <label for="image_delete" class="form-control">
                <input type="checkbox" name="image_delete" id="image_delete" value="1" @checked(old('image_delete')) aria-label="image_delete">
                {{ trans('general.image_delete') }}
              </label>
              {!! $errors->first('image_delete', '<span class="alert-msg">:message</span>') !!}
              @endif
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-9 col-md-offset-3">
              <img src="{{ (($user->isAvatarExternal()) ? $user->avatar : Storage::disk('public')->url(app('users_upload_path').e($user->avatar))) }}" class="img-responsive">
              {!! $errors->first('image_delete', '<span class="alert-msg">:message</span>') !!}
            </div>
          </div>

          @else
          <!-- Gravatar Email -->
          <div class="form-group {{ $errors->has('gravatar') ? ' has-error' : '' }}">
            <label for="gravatar" class="col-md-3 control-label">{{ trans('general.gravatar_email') }}
              <small>(Private)</small>
            </label>
            <div class="col-md-8">
              <input class="form-control" type="text" name="gravatar" id="gravatar" value="{{ old('gravatar', $user->gravatar) }}" />
              {!! $errors->first('gravatar', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
              <p style="padding-top: 3px;">
                <img src="//secure.gravatar.com/avatar/{{ md5(strtolower(trim($user->gravatar))) }}" width="30" height="30" alt="{{ $user->display_name }} avatar image">
                {!! trans('general.gravatar_url') !!}
              </p>
            </div>
          </div>

        @endif


        @include ('partials.forms.edit.image-upload', ['fieldname' => 'avatar', 'image_path' => app('users_upload_path')])


        <!-- Two factor opt in -->
        @if ($snipeSettings->two_factor_enabled=='1')
        <div class="form-group {{ $errors->has('two_factor_optin') ? 'has-error' : '' }}">
          <div class="col-md-7 col-md-offset-3">
              <label
                  for="two_factor_optin"
                  @class([
                    'form-control',
                    'form-control--disabled' => auth()->user()->cannot('self.two_factor'),
                  ])
              >
                <input
                    type="checkbox"
                    name="two_factor_optin"
                    id="two_factor_optin"
                    value="1"
                    @checked(old('two_factor_optin', $user->two_factor_optin))
                    @disabled(auth()->user()->cannot('self.two_factor'))
                >
                {{ trans('admin/settings/general.two_factor_enabled_text') }}
              </label>
            @can('self.two_factor')
              <p class="help-block">{{ trans('admin/settings/general.two_factor_enabled_warning') }}</p>
            @else
              <p class="help-block">{{ trans('admin/settings/general.two_factor_enabled_edit_not_allowed') }}</p>
            @endcan
            @if (config('app.lock_passwords'))
              <p class="help-block">{{ trans('general.feature_disabled') }}</p>
            @endif
          </div>
        </div>
        @endif



      </div> <!-- .box-body -->
      <div class="text-right box-footer">
        <a class="btn btn-link" href="{{ URL::previous() }}">{{ trans('button.cancel') }}</a>
        <button type="submit" class="btn btn-primary"><x-icon type="checkmark" /> {{ trans('general.save') }}</button>
      </div>
    </div> <!-- .box-default -->
    </form>
  </div> <!-- .col-md-9 -->
</div> <!-- .row-->

@stop
