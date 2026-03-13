@extends('layouts/basic')


{{-- Page content --}}
@section('content')


    @if ($snipeSettings->custom_forgot_pass_url)
        <!--  The admin settings specify an LDAP password reset URL to let's send them there -->
        <div class="col-md-4 col-md-offset-4" style="margin-top: 20px;">
            <div class="box box-header text-center">
                <h3 class="box-title">
                    <a href="{{ $snipeSettings->custom_forgot_pass_url  }}" rel="noopener">
                        {{ trans('auth/general.ldap_reset_password')  }}
                    </a>
                </h3>
            </div>
        </div>

    @else


    <form class="form" role="form" method="POST" action="{{ url('/password/email') }}">
        {!! csrf_field() !!}
    <div class="container">
        <div class="row">

            <div class="col-md-4 col-md-offset-4">

                <div class="box login-box" style="width: 100%">
                        <div class="box-header with-border">
                            <h2 class="box-title"> {{ trans('auth/general.send_password_link')  }}</h2>
                        </div>


                        <div class="login-box-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <x-icon type="info-circle" />
                                        {!! trans('auth/general.username_help_top') !!}
                                    </div>
                                </div>


                            </div>
                            <div class="row">


                                <!-- Notifications -->
                                @include('notifications')



                                    <div class="form-group{{ $errors->has('username') ? ' has-error' : '' }}">

                                        <div class="col-md-12">
                                            <label for="username"><x-icon type="user" /> {{ trans('admin/users/table.username') }} </label>
                                            <input type="text" class="form-control" name="username" value="{{ old('username') }}" placeholder="{{ trans('admin/users/table.username') }}" aria-label="username">
                                            {!! $errors->first('username', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
                                        </div>
                                    </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <br>
                                    <!-- show help text toggle -->
                                    <a href="#" id="show">
                                        <x-icon type="caret-right" />
                                        {{ trans('general.show_help') }}
                                    </a>

                                    <!-- hide help text toggle -->
                                    <a href="#" id="hide" style="display:none">
                                        <x-icon type="caret-up" />
                                        {{ trans('general.hide_help') }}
                                    </a>

                                    <!-- help text  -->
                                    <p class="help-block" id="help-text" style="display:none">
                                        {!! trans('auth/general.username_help_bottom') !!}
                                    </p>

                                </div>
                            </div>
                        </div>

                        <div class="box-footer">
                            <button type="submit" class="btn btn-lg btn-primary btn-block">
                                {{ trans('auth/general.email_reset_password')  }}
                            </button>
                        </div>

                    </div>
            </div>
        </div>
    </div>

    </form>

    @endif
@stop

@push('js')
    <script nonce="{{ csrf_token() }}">
        $(document).ready(function () {
            $("#show").click(function(){
                $("#help-text").fadeIn(500);
                $("#show").hide();
                $("#hide").show();
            });

            $("#hide").click(function(){
                $("#help-text").fadeOut(300);
                $("#show").show();
                $("#hide").hide();
            });
        });
    </script>
@endpush

