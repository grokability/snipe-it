@extends('layouts/basic')


{{-- Page content --}}
@section('content')
    <div class="container">
        <div class="row">



            <div class="col-md-4 col-md-offset-4">
                <form role="form" action="{{ route('two-factor') }}" method="POST">

                    <div class="box login-box">
                        <div class="box-header">
                            <h2 class="box-title"> {{ trans('admin/settings/general.two_factor_enrollment')  }}</h2>
                        </div>


                        <div class="login-box-body">
                            <div class="row">

                                <!-- Notifications -->
                                @include('notifications')

                                <div class="col-md-12">
                                    {{ trans('admin/settings/general.two_factor_enrollment_text') }}
                                </div>

                                <div class="col-md-12 text-center">
                                   {!!  $barcode_obj->getHtmlDiv() !!}
                                </div>

                            </div>
                            <div class="row">

                                <div class="col-md-12">
                            <!-- CSRF Token -->
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" />

                            <fieldset>
                                <div class="form-group{{ $errors->has('secret') ? ' has-error' : '' }}">
                                    <input class="form-control" placeholder="{{ trans('admin/settings/general.two_factor_secret')  }}" name="two_factor_secret" type="text" aria-label="two_factor_secret" autofocus>
                                    {!! $errors->first('two_factor_secret', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                </div>
                            </fieldset>
                                </div>
                         </div>



                        </div>
                        <div class="box-footer">
                            <button class="btn btn-lg btn-primary btn-block">{{ trans('admin/settings/general.two_factor_config_complete')  }}</button>
                        </div>

                    </div>
            </div>
            </form>
        </div>
    </div>


@stop
