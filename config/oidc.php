<?php

/*
|--------------------------------------------------------------------------
| OIDC API bearer authentication
|--------------------------------------------------------------------------
|
| Lets the Snipe-IT API accept a short-lived JWT issued by an external OpenID
| Connect provider (Microsoft Entra, Okta, Auth0, Keycloak, Google, ...) as a
| `Authorization: Bearer` credential, in addition to Passport tokens. The token
| is validated against the configured issuer(s)/audience/JWKS and mapped to an
| existing Snipe-IT user, whose own permissions then apply unchanged.
|
| Provider-agnostic and inert until configured: with OIDC_API_ENABLED unset (or
| no issuers/audience), the guard authenticates nothing and Passport keeps
| working exactly as before.
|
*/

return [

    // Master switch. The guard is a no-op until this is true AND issuers +
    // audience are configured.
    'enabled' => env('OIDC_API_ENABLED', false),

    // Comma-separated list of trusted token issuers (the `iss` claim).
    // Entra v2: https://login.microsoftonline.com/<tenant-id>/v2.0
    'issuers' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('OIDC_API_ISSUERS', ''))
    ))),

    // Comma-separated list of accepted audiences (the `aud` claim). Usually the
    // API app registration's client id and/or its App ID URI (api://<id>).
    'audiences' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('OIDC_API_AUDIENCE', ''))
    ))),

    // Optional explicit JWKS endpoint. When empty, it is discovered from each
    // issuer's /.well-known/openid-configuration document.
    'jwks_uri' => env('OIDC_API_JWKS_URI') ?: null,

    // Accepted signing algorithms. Asymmetric only -- `none` and HS* are never
    // accepted (they would enable algorithm-confusion attacks).
    'algorithms' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('OIDC_API_ALGORITHMS', 'RS256,RS384,RS512,ES256'))
    ))),

    // Token claim used to match a Snipe-IT user by username. Entra: the UPN
    // arrives in `preferred_username`.
    'username_claim' => env('OIDC_API_USERNAME_CLAIM', 'preferred_username'),

    // Clock-skew leeway (seconds) for exp/nbf validation.
    'leeway' => (int) env('OIDC_API_LEEWAY', 60),

];
