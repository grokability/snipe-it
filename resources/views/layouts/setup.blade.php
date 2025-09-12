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



        <style nonce="{{ csrf_token() }}">
          td, th {
            font-size: 14px;
          }

          .preflight-success {
            color: green;
          }

          .preflight-error {
            color: red;
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


        </style>

    </head>
    <body>
          <div class="container">
              <div class="row">
                  <div class="col-lg-10 col-lg-offset-1">
                    <h1 class="page-header">Snipe-IT {{ trans('general.pre_flight') }}</h1>
                  </div>
                  <div class="col-lg-11 col-lg-offset-1">

                    <div class="row bs-wizard" style="border-bottom:0;">

                      <div class="col-xs-3 bs-wizard-step {{ ($step > 1) ? 'complete':'active' }}">
                        <div class="text-center bs-wizard-stepnum">{{ trans('general.setup_step_1') }}</div>
                        <div class="progress"><div class="progress-bar"></div></div>
                        <a href="{{ route('setup') }}" class="bs-wizard-dot"></a>
                        <div class="bs-wizard-info text-center">{{ trans('general.setup_config_check') }}</div>
                      </div>

                      <div class="col-xs-3 bs-wizard-step {{ ($step == 2) ? 'active': (($step < 2) ? 'disabled' : 'complete')  }}"><!-- complete -->
                        <div class="text-center bs-wizard-stepnum">{{ trans('general.setup_step_2') }}</div>
                        <div class="progress"><div class="progress-bar"></div></div>
                        <a href="{{ route('setup.migrate') }}" class="bs-wizard-dot"></a>
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


                    <div class="panel panel-default">
                        <div class="panel-heading">
                            {{ $section }}
                        </div>
                        <div class="panel-body">
                          <!-- Content -->
                          @yield('content')
                        </div>
                        <div class="panel-footer text-right">
                            @section('button')
                            @show


                        </div>
                    </div>

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
