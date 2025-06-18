@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.admin') }}
@parent
@stop

@section('header_right')



  <!-- search filter box -->
  <div class="pull-right">


    <form onsubmit="return false;" role="search" aria-label="Admin Options" id="setting-search">
      <div class="btn-group">
        <input id="searchinput" name="search" type="search" class="search form-control" placeholder="{{ trans('admin/settings/general.filter_by_keyword') }}" aria-label="keyword search">
        <span id="searchclear" class="fas fa-times" aria-hidden="true"></span>
        <button type="submit" disabled style="display: none" aria-hidden="true"></button>
      </div>
    </form>




  </div>
  <!--/ search filter box -->
@stop


{{-- Page content --}}
@section('content')



  <style>
    #searchinput {
      width: 200px;
    }
    #searchclear {
      position: absolute;
      right: 5px;
      top: 0;
      bottom: 0;
      height: 14px;
      margin: auto;
      font-size: 14px;
      cursor: pointer;
      color: #ccc;
    }
  </style>

  <div class="row">
    <!-- search filter list -->
    <div class="list clearfix">

        <div class="col-md-4 col-lg-3 col-sm-6 col-xl-1">
          <div class="admin box box-default">
            <div class="box-body text-center">
              <h5>
              <a href="{{ route('settings.branding.index') }}" class="settings_button">
                <x-icon type="branding" class="fa-4x"/>
                <br><br>
                <span class="name">{{ trans('admin/settings/general.brand') }}</span>
                <span class="keywords" aria-hidden="true" style="display:none">{{ trans('admin/settings/general.keywords.brand') }}</span>
              </a>
              </h5>
              <p class="index-block">{{ trans('admin/settings/general.brand_help') }}</p>
            </div>
          </div>
        </div>


        <div class="col-md-4 col-lg-3 col-sm-6 col-xl-1">
          <div class="admin box box-default">
            <div class="box-body text-center">
              <h5>
                <a href="{{ route('settings.general.index') }}" class="settings_button">
                  <x-icon type="general-settings" class="fa-4x"/>
                  <br><br>
                  <span class="name"> {{ trans('admin/settings/general.general_settings') }}</span>
                  <span class="keywords" aria-hidden="true" style="display:none">{{ trans('admin/settings/general.keywords.general_settings') }}</span>
                </a>
              </h5>
              <p class="index-block">{{ trans('admin/settings/general.general_settings_help') }}</p>
            </div>
          </div>
        </div>


        <div class="col-md-4 col-lg-3 col-sm-6 col-xl-1">
          <div class="admin box box-default">
            <div class="box-body text-center">
              <h5>
                <a href="{{ route('settings.security.index') }}" class="settings_button">
                  <x-icon type="locked" class="fa-4x"/>
                  <br><br>
                  <span class="name">{{ trans('admin/settings/general.security') }}</span>
                  <span class="keywords" aria-hidden="true" style="display:none">{{ trans('admin/settings/general.keywords.security') }}</span>
                </a>
              </h5>
              <p class="index-block">{{ trans('admin/settings/general.security_help') }}</p>
            </div>
          </div>
        </div>

        <div class="col-md-4 col-lg-3 col-sm-6 col-xl-1">
          <div class="admin box box-default">
            <div class="box-body text-center">
              <h5>
                <a href="{{ route('groups.index') }}" class="settings_button">
                  <x-icon type="groups" class="fa-4x"/>
                  <br><br>
                  <span class="name">{{ trans('general.groups') }}</span>
                  <span class="keywords" aria-hidden="true" style="display:none"> {{ trans('admin/settings/general.keywords.groups') }}</span>
                  </a>
              </h5>
              <p class="index-block">{{ trans('admin/settings/general.groups_help') }}</p>
            </div>
          </div>
        </div>


        <div class="col-md-4 col-lg-3 col-sm-6 col-xl-1">
          <div class="admin box box-default">
            <div class="box-body text-center">
              <h5>
                <a href="{{ route('settings.localization.index') }}" class="settings_button">
                  <x-icon type="globe-us" class="fa-4x"/>
                  <br><br>
                  <span class="name">{{ trans('admin/settings/general.localization') }}</span>
                  <span class="keywords" aria-hidden="true" style="display:none"> {{ trans('admin/settings/general.keywords.localization') }}</span>
                </a>
              </h5>
              <p class="index-block">{{ trans('admin/settings/general.localization_help') }}</p>

            </div>
          </div>
        </div>


        <div class="col-md-4 col-lg-3 col-sm-6 col-xl-1">
          <div class="admin box box-default">
            <div class="box-body text-center">
              <h5>
                <a href="{{ route('settings.alerts.index') }}" class="settings_button">
                  <x-icon type="bell" class="fa-4x"/>
                  <br><br>
                  <span class="name">{{ trans('admin/settings/general.notifications') }}</span>
                  <span class="keywords" aria-hidden="true" style="display:none"> {{ trans('admin/settings/general.keywords.notifications') }}</span>

                </a>
              </h5>
              <p class="index-block">{{ trans('admin/settings/general.notifications_help') }}</p>
            </div>
          </div>
        </div>

        <div class="col-md-4 col-lg-3 col-sm-6 col-xl-1">
          <div class="admin box box-default">
            <div class="box-body text-center">
              <h5>
                <a href="{{ route('settings.slack.index') }}" class="settings_button">
                  <x-icon type="hashtag" class="fa-4x"/>
                  <br><br>
                  <span class="name">{{ trans('admin/settings/general.integrations') }}</span>
                </a>
              </h5>
              <p class="index-block">{{ trans('admin/settings/general.webhook_help') }}</p>
            </div>
          </div>
        </div>

        <div class="col-md-4 col-lg-3 col-sm-6 col-xl-1">
          <div class="admin box box-default">
            <div class="box-body text-center">
              <h5>
                <a href="{{ route('settings.asset_tags.index') }}" class="settings_button">
                  <x-icon type="asset-tags" class="fa-4x"/>
                  <br><br>
                  <span class="name">{{ trans('general.asset_tags') }}</span>
                </a>
              </h5>
              <p class="index-block">{{ trans('admin/settings/general.asset_tags_help') }}</p>
            </div>
          </div>
        </div>

        <div class="col-md-4 col-lg-3 col-sm-6 col-xl-1">
          <div class="admin box box-default">
            <div class="box-body text-center">
              <h5>
                <a href="{{ route('settings.labels.index') }}" class="settings_button">
                  <x-icon type="labels" class="fa-4x"/>
                  <br><br>
                  <span class="name">{{ trans('admin/settings/general.labels') }}</span>
                  <span class="keywords" aria-hidden="true" style="display:none"> {{ trans('admin/settings/general.keywords.labels') }}</span>
                </a>
              </h5>
              <p class="index-block">{!! trans('admin/settings/general.labels_help') !!}</p>
            </div>
          </div>
        </div>


        <div class="col-md-4 col-lg-3 col-sm-6 col-xl-1">
          <div class="admin box box-default">
            <div class="box-body text-center">
              <h5>
                <a href="{{ route('settings.ldap.index') }}" class="settings_button">
                  <x-icon type="ldap" class="fa-4x"/>
                  <br><br>
                  <span class="name">{{ trans('admin/settings/general.ldap') }}</span>
                </a>
              </h5>
              <p class="index-block">{{ trans('admin/settings/general.ldap_help') }}</p>
            </div>
          </div>
        </div>

      <div class="col-md-4 col-lg-3 col-sm-6 col-xl-1">
        <div class="admin box box-default">
          <div class="box-body text-center">
            <h5>
              <a href="{{ route('settings.google.index') }}" class="settings_button">
                <x-icon type="google" class="fa-4x"/>
                <br><br>
                <span class="name">Google</span>
              </a>
            </h5>
            <p class="index-block">{{ trans('admin/settings/general.google_login') }}</p>
          </div>
        </div>
      </div>

      <div class="col-md-4 col-lg-3 col-sm-6 col-xl-1">
        <div class="admin box box-default">
          <div class="box-body text-center">
            <h5>
              <a href="{{ route('settings.saml.index') }}" class="settings_button">
                <x-icon type="saml" class="fa-4x"/>
                <br><br>
                <span class="name">{{ trans('admin/settings/general.saml') }}</span>
              </a>
            </h5>
            <p class="index-block">{{ trans('admin/settings/general.saml_help') }}</p>
          </div>
        </div>
      </div>

        <div class="col-md-4 col-lg-3 col-sm-6 col-xl-1">
          <div class="admin box box-default">
            <div class="box-body text-center">
              <h5>
                <a href="{{ route('settings.backups.index') }}" class="settings_button">
                  <x-icon type="backups" class="fa-4x"/>
                  <br><br>
                  <span class="name">{{ trans('admin/settings/general.backups') }}</span>
                </a>
              </h5>
              <p class="index-block">{!! trans('admin/settings/general.backups_help') !!}</p>
            </div>
          </div>
        </div>


      <div class="col-md-4 col-lg-3 col-sm-6 col-xl-1">
        <div class="admin box box-default">
          <div class="box-body text-center">
            <h5>
              <a href="{{ route('settings.logins.index') }}" class="settings_button">
                <x-icon type="logins" class="fa-4x"/>
                <br><br>
                <span class="name">{{ trans('admin/settings/general.login') }}</span>
              </a>
            </h5>
            <p class="index-block">{{ trans('admin/settings/general.login_help') }} </p>
          </div>
        </div>
      </div>

        <div class="col-md-4 col-lg-3 col-sm-6 col-xl-1">
          <div class="admin box box-default">
            <div class="box-body text-center">
              <h5>
              <a href="{{ route('settings.oauth.index') }}" class="settings_button">
                <x-icon type="oauth" class="fa-4x"/>
                <br><br>
                <span class="name">{{  trans('admin/settings/general.oauth') }}</span>
              </a>
              </h5>
              <p class="index-block">{{  trans('admin/settings/general.oauth_help') }}</p>
            </div>
          </div>
        </div>

        @if (config('app.debug')=== true)
          <div class="col-md-4 col-lg-3 col-sm-6 col-xl-1">
            <div class="admin box box-default">
              <div class="box-body text-center">
                <h5>
                  <a href="{{ route('settings.phpinfo.index') }}" class="settings_button">
                    <i class="fab fa-php fa-4x" aria-hidden="true"></i>
                    <br><br>
                    <span class="name">{{ trans('admin/settings/general.php_overview') }}</span>
                    <span class="keywords" aria-hidden="true" style="display:none">{{ trans('admin/settings/general.keywords.php_overview') }}</span>
                  </a>
                </h5>
                <p class="index-block">{{ trans('admin/settings/general.php_overview_help') }}</p>
              </div>
            </div>
          </div>
        @endif


    <div class="col-md-4 col-lg-3 col-sm-6 col-xl-1">
      <div class="admin box box-danger">
        <div class="box-body text-center">
          <h5>
            <a href="{{ route('settings.purge.index') }}" class="link-danger">
              <i class="fas fa-trash fa-4x" aria-hidden="true"></i>
              <br><br>
              <span class="name">{{ trans('admin/settings/general.purge') }}</span>
              <span class="keywords" aria-hidden="true" style="display:none">{{ trans('admin/settings/general.keywords.purge') }}</span>
            </a>
          </h5>
          <p class="index-block">{{ trans('admin/settings/general.purge_help') }}</p>
        </div>
      </div>
    </div>
  </div>
</div>




<div class="row">
  <div class="col-md-12">
    <div class="box box-default">
      <div class="box-header">
        <h2 class="box-title">{{ trans('admin/settings/general.system') }}</h2>
      </div>
      <div class="box-body">
        <div class="col-md-12" style="margin-right:4px;">
        <div class="row row-new-striped" style="line-height: 23px;">

          <!-- row -->
          <div class="row">
            <div class="col-md-2" style="padding-top: 3px; padding-bottom: 3px;">
              <strong>{{ trans('admin/settings/general.snipe_version') }}:</strong>
            </div>
            <div class="col-md-4" style="padding-top: 3px; padding-bottom: 3px;">
              {{ config('version.app_version') }}  build {{ config('version.build_version') }} ({{ config('version.hash_version') }})
            </div>

            <div class="col-md-2" style="padding-top: 3px; padding-bottom: 3px;">
              <strong>{{ trans('admin/settings/general.license') }}:</strong>
            </div>
            <div class="col-md-4" style="padding-top: 3px; padding-bottom: 3px;">
              <a href="https://www.gnu.org/licenses/agpl-3.0.en.html" rel="noopener">AGPL3</a>
           </div>
          </div>
          <!-- / row -->

          <!-- row -->
          <div class="row">
            <div class="col-md-2" style="padding-top: 3px; padding-bottom: 3px;">
              <strong>{{ trans('admin/settings/general.php') }}:</strong>
            </div>
            <div class="col-md-4" style="padding-top: 3px; padding-bottom: 3px;">
              {{ phpversion() }}
            </div>

            <div class="col-md-2" style="padding-top: 3px; padding-bottom: 3px;">
              <strong>{{ trans('admin/settings/general.laravel') }}:</strong>
            </div>
            <div class="col-md-4" style="padding-top: 3px; padding-bottom: 3px;">
              {{ $snipeSettings->lar_ver() }}
            </div>
          </div>

          <!-- row -->
          <div class="row">
              <div class="col-md-2" style="padding-top: 3px; padding-bottom: 3px;">
                <strong>{{ trans('admin/settings/general.timezone') }}:</strong>
              </div>
              <div class="col-md-4" style="padding-top: 3px; padding-bottom: 3px;">
                {{ config('app.timezone') }}
              </div>

              <div class="col-md-2" style="padding-top: 3px; padding-bottom: 3px;">
                <strong>{{ trans('admin/settings/general.database_driver') }}:</strong>
              </div>
              <div class="col-md-4" style="padding-top: 3px; padding-bottom: 3px;">
                {{ config('database.default') }}
              </div>
          </div>

          <!-- row -->
          <div class="row">
            <div class="col-md-2" style="padding-top: 3px; padding-bottom: 3px;">
              <strong>{{ trans('admin/settings/general.mail_from') }}:</strong>
            </div>
            <div class="col-md-4" style="padding-top: 3px; padding-bottom: 3px;">
              {{ config('mail.from.name') }}
              <code>&lt;{{ config('mail.from.address') }}&gt;</code>
            </div>

            <div class="col-md-2" style="padding-top: 3px; padding-bottom: 3px;">
              <strong>{{ trans('admin/settings/general.mail_reply_to') }}:</strong>
            </div>
            <div class="col-md-4" style="padding-top: 3px; padding-bottom: 3px;">
              {{ config('mail.reply_to.name') }}
              <code>&lt;{{ config('mail.reply_to.address') }}&gt;</code>
            </div>
          </div>

          <!-- row -->
          <div class="row">
            <div class="col-md-2" style="padding-top: 3px; padding-bottom: 3px;">
              <strong>{{ trans('admin/settings/general.bs_table_storage') }}:</strong>
            </div>
            <div class="col-md-4" style="padding-top: 3px; padding-bottom: 3px;">
              {{ config('session.bs_table_storage') }}
            </div>
            <div class="col-md-2">
            </div>
            <div class="col-md-4">
            </div>
          </div>
        </div>
        </div>
          </div>
          <!--/ row -->
        </div>
      </div> <!-- /box-body-->
    </div> <!--/box-default-->






  @section('moar_scripts')
<script nonce="{{ csrf_token() }}">



  var options = {
    valueNames: [ 'name', 'keywords', 'summary', 'index-block']
  };

  var settingList = new List('setting-list', options);

  $("#searchclear").click(function(){
    $("#searchinput").val('');
    settingList.search();
  });



</script>
  @endsection

@stop


