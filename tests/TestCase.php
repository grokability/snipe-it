<?php

namespace Tests;

use App\Http\Middleware\SecurityHeaders;
use App\Models\Asset;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;
use Tests\Support\AssertHasActionLogs;
use Tests\Support\AssertsAgainstSlackNotifications;
use Tests\Support\CanSkipTests;
use Tests\Support\CustomTestMacros;
use Tests\Support\InitializesSettings;
use Tests\Support\InteractsWithAuthentication;

abstract class TestCase extends BaseTestCase
{
    use AssertHasActionLogs;
    use AssertsAgainstSlackNotifications;
    use CanSkipTests;
    use CreatesApplication;
    use CustomTestMacros;
    use InitializesSettings;
    use InteractsWithAuthentication;
    use LazilyRefreshDatabase;

    private array $globallyDisabledMiddleware = [
        SecurityHeaders::class,
    ];

    protected function setUp(): void
    {
        $this->guardAgainstMissingEnv();

        parent::setUp();

        $this->registerCustomMacros();

        $this->withoutMiddleware($this->globallyDisabledMiddleware);

        $this->initializeSettings();

        // Flush the custom field filter map cache between tests so that
        // dynamically-created custom fields are always picked up fresh.
        Asset::flushCustomFieldFilterMap();
    }

    // ...existing code...

    private function guardAgainstMissingEnv(): void
    {
        if (! file_exists(realpath(__DIR__.'/../').'/.env.testing')) {
            throw new RuntimeException(
                '.env.testing file does not exist. Aborting to avoid wiping your local database.'
            );
        }
    }
}
