<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<html>
    <head>
      <title>
        @section('title')
         Snipe-IT {{ trans('general.setup') }}
        @show
      </title>
        <link rel="stylesheet" href="{{ url(mix('css/dist/all.css')) }}">



        <script nonce="{{ csrf_token() }}">
            window.snipeit = {
                settings: {
                    "per_page": 20
                }
            };
        </script>



        <style>
          td, th {
            font-size: 14px;
          }

          .preflight-success {
            color: green;
          }

          .preflight-error {
            color: #b60707;
          }
          .preflight-info {
              font-size: 18px;
          }

          .preflight-warning {
            color: orange;
          }

          .page-header {
            font-size: 280%;
          }

          h3 {
            font-size: 250%;
          }

          .alert {
            font-size: 16px;
          }

          body {
              background-color: #ecf0f5;
          }

          .bs-wizard-info {
              color: #959495 !important;
          }

          h4 {
              line-height: 25px;
          }

          p, li {
              font-size: 15px;
              line-height: 25px;
          }

          li {
              display: block;
          }
        </style>

    </head>
    <body>
          <div class="container">
              <div class="row">
                  <div class="col-lg-10 col-lg-offset-1">
                    <h1 class="page-header"><img src="../img/logo.png" style="height: 65px;" alt="Snipe-IT logo"> {{ trans('general.pre_flight') }}</h1>
                  </div>
                  <div class="col-lg-12">

                    <div class="row bs-wizard" style="border-bottom:0;">

                      <div class="col-xs-3 bs-wizard-step {{ ($step > 1) ? 'complete':'active' }}">
                        <div class="text-center bs-wizard-stepnum">{{ trans('general.setup_step_1') }}</div>
                        <div class="progress"><div class="progress-bar"></div></div>
                        <a href="{{ route('setup') }}" class="bs-wizard-dot"></a>
                        <div class="bs-wizard-info text-center" style="padding-left: 90px;">{{ trans('general.setup_config_check') }}</div>
                      </div>

                      <div class="col-xs-3 bs-wizard-step {{ ($step == 2) ? 'active': (($step < 2) ? 'disabled' : 'complete')  }}"><!-- complete -->
                        <div class="text-center bs-wizard-stepnum">{{ trans('general.setup_step_2') }}</div>
                        <div class="progress"><div class="progress-bar"></div></div>
                        <a href="#" class="bs-wizard-dot"></a>
                        <div class="bs-wizard-info text-center">{{ trans('general.setup_create_database') }}</div>
                      </div>

                      <div class="col-xs-3 bs-wizard-step {{ ($step == 3) ? 'active': (($step < 3) ? 'disabled' : 'complete')  }}"><!-- complete -->
                        <div class="text-center bs-wizard-stepnum">{{ trans('general.setup_step_3') }}</div>
                        <div class="progress"><div class="progress-bar"></div></div>
                        <a href="{{ route('setup.user') }}" class="bs-wizard-dot"></a>
                        <div class="bs-wizard-info text-center">{{ trans('general.setup_create_admin') }}</div>
                      </div>

                      <div class="col-xs-3 bs-wizard-step {{ ($step == 4) ? 'active': (($step < 4) ? 'disabled' : 'complete')  }}"><!-- active -->
                        <div class="text-center bs-wizard-stepnum">{{ trans('general.setup_step_4') }}</div>
                        <div class="progress"><div class="progress-bar"></div></div>
                        <a href="#" class="bs-wizard-dot"></a>
                        <div class="bs-wizard-info text-center">{{ trans('general.setup_done') }}</div>
                      </div>
                  </div>
                </div>


                  <div class="col-lg-10 col-lg-offset-1" style="padding-top: 50px;">


                    <div class="box box-default">
                        <div class="box-header with-border">
                            <h4><i class="{{ $icon ?? '' }}" style="--fa-animation-duration: 10s; --fa-animation-iteration-count: 3;"></i> {{ $section }}</h4>
                        </div>
                        <div class="box-body">

                            @include('notifications')


                          <!-- Content -->
                          @yield('content')
                        </div>
                        <div class="box-footer text-right">
                            @section('button')
                            @show
                        </div>
                    </div>

                      <strong>Snipe-IT {{ trans('general.version') }}</strong> {{ config('version.app_version') }} -
                      {{ trans('general.build') }} {{ config('version.build_version') }} ({{ config('version.branch') }})

                  </div>
              </div>
          </div>
          
        {{-- Javascript files --}}
          <script src="{{ url('js/dist/all.js') }}" nonce="{{ csrf_token() }}"></script>

        <script nonce="{{ csrf_token() }}">
            $(function () {
                $(".select2").select2();
            });
        </script>
          @section('moar_scripts')
          @show

    </body>
</html>
