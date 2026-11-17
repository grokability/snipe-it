<?php

use Illuminate\Support\Str;

// Memcached server pool. MEMCACHED_HOST can be a single hostname
// (single-server, the historical shape) or a comma-separated list to
// spread the cache across a pool. Each list entry accepts one of:
//
//   host
//   host:port
//   host:port:weight
//
// A missing port falls back to MEMCACHED_PORT (or 11211). A missing
// weight defaults to 100 — matching the pre-existing single-server
// default, so distribution stays uniform until an operator opts into
// per-server weighting.
$memcachedDefaultPort = (int) env('MEMCACHED_PORT', 11211);
$memcachedServers = [];
foreach (array_filter(array_map('trim', explode(',', (string) env('MEMCACHED_HOST', '127.0.0.1')))) as $entry) {
    $parts = explode(':', $entry);
    $memcachedServers[] = [
        'host' => $parts[0],
        'port' => isset($parts[1]) ? (int) $parts[1] : $memcachedDefaultPort,
        'weight' => isset($parts[2]) ? (int) $parts[2] : 100,
    ];
}
if (empty($memcachedServers)) {
    $memcachedServers[] = [
        'host' => '127.0.0.1',
        'port' => $memcachedDefaultPort,
        'weight' => 100,
    ];
}

return [

    /*
    |--------------------------------------------------------------------------
    | Default Cache Store
    |--------------------------------------------------------------------------
    |
    | This option controls the default cache connection that gets used while
    | using this caching library. This connection is used when another is
    | not explicitly specified when executing a given caching function.
    |
    */

    'default' => env('CACHE_DRIVER', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the cache "stores" for your application as
    | well as their drivers. You may even define multiple stores for the
    | same cache driver to group types of items stored in your caches.
    |
    | Supported drivers: "apc", "array", "database", "file",
    |            "memcached", "redis", "dynamodb", "null"
    |
    */

    'stores' => [

        'apc' => [
            'driver' => 'apc',
        ],

        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'cache',
            'connection' => null,
            'lock_connection' => null,
        ],

        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache'),
        ],

        'memcached' => [
            'driver' => 'memcached',
            'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
            'sasl' => [
                env('MEMCACHED_USERNAME'),
                env('MEMCACHED_PASSWORD'),
            ],
            'options' => [
                // Memcached::OPT_CONNECT_TIMEOUT => 2000,
            ],
            'servers' => $memcachedServers,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'lock_connection' => 'default',
        ],

        'dynamodb' => [
            'driver' => 'dynamodb',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'token' => env('AWS_SESSION_TOKEN'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'table' => env('DYNAMODB_CACHE_TABLE', 'cache'),
            'endpoint' => env('DYNAMODB_ENDPOINT'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | When utilizing a RAM based store such as APC or Memcached, there might
    | be other applications utilizing the same cache. So, we'll specify a
    | value to get prefixed to all our keys so we can avoid collisions.
    |
    */

    'prefix' => env('CACHE_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_cache'),

];
