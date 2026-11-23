<?php

/*
|--------------------------------------------------------------------------
| Snipe-IT MCP configuration
|--------------------------------------------------------------------------
|
| Overrides laravel/mcp's package defaults to narrow the Dynamic Client
| Registration attack surface. The laravel/mcp package ships with
| redirect_domains => ['*'], which means any redirect URI on any host
| is accepted by POST /oauth/register. Snipe-IT instead ships with a
| locked-down baseline and admins layer their own trusted entries on top
| via the Settings UI.
|
| Hardcoded safe baseline here:
|   redirect_domains: loopback only (http://localhost, 127.0.0.1, [::1]).
|                     Attackers cannot intercept callbacks delivered to
|                     the victim's own device loopback.
|   custom_schemes:   the first-party Snipe-IT mobile app's reverse-
|                     domain scheme. Native URI schemes require the
|                     attacker to have an app installed on the victim
|                     device to intercept, which is a worse prior
|                     compromise than the SA attack itself.
|
| Admin-configured additions are merged in by AppServiceProvider at
| boot time from the settings table columns
| mcp_trusted_custom_schemes and mcp_trusted_redirect_domains, so
| changes take effect without a config:cache rebuild.
|
| Anything that doesn't match this merged allow-list is still accepted
| by POST /oauth/register but the resulting client is marked pending
| and cannot complete an authorize/token exchange until a superuser
| approves it from Settings -> MCP -> Pending Clients.
|
*/

return [
    'redirect_domains' => [
        'http://localhost',
        'http://127.0.0.1',
        'http://[::1]',
    ],

    'custom_schemes' => [
        'com.grokability.snipeitmobile',
    ],
];
