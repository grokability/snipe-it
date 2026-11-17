<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Laravel\Passport\Passport;
use RuntimeException;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        (new Filesystem)->ensureDirectoryExists($app->storagePath('framework/views'));

        $app->make(Kernel::class)->bootstrap();

        $this->preparePassportKeys($app, $app->storagePath('framework/testing/passport'));

        return $app;
    }

    protected function preparePassportKeys(Application $app, string $directory): void
    {
        (new Filesystem)->ensureDirectoryExists($directory);

        Passport::loadKeysFrom($directory);
        $app->make('config')->set([
            'passport.private_key' => null,
            'passport.public_key' => null,
        ]);

        $lock = fopen($directory.'/keys.lock', 'c');

        if ($lock === false) {
            throw new RuntimeException('Unable to open the test Passport key lock.');
        }

        try {
            if (! flock($lock, LOCK_EX)) {
                throw new RuntimeException('Unable to lock the test Passport keys.');
            }

            clearstatcache();

            if (! is_file(Passport::keyPath('oauth-private.key')) || ! is_file(Passport::keyPath('oauth-public.key'))) {
                $exitCode = $app->make(Kernel::class)->call('passport:keys', [
                    '--force' => true,
                    '--length' => 2048,
                    '--no-interaction' => true,
                ]);

                if ($exitCode !== 0) {
                    throw new RuntimeException('Unable to generate the test Passport keys.');
                }
            }
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}
