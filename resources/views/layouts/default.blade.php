<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
dir="{{ Helper::determineLanguageDirection() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>
        @section('title')
        @show
        :: {{ $snipeSettings->site_name }}
    </title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1" name="viewport">

    <meta name="apple-mobile-web-app-capable" content="yes">


    <link rel="apple-touch-icon"
          href="{{ ($snipeSettings) && ($snipeSettings->favicon!='') ?  Storage::disk('public')->url(e($snipeSettings->logo)) :  config('app.url').'/img/snipe-logo-bug.png' }}">
    <link rel="apple-touch-startup-image"
          href="{{ ($snipeSettings) && ($snipeSettings->favicon!='') ?  Storage::disk('public')->url(e($snipeSettings->logo)) :  config('app.url').'/img/snipe-logo-bug.png' }}">
    <link rel="shortcut icon" type="image/ico"
          href="{{ ($snipeSettings) && ($snipeSettings->favicon!='') ?  Storage::disk('public')->url(e($snipeSettings->favicon)) : config('app.url').'/favicon.ico' }} ">


    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="language" content="{{ Helper::mapBackToLegacyLocale(app()->getLocale()) }}">
    <meta name="language-direction" content="{{ Helper::determineLanguageDirection() }}">
    <meta name="baseUrl" content="{{ config('app.url') }}/">

    <script nonce="{{ csrf_token() }}">
        window.Laravel = {csrfToken: '{{ csrf_token() }}'};
    </script>

    {{-- stylesheets --}}
    <link rel="stylesheet" href="{{ url(mix('css/dist/all.css')) }}">
    @if (($snipeSettings) && ($snipeSettings->allow_user_skin==1) && Auth::check() && Auth::user()->present()->skin != '')
        <link rel="stylesheet" href="{{ url(mix('css/dist/skins/skin-'.Auth::user()->present()->skin.'.min.css')) }}">
    @else
        <link rel="stylesheet"
              href="{{ url(mix('css/dist/skins/skin-'.($snipeSettings->skin!='' ? $snipeSettings->skin : 'blue').'.css')) }}">
    @endif
    {{-- page level css --}}
    @stack('css')



    @if (($snipeSettings) && ($snipeSettings->header_color!=''))
        <style nonce="{{ csrf_token() }}">
            .main-header .navbar, .main-header .logo {
                background-color: {{ $snipeSettings->header_color }};
                background: -webkit-linear-gradient(top,  {{ $snipeSettings->header_color }} 0%,{{ $snipeSettings->header_color }} 100%);
                background: linear-gradient(to bottom, {{ $snipeSettings->header_color }} 0%,{{ $snipeSettings->header_color }} 100%);
                border-color: {{ $snipeSettings->header_color }};
            }

            .skin-{{ $snipeSettings->skin!='' ? $snipeSettings->skin : 'blue' }} .sidebar-menu > li:hover > a, .skin-{{ $snipeSettings->skin!='' ? $snipeSettings->skin : 'blue' }} .sidebar-menu > li.active > a {
                border-left-color: {{ $snipeSettings->header_color }};
            }

            .btn-primary {
                background-color: {{ $snipeSettings->header_color }};
                border-color: {{ $snipeSettings->header_color }};
            }
        </style>
    @endif

    {{-- Custom CSS --}}
    @if (($snipeSettings) && ($snipeSettings->custom_css))
        <style>
            {!! $snipeSettings->show_custom_css() !!}
        </style>
    @endif


    <script nonce="{{ csrf_token() }}">
        window.snipeit = {
            settings: {
                "per_page": {{ $snipeSettings->per_page }}
            }
        };
    </script>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <script src="{{ url(asset('js/html5shiv.js')) }}" nonce="{{ csrf_token() }}"></script>
    <script src="{{ url(asset('js/respond.js')) }}" nonce="{{ csrf_token() }}"></script>



</head>

@if (($snipeSettings) && ($snipeSettings->allow_user_skin==1) && Auth::check() && Auth::user()->present()->skin != '')
    <body class="sidebar-mini skin-{{ $snipeSettings->skin!='' ? Auth::user()->present()->skin : 'blue' }} {{ (session('menu_state')!='open') ? 'sidebar-mini sidebar-collapse' : ''  }}">
    @else
        <body class="sidebar-mini skin-{{ $snipeSettings->skin!='' ? $snipeSettings->skin : 'blue' }} {{ (session('menu_state')!='open') ? 'sidebar-mini sidebar-collapse' : ''  }}">
        @endif


        <a class="skip-main" href="#main">{{ trans('general.skip_to_main_content') }}</a>
        <div class="wrapper">

            <header class="main-header">

                <!-- Logo -->


                <!-- Header Navbar: style can be found in header.less -->
                <nav class="navbar navbar-static-top" role="navigation">
                    <!-- Sidebar toggle button above the compact sidenav -->
                    <a href="#" style="color: white" class="sidebar-toggle btn btn-white" data-toggle="push-menu"
                       role="button">
                        <span class="sr-only">{{ trans('general.toggle_navigation') }}</span>
                    </a>
                    <div class="nav navbar-nav navbar-left">
                        <div class="left-navblock">
                            @if ($snipeSettings->brand == '3')
                                <a class="logo navbar-brand no-hover" href="{{ config('app.url') }}">
                                    @if ($snipeSettings->logo!='')
                                        <img class="navbar-brand-img"
                                             src="{{ Storage::disk('public')->url($snipeSettings->logo) }}"
                                             alt="{{ $snipeSettings->site_name }} logo">
                                    @endif
                                    {{ $snipeSettings->site_name }}
                                </a>
                            @elseif ($snipeSettings->brand == '2')
                                <a class="logo navbar-brand no-hover" href="{{ config('app.url') }}">
                                    @if ($snipeSettings->logo!='')
                                        <img class="navbar-brand-img"
                                             src="{{ Storage::disk('public')->url($snipeSettings->logo) }}"
                                             alt="{{ $snipeSettings->site_name }} logo">
                                    @endif
                                    <span class="sr-only">{{ $snipeSettings->site_name }}</span>
                                </a>
                            @else
                                <a class="logo navbar-brand no-hover" href="{{ config('app.url') }}">
                                    {{ $snipeSettings->site_name }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Navbar Right Menu -->
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            @can('index', \App\Models\Asset::class)
                                <li aria-hidden="true"{!! (Request::is('hardware*') ? ' class="active"' : '') !!}>
                                    <a href="{{ url('hardware') }}" {{$snipeSettings->shortcuts_enabled == 1 ? "accesskey=1" : ''}} tabindex="-1" data-tooltip="true" data-placement="bottom" data-title="{{ trans('general.assets') }}">
                                        <x-icon type="assets" class="fa-fw" />
                                        <span class="sr-only">{{ trans('general.assets') }}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view', \App\Models\License::class)
                                <li aria-hidden="true"{!! (Request::is('licenses*') ? ' class="active"' : '') !!}>
                                    <a href="{{ route('licenses.index') }}" {{$snipeSettings->shortcuts_enabled == 1 ? "accesskey=2" : ''}} tabindex="-1" data-tooltip="true" data-placement="bottom" data-title="{{ trans('general.licenses') }}">
                                        <x-icon type="licenses" class="fa-fw" />
                                        <span class="sr-only">{{ trans('general.licenses') }}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('index', \App\Models\Accessory::class)
                                <li aria-hidden="true"{!! (Request::is('accessories*') ? ' class="active"' : '') !!}>
                                    <a href="{{ route('accessories.index') }}" {{$snipeSettings->shortcuts_enabled == 1 ? "accesskey=3" : ''}} tabindex="-1" data-tooltip="true" data-placement="bottom" data-title="{{ trans('general.accessories') }}">
                                        <x-icon type="accessories" class="fa-fw" />
                                        <span class="sr-only">{{ trans('general.accessories') }}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('index', \App\Models\Consumable::class)
                                <li aria-hidden="true"{!! (Request::is('consumables*') ? ' class="active"' : '') !!}>
                                    <a href="{{ url('consumables') }}" {{$snipeSettings->shortcuts_enabled == 1 ? "accesskey=4" : ''}} tabindex="-1" data-tooltip="true" data-placement="bottom" data-title="{{ trans('general.consumables') }}">
                                        <x-icon type="consumables" class="fa-fw" />
                                        <span class="sr-only">{{ trans('general.consumables') }}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view', \App\Models\Component::class)
                                <li aria-hidden="true"{!! (Request::is('components*') ? ' class="active"' : '') !!}>
                                    <a href="{{ route('components.index') }}" {{$snipeSettings->shortcuts_enabled == 1 ? "accesskey=5" : ''}} tabindex="-1" data-tooltip="true" data-placement="bottom" data-title="{{ trans('general.components') }}">
                                        <x-icon type="components" class="fa-fw" />
                                        <span class="sr-only">{{ trans('general.components') }}</span>
                                    </a>
                                </li>
                            @endcan
                            @can('view', \App\Models\Location::class)
                                <li aria-hidden="true"{!! (Request::is('locations*') ? ' class="active"' : '') !!}>
                                    <a href="{{ route('locations.index') }}" {{$snipeSettings->shortcuts_enabled == 1 ? "accesskey=6" : ''}} tabindex="-1" data-tooltip="true" data-placement="bottom" data-title="{{ trans('general.locations') }}">
                                        <i class="fa-solid fa-location-dot"></i>
                                        <span class="sr-only">{{ trans('general.locations') }}</span>
                                    </a>
                                </li>
                            @endcan

                            @can('index', \App\Models\Asset::class)
                                <li>
                                    <form class="navbar-form navbar-left form-horizontal" role="search"
                                          action="{{ route('findbytag/hardware') }}" method="get">
                                        <div class="col-xs-12 col-md-12">
                                            <div class="col-xs-12 form-group">
                                                <label class="sr-only" for="tagSearch">
                                                    {{ trans('general.lookup_by_tag') }}
                                                </label>
                                                <input type="text" class="form-control" id="tagSearch" name="assetTag" placeholder="{{ trans('general.lookup_by_tag') }}">
                                                <input type="hidden" name="topsearch" value="true" id="search">
                                            </div>
                                            <div class="col-xs-1">
                                                <button type="submit" id="topSearchButton" class="btn btn-primary pull-right">
                                                    <x-icon type="search" />
                                                    <span class="sr-only">{{ trans('general.search') }}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </li>
                            @endcan

                            @can('admin')
                                <li class="dropdown" aria-hidden="true">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" tabindex="-1">
                                        {{ trans('general.create') }}
                                        <strong class="caret"></strong>
                                    </a>
                                    <ul class="dropdown-menu">
                                        @can('create', \App\Models\Asset::class)
                                            <li{!! (Request::is('hardware/create') ? ' class="active"' : '') !!}>
                                                <a href="{{ route('hardware.create') }}" tabindex="-1">
                                                    <x-icon type="assets" />
                                                    {{ trans('general.asset') }}
                                                </a>
                                            </li>
                                        @endcan
                                        @can('create', \App\Models\License::class)
                                            <li{!! (Request::is('licenses/create') ? ' class="active"' : '') !!}>
                                                <a href="{{ route('licenses.create') }}" tabindex="-1">
                                                    <x-icon type="licenses" />
                                                    {{ trans('general.license') }}
                                                </a>
                                            </li>
                                        @endcan
                                        @can('create', \App\Models\Accessory::class)
                                            <li {!! (Request::is('accessories/create') ? 'class="active"' : '') !!}>
                                                <a href="{{ route('accessories.create') }}" tabindex="-1">
                                                    <x-icon type="accessories" />
                                                    {{ trans('general.accessory') }}
                                                </a>
                                            </li>
                                        @endcan
                                        @can('create', \App\Models\Consumable::class)
                                            <li {!! (Request::is('consunmables/create') ? 'class="active"' : '') !!}>
                                                <a href="{{ route('consumables.create') }}" tabindex="-1">
                                                    <x-icon type="consumables" />
                                                    {{ trans('general.consumable') }}
                                                </a>
                                            </li>
                                        @endcan
                                        @can('create', \App\Models\Component::class)
                                            <li {!! (Request::is('components/create') ? 'class="active"' : '') !!}>
                                                <a href="{{ route('components.create') }}" tabindex="-1">
                                                    <x-icon type="components" />
                                                    {{ trans('general.component') }}
                                                </a>
                                            </li>
                                        @endcan
                                        @can('create', \App\Models\User::class)
                                            <li {!! (Request::is('users/create') ? 'class="active"' : '') !!}>
                                                <a href="{{ route('users.create') }}" tabindex="-1">
                                                    <x-icon type="users" />
                                                    {{ trans('general.user') }}
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </li>
                            @endcan
                            @can('admin')
                                @if ($snipeSettings->show_alerts_in_menu=='1')
                                    <!-- Tasks: style can be found in dropdown.less -->
                                    <?php $alert_items = Helper::checkLowInventory(); $deprecations = Helper::deprecationCheck()?>

                                    <li class="dropdown tasks-menu">
                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                            <x-icon type="alerts" />
                                            <span class="sr-only">{{ trans('general.alerts') }}</span>
                                            @if (count($alert_items) || count($deprecations))
                                                <span class="label label-danger">{{ count($alert_items) + count($deprecations) }}</span>
                                            @endif
                                        </a>
                                        <ul class="dropdown-menu">
                                            @if($deprecations)
                                                @foreach ($deprecations as $key => $deprecation)
                                                    @if ($deprecation['check'])
                                                        <li class="header alert-warning">{!! $deprecation['message'] !!}</li>
                                                    @endif
                                                @endforeach
                                            @endif
                                            <li class="header">{{ trans_choice('general.quantity_minimum', count($alert_items)) }}</li>
                                            <li>
                                                <!-- inner menu: contains the actual data -->
                                                <ul class="menu">

                                                    @for($i = 0; count($alert_items) > $i; $i++)

                                                        <li><!-- Task item -->
                                                            <a href="{{ route($alert_items[$i]['type'].'.show', $alert_items[$i]['id'])}}">
                                                                <h2 class="task_menu">{{ $alert_items[$i]['name'] }}
                                                                    <small class="pull-right">
                                                                        {{ $alert_items[$i]['remaining'] }} {{ trans('general.remaining') }}
                                                                    </small>
                                                                </h2>
                                                                <div class="progress xs">
                                                                    <div class="progress-bar progress-bar-yellow"
                                                                         style="width: {{ $alert_items[$i]['percent'] }}%"
                                                                         role="progressbar"
                                                                         aria-valuenow="{{ $alert_items[$i]['percent'] }}"
                                                                         aria-valuemin="0" aria-valuemax="100">
                                                                        <span class="sr-only">{{ $alert_items[$i]['percent'] }}% Complete</span>
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </li>
                                                        <!-- end task item -->
                                                    @endfor
                                                </ul>
                                            </li>
                                            {{-- <li class="footer">
                                              <a href="#">{{ trans('general.tasks_view_all') }}</a>
                                            </li> --}}
                                        </ul>
                                    </li>
                                @endcan
                            @endif



                            <!-- User Account: style can be found in dropdown.less -->
                            @if (Auth::check())
                                <li class="dropdown user user-menu">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                        @if (Auth::user()->present()->gravatar())
                                            <img src="{{ Auth::user()->present()->gravatar() }}" class="user-image"
                                                 alt="">
                                        @else
                                            <x-icon type="user" />
                                        @endif

                                        <span class="hidden-xs">
                                            {{ Auth::user()->getFullNameAttribute() }}
                                            <strong class="caret"></strong>
                                        </span>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <!-- User image -->
                                        <li {!! (Request::is('account/profile') ? ' class="active"' : '') !!}>
                                            <a href="{{ route('view-assets') }}">
                                                <x-icon type="checkmark" class="fa-fw" />
                                                {{ trans('general.viewassets') }}
                                            </a></li>

                                        @can('viewRequestable', \App\Models\Asset::class)
                                            <li {!! (Request::is('account/requested') ? ' class="active"' : '') !!}>
                                                <a href="{{ route('account.requested') }}">
                                                    <x-icon type="checkmark" class="fa-fw" />
                                                    {{ trans('general.requested_assets_menu') }}
                                                </a></li>
                                        @endcan

                                        <li {!! (Request::is('account/accept') ? ' class="active"' : '') !!}>
                                            <a href="{{ route('account.accept') }}">
                                                <x-icon type="checkmark" class="fa-fw" />
                                                {{ trans('general.accept_assets_menu') }}
                                            </a></li>


                                        @can('self.profile')
                                        <li>
                                            <a href="{{ route('profile') }}">
                                                <x-icon type="user" class="fa-fw" />
                                                {{ trans('general.editprofile') }}
                                            </a>
                                        </li>
                                        @endcan

                                        @if (Auth::user()->ldap_import!='1')
                                        <li>
                                            <a href="{{ route('account.password.index') }}">
                                                <x-icon type="password" class="fa-fw" />
                                                {{ trans('general.changepassword') }}
                                            </a>
                                        </li>
                                        @endif


                                        @can('self.api')
                                            <li>
                                                <a href="{{ route('user.api') }}">
                                                    <x-icon type="api-key" class="fa-fw" />
                                                     {{ trans('general.manage_api_keys') }}
                                                </a>
                                            </li>
                                        @endcan
                                        <li class="divider" style="margin-top: -1px; margin-bottom: -1px"></li>
                                        <li>

                                            <a href="{{ route('logout.get') }}"
                                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                                <x-icon type="logout" class="fa-fw" />
                                                 {{ trans('general.logout') }}
                                            </a>

                                            <form id="logout-form" action="{{ route('logout.post') }}" method="POST"
                                                  style="display: none;">
                                                {{ csrf_field() }}
                                            </form>

                                        </li>
                                    </ul>
                                </li>
                            @endif


                            @can('superadmin')
                                <li>
                                    <a href="{{ route('settings.index') }}">
                                        <x-icon type="admin-settings" />
                                        <span class="sr-only">{{ trans('general.admin') }}</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </nav>
                <a href="#" style="float:left" class="sidebar-toggle-mobile visible-xs btn" data-toggle="push-menu"
                   role="button">
                    <span class="sr-only">{{ trans('general.toggle_navigation') }}</span>
                    <x-icon type="nav-toggle" />
                </a>
                <!-- Sidebar toggle button-->
            </header>

            <!-- Left side column. contains the logo and sidebar -->
            <aside class="main-sidebar">
                <!-- sidebar: style can be found in sidebar.less -->
                <section class="sidebar">
                    <!-- sidebar menu: : style can be found in sidebar.less -->
                    <ul class="sidebar-menu" data-widget="tree" {{ \App\Helpers\Helper::determineLanguageDirection() == 'rtl' ? 'style="margin-right:12px' : '' }}>
                        @can('admin')
                            <li {!! (\Request::route()->getName()=='home' ? ' class="active"' : '') !!} class="firstnav">
                                <a href="{{ route('home') }}">
                                    <x-icon type="dashboard" class="fa-fw" />
                                    <span>{{ trans('general.dashboard') }}</span>
                                </a>
                            </li>
                        @endcan
                        @can('index', \App\Models\Asset::class)
                            <li class="treeview{{ ((Request::is('statuslabels/*') || Request::is('hardware*')) ? ' active' : '') }}">
                                <a href="#">
                                    <x-icon type="assets" class="fa-fw" />
                                    <span>{{ trans('general.assets') }}</span>
                                    <x-icon type="angle-left" class="pull-right fa-fw"/>
                                </a>
                                <ul class="treeview-menu">
                                    <li>
                                        <a href="{{ url('hardware') }}">
                                            <x-icon type="circle" class="text-grey fa-fw"/>
                                            {{ trans('general.list_all') }}
                                            <span class="badge">
                                                {{ (isset($total_assets)) ? $total_assets : '' }}
                                            </span>
                                        </a>
                                    </li>

                                    <?php $status_navs = \App\Models\Statuslabel::where('show_in_nav', '=', 1)->withCount('assets as asset_count')->get(); ?>
                                    @if (count($status_navs) > 0)
                                        @foreach ($status_navs as $status_nav)
                                            <li{!! (Request::is('statuslabels/'.$status_nav->id) ? ' class="active"' : '') !!}>
                                                <a href="{{ route('statuslabels.show', ['statuslabel' => $status_nav->id]) }}">
                                                    <i class="fas fa-circle text-grey fa-fw"
                                                       aria-hidden="true"{!!  ($status_nav->color!='' ? ' style="color: '.e($status_nav->color).'"' : '') !!}></i>
                                                    {{ $status_nav->name }}
                                                    <span class="badge badge-secondary">{{ $status_nav->asset_count }}</span></a></li>
                                        @endforeach
                                    @endif


                                    <li{!! (Request::query('status') == 'Deployed' ? ' class="active"' : '') !!}>
                                        <a href="{{ url('hardware?status=Deployed') }}">
                                            <x-icon type="circle" class="text-blue fa-fw" />
                                            {{ trans('general.deployed') }}
                                            <span class="badge">{{ (isset($total_deployed_sidebar)) ? $total_deployed_sidebar : '' }}</span>
                                        </a>
                                    </li>
                                    <li{!! (Request::query('status') == 'RTD' ? ' class="active"' : '') !!}>
                                        <a href="{{ url('hardware?status=RTD') }}">
                                            <x-icon type="circle" class="text-green fa-fw" />
                                            {{ trans('general.ready_to_deploy') }}
                                            <span class="badge">{{ (isset($total_rtd_sidebar)) ? $total_rtd_sidebar : '' }}</span>
                                        </a>
                                    </li>
                                    <li{!! (Request::query('status') == 'Pending' ? ' class="active"' : '') !!}><a href="{{ url('hardware?status=Pending') }}">
                                            <x-icon type="circle" class="text-orange fa-fw" />
                                            {{ trans('general.pending') }}
                                            <span class="badge">{{ (isset($total_pending_sidebar)) ? $total_pending_sidebar : '' }}</span>
                                        </a>
                                    </li>
                                    <li{!! (Request::query('status') == 'Undeployable' ? ' class="active"' : '') !!} ><a
                                                href="{{ url('hardware?status=Undeployable') }}">
                                            <x-icon type="x" class="text-red fa-fw" />
                                            {{ trans('general.undeployable') }}
                                            <span class="badge">{{ (isset($total_undeployable_sidebar)) ? $total_undeployable_sidebar : '' }}</span>
                                        </a>
                                    </li>
                                    <li{!! (Request::query('status') == 'byod' ? ' class="active"' : '') !!}><a
                                                href="{{ url('hardware?status=byod') }}">
                                            <x-icon type="x" class="text-red fa-fw" />
                                            {{ trans('general.byod') }}
                                            <span class="badge">{{ (isset($total_byod_sidebar)) ? $total_byod_sidebar : '' }}</span>
                                        </a>
                                    </li>
                                    <li{!! (Request::query('status') == 'Archived' ? ' class="active"' : '') !!}><a
                                                href="{{ url('hardware?status=Archived') }}">
                                            <x-icon type="x" class="text-red fa-fw" />
                                            {{ trans('admin/hardware/general.archived') }}
                                            <span class="badge">{{ (isset($total_archived_sidebar)) ? $total_archived_sidebar : '' }}</span>
                                        </a>
                                    </li>
                                    <li{!! (Request::query('status') == 'Requestable' ? ' class="active"' : '') !!}><a
                                                href="{{ url('hardware?status=Requestable') }}">
                                            <x-icon type="checkmark" class="text-blue fa-fw" />
                                            {{ trans('admin/hardware/general.requestable') }}
                                        </a>
                                    </li>

                                    @can('audit', \App\Models\Asset::class)
                                        <li{!! (Request::is('hardware/audit/due') ? ' class="active"' : '') !!}>
                                            <a href="{{ route('assets.audit.due') }}">
                                                <x-icon type="due" class="text-yellow fa-fw"/>
                                                {{ trans('general.audit_due') }}
                                                <span class="badge">{{ (isset($total_due_and_overdue_for_audit)) ? $total_due_and_overdue_for_audit : '' }}</span>
                                            </a>
                                        </li>
                                    @endcan

                                    @can('checkin', \App\Models\Asset::class)
                                    <li{!! (Request::is('hardware/checkins/due') ? ' class="active"' : '') !!}>
                                        <a href="{{ route('assets.checkins.due') }}">
                                            <x-icon type="due" class="text-orange fa-fw"/>
                                            {{ trans('general.checkin_due') }}
                                            <span class="badge">{{ (isset($total_due_and_overdue_for_checkin)) ? $total_due_and_overdue_for_checkin : '' }}</span>
                                        </a>
                                    </li>
                                    @endcan

                                    <li class="divider">&nbsp;</li>
                                    @can('checkin', \App\Models\Asset::class)
                                        <li{!! (Request::is('hardware/quickscancheckin') ? ' class="active"' : '') !!}>
                                            <a href="{{ route('hardware/quickscancheckin') }}">
                                                {{ trans('general.quickscan_checkin') }}
                                            </a>
                                        </li>
                                    @endcan

                                    @can('checkout', \App\Models\Asset::class)
                                        <li{!! (Request::is('hardware/bulkcheckout') ? ' class="active"' : '') !!}>
                                            <a href="{{ route('hardware.bulkcheckout.show') }}">
                                                {{ trans('general.bulk_checkout') }}
                                            </a>
                                        </li>
                                        <li{!! (Request::is('hardware/requested') ? ' class="active"' : '') !!}>
                                            <a href="{{ route('assets.requested') }}">
                                                {{ trans('general.requested') }}</a>
                                        </li>
                                    @endcan

                                    @can('create', \App\Models\Asset::class)
                                        <li{!! (Request::query('Deleted') ? ' class="active"' : '') !!}>
                                            <a href="{{ url('hardware?status=Deleted') }}">
                                                {{ trans('general.deleted') }}
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('maintenances.index') }}">
                                                {{ trans('general.asset_maintenances') }}
                                            </a>
                                        </li>
                                    @endcan
                                    @can('admin')
                                        <li>
                                            <a href="{{ url('hardware/history') }}">
                                                {{ trans('general.import-history') }}
                                            </a>
                                        </li>
                                    @endcan
                                    @can('audit', \App\Models\Asset::class)
                                        <li>
                                            <a href="{{ route('assets.bulkaudit') }}">
                                                {{ trans('general.bulkaudit') }}
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </li>
                        @endcan
                        <!-- Locations -->
                        @can('view', \App\Models\Location::class)
                            <li id="menu-locations" class="treeview {!! (Request::is('locations*') ? ' active' : '') !!}">
                                <a href="#">
                                    <x-icon type="map" class="fa-fw fa-solid fa-location-dot" /> <!-- Ikonica mape -->
                                    <span>{{ trans('general.locations') }}</span>
                                    <x-icon type="angle-left" class="pull-right fa-fw"/> <!-- Strelica -->
                                </a>

                                <ul class="treeview-menu">
                                    <li {!! (Request::routeIs('locations.index') ? ' class="active"' : '') !!}>
                                        <a href="{{ route('locations.index') }}">
                                            <x-icon type="map-marker" class="fa-fw" />
                                            {{ trans('general.locations') }}
                                        </a>
                                    </li>
                                    <li {!! (Request::routeIs('locations.parentIndex') ? ' class="active"' : '') !!}>
                                        <a href="{{ route('locations.parentIndex') }}">
                                            <x-icon type="times-circle" class="fa-fw" />
                                            {{ trans('general.parent') }}
                                        </a>
                                    </li>
                                    <li {!! (Request::routeIs('locations.closedIndex') ? ' class="active"' : '') !!}>
                                        <a href="{{ route('locations.closedIndex') }}">
                                            <x-icon type="times-circle" class="fa-fw" />
                                            {{ trans('general.closed_locations') }}
                                        </a>
                                    </li>
                                    <li {!! (Request::routeIs('locations.storagedIndex') ? ' class="active"' : '') !!}>
                                        <a href="{{ route('locations.storageIndex') }}">
                                            <x-icon type="times-circle" class="fa-fw" />
                                            {{ trans('general.storage_unit') }}
                                        </a>
                                    </li>

                                    <li {!! (Request::routeIs('locations.officeIndex') ? ' class="active"' : '') !!}>
                                        <a href="{{ route('locations.officeIndex') }}">
                                            <x-icon type="times-circle" class="fa-fw" />
                                            {{ trans('general.office_location') }}
                                        </a>
                                    </li>

                                    @can('create', \App\Models\Location::class)
                                        <li {!! (Request::routeIs('locations.create') ? ' class="active"' : '') !!}>
                                            <a href="{{ route('locations.create') }}">
                                                <x-icon type="plus-circle" class="fa-fw" />
                                                {{ trans('general.create') }}
                                            </a>
                                        </li>
                                    @endcan
                                    @can('edit', \App\Models\Location::class)
                                        <li {!! (Request::is('locations.deletedIndex') ? ' class="active"' : '') !!}>
                                        <a href="{{ route('locations.deletedIndex') }}">
                                            {{ trans('general.deleted') }}
                                        </a>
                                    </li>
                                    @endcan
                                </ul>
                            </li>
                        @endcan

                        @can('view', \App\Models\License::class)
                            <li{!! (Request::is('licenses*') ? ' class="active"' : '') !!}>
                                <a href="{{ route('licenses.index') }}">
                                    <x-icon type="licenses" class="fa-fw"/>
                                    <span>{{ trans('general.licenses') }}</span>
                                </a>
                            </li>
                        @endcan
                        @can('index', \App\Models\Accessory::class)
                            <li{!! (Request::is('accessories*') ? ' class="active"' : '') !!}>
                                <a href="{{ route('accessories.index') }}">
                                    <x-icon type="accessories" class="fa-fw" />
                                    <span>{{ trans('general.accessories') }}</span>
                                </a>
                            </li>
                        @endcan
                        @can('view', \App\Models\Consumable::class)
                            <li{!! (Request::is('consumables*') ? ' class="active"' : '') !!}>
                                <a href="{{ url('consumables') }}">
                                    <x-icon type="consumables" class="fa-fw" />
                                    <span>{{ trans('general.consumables') }}</span>
                                </a>
                            </li>
                        @endcan
                        @can('view', \App\Models\Component::class)
                            <li{!! (Request::is('components*') ? ' class="active"' : '') !!}>
                                <a href="{{ route('components.index') }}">
                                    <x-icon type="components" class="fa-fw" />
                                    <span>{{ trans('general.components') }}</span>
                                </a>
                            </li>
                        @endcan
                        @can('view', \App\Models\PredefinedKit::class)
                            <li{!! (Request::is('kits') ? ' class="active"' : '') !!}>
                                <a href="{{ route('kits.index') }}">
                                    <x-icon type="kits" class="fa-fw" />
                                    <span>{{ trans('general.kits') }}</span>
                                </a>
                            </li>
                        @endcan

                        @can('view', \App\Models\User::class)
                            <li{!! (Request::is('users*') ? ' class="active"' : '') !!}>
                                <a href="{{ route('users.index') }}" {{$snipeSettings->shortcuts_enabled == 1 ? "accesskey=6" : ''}}>
                                    <x-icon type="users" class="fa-fw" />
                                    <span>{{ trans('general.people') }}</span>
                                </a>
                            </li>
                        @endcan
                        @can('import')
                            <li{!! (Request::is('import/*') ? ' class="active"' : '') !!}>
                                <a href="{{ route('imports.index') }}">
                                    <x-icon type="import" class="fa-fw" />
                                    <span>{{ trans('general.import') }}</span>
                                </a>
                            </li>
                        @endcan

                        @can('backend.interact')
                            <li class="treeview {!! in_array(Request::route()->getName(),App\Helpers\Helper::SettingUrls()) ? ' active': '' !!}">
                                <a href="#" id="settings">
                                    <x-icon type="settings" class="fa-fw" />
                                    <span>{{ trans('general.settings') }}</span>
                                    <x-icon type="angle-left" class="pull-right fa-fw"/>
                                </a>

                                <ul class="treeview-menu">
                                    @if(Gate::allows('view', App\Models\CustomField::class) || Gate::allows('view', App\Models\CustomFieldset::class))
                                        <li {!! (Request::is('fields*') ? ' class="active"' : '') !!}>
                                            <a href="{{ route('fields.index') }}">
                                                {{ trans('admin/custom_fields/general.custom_fields') }}
                                            </a>
                                        </li>
                                    @endif

                                    @can('view', \App\Models\Statuslabel::class)
                                        <li {!! (Request::is('statuslabels*') ? ' class="active"' : '') !!}>
                                            <a href="{{ route('statuslabels.index') }}">
                                                {{ trans('general.status_labels') }}
                                            </a>
                                        </li>
                                    @endcan

                                    @can('view', \App\Models\AssetModel::class)
                                        <li>
                                            <a href="{{ route('models.index') }}" {{ (Request::is('/assetmodels') ? ' class="active"' : '') }}>
                                                {{ trans('general.asset_models') }}
                                            </a>
                                        </li>
                                    @endcan

                                    @can('view', \App\Models\Category::class)
                                        <li>
                                            <a href="{{ route('categories.index') }}" {{ (Request::is('/categories') ? ' class="active"' : '') }}>
                                                {{ trans('general.categories') }}
                                            </a>
                                        </li>
                                    @endcan

                                    @can('view', \App\Models\Manufacturer::class)
                                        <li>
                                            <a href="{{ route('manufacturers.index') }}" {{ (Request::is('/manufacturers') ? ' class="active"' : '') }}>
                                                {{ trans('general.manufacturers') }}
                                            </a>
                                        </li>
                                    @endcan

                                    @can('view', \App\Models\Supplier::class)
                                        <li>
                                            <a href="{{ route('suppliers.index') }}" {{ (Request::is('/suppliers') ? ' class="active"' : '') }}>
                                                {{ trans('general.suppliers') }}
                                            </a>
                                        </li>
                                    @endcan

                                    @can('view', \App\Models\Department::class)
                                        <li>
                                            <a href="{{ route('departments.index') }}" {{ (Request::is('/departments') ? ' class="active"' : '') }}>
                                                {{ trans('general.departments') }}
                                            </a>
                                        </li>
                                    @endcan

                                    @can('view', \App\Models\Company::class)
                                        <li>
                                            <a href="{{ route('companies.index') }}" {{ (Request::is('/companies') ? ' class="active"' : '') }}>
                                                {{ trans('general.companies') }}
                                            </a>
                                        </li>
                                    @endcan

                                    @can('view', \App\Models\Depreciation::class)
                                        <li>
                                            <a href="{{ route('depreciations.index') }}" {{ (Request::is('/depreciations') ? ' class="active"' : '') }}>
                                                {{ trans('general.depreciation') }}
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </li>
                        @endcan

                        @can('reports.view')
                            <li class="treeview{{ (Request::is('reports*') ? ' active' : '') }}">
                                <a href="#" class="dropdown-toggle">
                                    <x-icon type="reports" class="fa-fw" />
                                    <span>{{ trans('general.reports') }}</span>
                                    <x-icon type="angle-left" class="pull-right"/>
                                </a>

                                <ul class="treeview-menu">
                                    <li>
                                        <a href="{{ route('reports.activity') }}" {{ (Request::is('reports/activity') ? ' class="active"' : '') }}>
                                            {{ trans('general.activity_report') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ url('reports/custom') }}" {{ (Request::is('reports/custom') ? ' class="active"' : '') }}>
                                            {{ trans('general.custom_report') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('reports.audit') }}" {{ (Request::is('reports.audit') ? ' class="active"' : '') }}>
                                            {{ trans('general.audit_report') }}</a>
                                    </li>
                                    <li>
                                        <a href="{{ url('reports/depreciation') }}" {{ (Request::is('reports/depreciation') ? ' class="active"' : '') }}>
                                            {{ trans('general.depreciation_report') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ url('reports/licenses') }}" {{ (Request::is('reports/licenses') ? ' class="active"' : '') }}>
                                            {{ trans('general.license_report') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ url('reports/asset_maintenances') }}" {{ (Request::is('reports/asset_maintenances') ? ' class="active"' : '') }}>
                                            {{ trans('general.asset_maintenance_report') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ url('reports/unaccepted_assets') }}" {{ (Request::is('reports/unaccepted_assets') ? ' class="active"' : '') }}>
                                            {{ trans('general.unaccepted_asset_report') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ url('reports/accessories') }}" {{ (Request::is('reports/accessories') ? ' class="active"' : '') }}>
                                            {{ trans('general.accessory_report') }}
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endcan

                        @can('viewRequestable', \App\Models\Asset::class)
                            <li{!! (Request::is('account/requestable-assets') ? ' class="active"' : '') !!}>
                                <a href="{{ route('requestable-assets') }}">
                                    <x-icon type="requestable" class="fa-fw" />
                                    <span>{{ trans('general.requestable_items') }}</span>
                                </a>
                            </li>
                        @endcan


                    </ul>
                </section>
                <!-- /.sidebar -->
            </aside>

            <!-- Content Wrapper. Contains page content -->

            <div class="content-wrapper" role="main" id="setting-list">

                @if ($debug_in_production)
                    <div class="row" style="margin-bottom: 0px; background-color: red; color: white; font-size: 15px;">
                        <div class="col-md-12"
                             style="margin-bottom: 0px; background-color: #b50408 ; color: white; padding: 10px 20px 10px 30px; font-size: 16px;">
                            <x-icon type="warning" class="fa-3x pull-left"/>
                            <strong>{{ strtoupper(trans('general.debug_warning')) }}:</strong>
                            {!! trans('general.debug_warning_text') !!}
                        </div>
                    </div>
                @endif

                <!-- Content Header (Page header) -->
                <section class="content-header">


                    <div class="row">
                        <div class="col-md-12" style="margin-bottom: 0px;">

                        <style>
                            .breadcrumb-item {
                                display: inline;
                                list-style: none;
                            }
                        </style>

                            <h1 class="pull-left pagetitle" style="font-size: 22px; margin-top: 5px;">

                                @if (Breadcrumbs::has() && (Breadcrumbs::current()->count() > 1))
                                    <ul style="padding-left: 0;">

                                    @foreach (Breadcrumbs::current() as $crumbs)
                                        @if ($crumbs->url() && !$loop->last)
                                            <li class="breadcrumb-item">
                                                <a href="{{ $crumbs->url() }}">
                                                    @if ($loop->first)
                                                        {!! Blade::render($crumbs->title()) !!}
                                                    @else
                                                        {{ Blade::render($crumbs->title()) }}
                                                    @endif
                                                </a>
                                                <x-icon type="angle-right" />
                                            </li>
                                        @elseif (is_null($crumbs->url()) && !$loop->last)
                                            <li class="breadcrumb-item active">
                                                {{ $crumbs->title() }}
                                                <x-icon type="angle-right" />
                                            </li>
                                       @else
                                            <li class="breadcrumb-item active">
                                                {{ $crumbs->title() }}
                                            </li>
                                        @endif
                                    @endforeach

                                    </ul>
                                @else
                                    @yield('title')
                                @endif

                            </h1>

                                @if (isset($helpText))
                                    @include ('partials.more-info',
                                                           [
                                                               'helpText' => $helpText,
                                                               'helpPosition' => (isset($helpPosition)) ? $helpPosition : 'left'
                                                           ])
                                @endif
                                <div class="pull-right">
                                    @yield('header_right')
                                </div>

                        </div>
                    </div>
                </section>


                <section class="content" id="main" tabindex="-1" style="padding-top: 0px;">

                    <!-- Notifications -->
                    <div class="row">
                        @if (config('app.lock_passwords'))
                            <div class="col-md-12">
                                <div class="callout callout-info">
                                    {{ trans('general.some_features_disabled') }}
                                </div>
                            </div>
                        @endif

                        @include('notifications')
                    </div>


                    <!-- Content -->
                    <div id="{!! (Request::is('*api*') ? 'app' : 'webui') !!}">
                        @yield('content')
                    </div>

                </section>

            </div><!-- /.content-wrapper -->
            <footer class="main-footer hidden-print" style="display:grid;flex-direction:column;">

                <div class="1hidden-xs pull-left">
                    <div class="pull-left" >
                        <a target="_blank" href="https://snipeitapp.com" rel="noopener">Snipe-IT</a> is open source software, made with <x-icon type="heart" style="color: #a94442; font-size: 10px" />
                            <span class="sr-only">love</span> by <a href="https://bsky.app/profile/snipeitapp.com" rel="noopener">@snipeitapp</a>.
                    </div>
                    <div class="pull-right">
                    @if ($snipeSettings->version_footer!='off')
                        @if (($snipeSettings->version_footer=='on') || (($snipeSettings->version_footer=='admin') && (Auth::user()->isSuperUser()=='1')))
                            &nbsp; <strong>Version</strong> {{ config('version.app_version') }} -
                            build {{ config('version.build_version') }} ({{ config('version.branch') }})
                        @endif
                    @endif

                    @if ($snipeSettings->support_footer!='off')
                        @if (($snipeSettings->support_footer=='on') || (($snipeSettings->support_footer=='admin') && (Auth::user()->isSuperUser()=='1')))
                            <a target="_blank" class="btn btn-default btn-xs"
                               href="https://snipe-it.readme.io/docs/overview"
                               rel="noopener">{{ trans('general.user_manual') }}</a>
                            <a target="_blank" class="btn btn-default btn-xs" href="https://snipeitapp.com/support/"
                               rel="noopener">{{ trans('general.bug_report') }}</a>
                        @endif
                    @endif

                    @if ($snipeSettings->privacy_policy_link!='')
                        <a target="_blank" class="btn btn-default btn-xs" rel="noopener"
                           href="{{  $snipeSettings->privacy_policy_link }}"
                           target="_new">{{ trans('admin/settings/general.privacy_policy') }}</a>
                    @endif
                    </div>
                    <br>
                    @if ($snipeSettings->footer_text!='')
                        <div class="pull-left">
                            {!!  Helper::parseEscapedMarkedown($snipeSettings->footer_text)  !!}
                        </div>
                    @endif
                </div>
            </footer>
        </div><!-- ./wrapper -->


        <!-- end main container -->

        <div class="modal modal-danger fade" id="dataConfirmModal" tabindex="-1" role="dialog"
             aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h2 class="modal-title" id="myModalLabel">&nbsp;</h2>
                    </div>
                    <div class="modal-body"></div>
                    <div class="modal-footer">
                        <form method="post" id="deleteForm" role="form">
                            {{ csrf_field() }}
                            {{ method_field('DELETE') }}

                            <button type="button" class="btn btn-default pull-left"
                                    data-dismiss="modal">{{ trans('general.cancel') }}</button>
                            <button type="submit" class="btn btn-outline"
                                    id="dataConfirmOK">{{ trans('general.yes') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        <div class="modal modal-warning fade" id="restoreConfirmModal" tabindex="-1" role="dialog"
             aria-labelledby="confirmModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title" id="confirmModalLabel">&nbsp;</h4>
                    </div>
                    <div class="modal-body"></div>
                    <div class="modal-footer">
                        <form method="post" id="restoreForm" role="form">
                            {{ csrf_field() }}
                            {{ method_field('POST') }}

                            <button type="button" class="btn btn-default pull-left"
                                    data-dismiss="modal">{{ trans('general.cancel') }}</button>
                            <button type="submit" class="btn btn-outline"
                                    id="dataConfirmOK">{{ trans('general.yes') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>



        {{-- Javascript files --}}
        <script src="{{ url(mix('js/dist/all.js')) }}" nonce="{{ csrf_token() }}"></script>
        <script src="{{ url('js/select2/i18n/'.Helper::mapBackToLegacyLocale(app()->getLocale()).'.js') }}"></script>

        <!-- v5-beta: This pGenerator call must remain here for v5 - until fixed - so that the JS password generator works for the user create modal. -->
        <script src="{{ url('js/pGenerator.jquery.js') }}"></script>

        {{-- Page level javascript --}}
        @stack('js')

        @section('moar_scripts')
        @show


        <script nonce="{{ csrf_token() }}">

            var clipboard = new ClipboardJS('.js-copy-link');

            clipboard.on('success', function(e) {
                // Get the clicked element
                var clickedElement = $(e.trigger);
                // Get the target element selector from data attribute
                var targetSelector = clickedElement.data('data-clipboard-target');
                // Show the alert that the content was copied
                clickedElement.tooltip('hide').attr('data-original-title', '{{ trans('general.copied') }}').tooltip('show');
            });

            // Reference: https://jqueryvalidation.org/validate/
            var validator = $('#create-form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'alert-msg',
                errorElement: 'span',
                errorPlacement: function(error, element) {
                    $(element).hasClass('select2') || $(element).hasClass('js-data-ajax')
                        // If the element is a select2 then append the error to the parent div
                        ? element.parent('div').append(error)
                        // Otherwise place it after
                        : error.insertAfter(element);
                },
                highlight: function(inputElement) {
                    $(inputElement).parent().addClass('has-error');
                    $(inputElement).closest('.help-block').remove();
                },
                onfocusout: function(element) {
                    return $(element).valid();
                },

            });

            $.extend($.validator.messages, {
                required: "{{ trans('validation.generic.required') }}",
                email: "{{ trans('validation.generic.email') }}"
            });


            function showHideEncValue(e) {
                // Use element id to find the text element to hide / show
                var targetElement = e.id+"-to-show";
                var hiddenElement = e.id+"-to-hide";
                var audio = new Audio('{{ config('app.url') }}/sounds/lock.mp3');
                if($(e).hasClass('fa-lock')) {
                    @if ((isset($user)) && ($user->enable_sounds))
                        audio.play()
                    @endif
                    $(e).removeClass('fa-lock').addClass('fa-unlock');
                    // Show the encrypted custom value and hide the element with asterisks
                    document.getElementById(targetElement).style.fontSize = "100%";
                    document.getElementById(hiddenElement).style.display = "none";

                } else {
                    @if ((isset($user)) && ($user->enable_sounds))
                        audio.play()
                    @endif
                    $(e).removeClass('fa-unlock').addClass('fa-lock');
                    // ClipboardJS can't copy display:none elements so use a trick to hide the value
                    document.getElementById(targetElement).style.fontSize = "0px";
                    document.getElementById(hiddenElement).style.display = "";

                 }
             }

            $(function () {

                // Invoke Bootstrap 3's tooltip
                $('[data-tooltip="true"]').tooltip({
                    container: 'body',
                    animation: true,
                });

                $('[data-toggle="popover"]').popover();
                $('.select2 span').addClass('needsclick');
                $('.select2 span').removeAttr('title');

                // This javascript handles saving the state of the menu (expanded or not)
                $('body').bind('expanded.pushMenu', function () {
                    $.ajax({
                        type: 'GET',
                        url: "{{ route('account.menuprefs', ['state'=>'open']) }}",
                        _token: "{{ csrf_token() }}"
                    });

                });

                $('body').bind('collapsed.pushMenu', function () {
                    $.ajax({
                        type: 'GET',
                        url: "{{ route('account.menuprefs', ['state'=>'close']) }}",
                        _token: "{{ csrf_token() }}"
                    });
                });

            });

            // Initiate the ekko lightbox
            $(document).on('click', '[data-toggle="lightbox"]', function (event) {
                event.preventDefault();
                $(this).ekkoLightbox();
            });
            //This prevents multi-click checkouts for accessories, components, consumables
            $(document).ready(function () {
                $('#checkout_form').submit(function (event) {
                    event.preventDefault();
                    $('#submit_button').prop('disabled', true);
                    this.submit();
                });
            });

            // Select encrypted custom fields to hide them in the asset list
            $(document).ready(function() {
                // Selector for elements with css-padlock class
                var selector = 'td.css-padlock';

                // Function to add original value to elements
                function addValue($element) {
                    // Get original value of the element
                    var originalValue = $element.text().trim();

                    // Show asterisks only for not empty values
                    if (originalValue !== '') {
                        // This is necessary to avoid loop because value is generated dynamically
                        if (originalValue !== '' && originalValue !== asterisks) $element.attr('value', originalValue);

                        // Hide the original value and show asterisks of the same length
                        var asterisks = '*'.repeat(originalValue.length);
                        $element.text(asterisks);

                        // Add click event to show original text
                        $element.click(function() {
                            var $this = $(this);
                            if ($this.text().trim() === asterisks) {
                                $this.text($this.attr('value'));
                            } else {
                                $this.text(asterisks);
                            }
                        });
                    }
                }
                // Add value to existing elements
                $(selector).each(function() {
                    addValue($(this));
                });

                // Function to handle mutations in the DOM because content is generated dynamically
                var observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        // Check if new nodes have been inserted
                        if (mutation.type === 'childList') {
                            mutation.addedNodes.forEach(function(node) {
                                if ($(node).is(selector)) {
                                    addValue($(node));
                                } else {
                                    $(node).find(selector).each(function() {
                                        addValue($(this));
                                    });
                                }
                            });
                        }
                    });
                });

                // Configure the observer to observe changes in the DOM
                var config = { childList: true, subtree: true };
                observer.observe(document.body, config);
            });


        </script>

        @if ((Session::get('topsearch')=='true') || (Request::is('/')))
            <script nonce="{{ csrf_token() }}">
                $("#tagSearch").focus();
            </script>
        @endif

        </body>
</html>
