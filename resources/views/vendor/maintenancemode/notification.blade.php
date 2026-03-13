{{--
    This is a simple notification bar to users who are exempt from the maintenance page, telling them that a
    maintenance period is going on. You should place this within your main template(s) via a call to:

    @include('maintenancemode::notification')
--}}

@if(isset(${Config::get('maintenancemode.inject.prefix').'Enabled'}) &&
    ${Config::get('maintenancemode.inject.prefix').'Enabled'} == true)

    @if(Config::get('maintenancemode.notification-styles', true))
        <style>
            .maintenance-mode-alert {
                width: 100%;
                padding: .5em;
                background-color: #FF130F;
                color: #fff;
            }
            .maintenance-mode-alert strong {
                font-weight: bold;
            }
            .maintenance-mode-alert time {
                opacity: 0.7;
                font-size: .8em;
                padding-top: .1em;
                float: right;
            }
        </style>
    @endif

    <div class="maintenance-mode-alert" id="maintenance-mode-alert" role="alert">

        <strong>Maintenance Mode</strong>

        {{-- Show the truncated message (so it doesn't overflow) --}}
        @if(isset(${Config::get('maintenancemode.inject.prefix').'Message'}))
            {{ str_limit(${Config::get('maintenancemode.inject.prefix').'Message'}, 100, "&hellip;") }}
        @endif

        {{-- And show a human-friendly timestamp --}}
        @if(isset(${Config::get('maintenancemode.inject.prefix').'Timestamp'}) &&
            ${Config::get('maintenancemode.inject.prefix').'Timestamp'} instanceof DateTime)

            <time datetime="{{ ${Config::get('maintenancemode.inject.prefix').'Timestamp'} }}" title="{{ ${Config::get('maintenancemode.inject.prefix').'Timestamp'} }}">
                {{ ${Config::get('maintenancemode.inject.prefix').'Timestamp'}->diffForHumans() }}
            </time>

        @endif
    </div>

@endif