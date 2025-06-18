<?php
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Illuminate\Support\Facades\Log;

$config = [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        // This will get overwritten to 'single' AND 'rollbar' in the code at the bottom of this file
        // if a ROLLBAR_TOKEN is given in the .env file
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'warning'),
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'warning'),
            'days' => env('LOG_MAX_DAYS', 14),
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => env('LOG_LEVEL', 'critical'),
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'warning'),
            'handler' => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'warning'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'warning'),
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'warning'),
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

        'scimtrace' => [
            'driver' => 'single',
            'path' => storage_path('logs/scim.log')
        ],

        'rollbar' => [
            'driver' => 'monolog',
            'handler' => \Rollbar\Laravel\MonologHandler::class,
            'access_token' => env('ROLLBAR_TOKEN'),
            'level' => env('ROLLBAR_LEVEL', 'error'),
        ],
    ],

];


if ((env('APP_ENV')=='production') && (env('ROLLBAR_TOKEN'))) {
    // Only add rollbar if the .env has a rollbar token
    $config['channels']['stack']['channels'] = ['single', 'rollbar'];

    // and only add the rollbar filter under the same conditions
    // Note: it will *not* be cacheable
    $config['channels']['rollbar']['check_ignore'] = function ($isUncaught, $args, $payload) {
        if (App::environment('production') && is_object($args) && get_class($args) == Rollbar\ErrorWrapper::class && $args->errorLevel == E_WARNING ) {
            Log::info("IGNORING E_WARNING in production mode: ".$args->getMessage());
            return true; // "TRUE - you should ignore it!"
        }
        $needle = "ArieTimmerman\\Laravel\\SCIMServer\\Exceptions\\SCIMException";
        if (App::environment('production') && is_string($args) && strncmp($args, $needle, strlen($needle) ) === 0 ) {
            Log::info("String: '$args' looks like a SCIM Exception; ignoring error");
            return true; //yes, *do* ignore it
        }
        return false;
    };

}

if (env('LOG_DEPRECATIONS')=='true') {
    $config['channels']['deprecations'] = [
        'driver' => 'single',
        'path' => storage_path('logs/deprecations.log')
    ];
}

return $config;