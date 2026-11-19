<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $relocatedCacheDirectory = storage_path('framework/testing/optimize-'.getmypid());

    $relocatableCaches = [
        'APP_CONFIG_CACHE' => 'config.php',
        'APP_EVENTS_CACHE' => 'events.php',
        'APP_ROUTES_CACHE' => 'routes-v7.php',
    ];

    $originalCacheEnv = snapshotCachePathEnv(array_keys($relocatableCaches));
    relocateFrameworkCaches($relocatedCacheDirectory, $relocatableCaches);

    $this->beforeApplicationDestroyed(function () use ($relocatedCacheDirectory, $originalCacheEnv) {
        File::deleteDirectory($relocatedCacheDirectory);
        restoreCachePathEnv($originalCacheEnv);
    });
});

test('optimize succeeds', function () {
    $this->artisan('optimize')->assertSuccessful();
});

/**
 * Record every cache path env var as it stands before this test
 * relocates it.
 *
 * @param  list<string>  $keys
 * @return array<string, array{env: mixed, server: mixed}>
 */
function snapshotCachePathEnv(array $keys): array
{
    $snapshot = [];

    foreach ($keys as $key) {
        $snapshot[$key] = [
            'env' => $_ENV[$key] ?? false,
            'server' => $_SERVER[$key] ?? false,
        ];
    }

    return $snapshot;
}

/**
 * Point the relocatable caches at an isolated
 * directory to avoid workers stepping on each other.
 *
 * @param  array<string, string>  $caches  env var => cache filename
 */
function relocateFrameworkCaches(string $directory, array $caches): void
{
    File::ensureDirectoryExists($directory);

    foreach ($caches as $key => $filename) {
        $_ENV[$key] = $_SERVER[$key] = $directory.'/'.$filename;
    }
}

/**
 * Put every cache path env var back exactly as it was before this test
 * ran, so a relocated path can't leak into the next test's config boot.
 *
 * @param  array<string, array{env: mixed, server: mixed}>  $originalCacheEnv
 */
function restoreCachePathEnv(array $originalCacheEnv): void
{
    foreach ($originalCacheEnv as $key => $original) {
        unset($_ENV[$key], $_SERVER[$key]);

        if ($original['env'] !== false) {
            $_ENV[$key] = $original['env'];
        }
        if ($original['server'] !== false) {
            $_SERVER[$key] = $original['server'];
        }
    }
}
