<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Encryption Keys
    |--------------------------------------------------------------------------
    |
    | Passport uses encryption keys while generating secure access tokens for
    | your application. By default, the keys are stored as local files but
    | can be set via environment variables when that is more convenient.
    |
    */
    'private_key' => env('PASSPORT_PRIVATE_KEY'),
    'public_key' => env('PASSPORT_PUBLIC_KEY'),
    'expiration_years' => env('API_TOKEN_EXPIRATION_YEARS', 20),
    'cookie_name' => env('PASSPORT_COOKIE_NAME', 'snipeit_passport_token'),
];
